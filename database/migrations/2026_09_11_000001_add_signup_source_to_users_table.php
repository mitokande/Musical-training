<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where an account was created: the website or the mobile app.
 *
 * The two surfaces register through different controllers — the web through
 * RegisteredUserController / SocialAuthController, the app only through
 * Api\V1\AuthController — so the column is stamped at creation and never
 * changes. 'web' is the default because everything that existed before the
 * app did was made on the site.
 *
 * The backfill marks the accounts the app has already created: the app mints
 * its Sanctum token in the same request that creates the user, and names it
 * after the platform (`ios-17.4`, `android-34`), so a platform-named token
 * created within a minute of the account is the signup itself. A web account
 * that later signs into the app has a token created much later and stays 'web'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signup_source', 20)->default('web')->index()->after('plan');
        });

        // The one-minute window is compared in PHP rather than with
        // DATE_ADD(): the migration also runs on the in-memory SQLite the
        // test-suite uses, which has no such function.
        $mobileUserIds = DB::table('personal_access_tokens as t')
            ->join('users as u', 'u.id', '=', 't.tokenable_id')
            ->where('t.tokenable_type', User::class)
            ->where(function ($q) {
                $q->where('t.name', 'like', 'ios-%')
                    ->orWhere('t.name', 'like', 'android-%');
            })
            ->whereNotNull('t.created_at')
            ->whereNotNull('u.created_at')
            ->get(['u.id', 'u.created_at as user_created_at', 't.created_at as token_created_at'])
            ->filter(fn ($row) => Carbon::parse($row->token_created_at)
                ->lte(Carbon::parse($row->user_created_at)->addMinute()))
            ->pluck('id')
            ->unique()
            ->values();

        if ($mobileUserIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $mobileUserIds)->update(['signup_source' => 'mobile']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['signup_source']);
            $table->dropColumn('signup_source');
        });
    }
};
