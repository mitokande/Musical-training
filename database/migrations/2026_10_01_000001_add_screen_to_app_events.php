<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The screen every app event happened on, as a real column.
 *
 * From this build on the app stamps each event with the route on top when it
 * fired — a tap is meaningless without it, and "which door did they come
 * through" is most of what a purchase event is for. It is a column rather than
 * a JSON path because it is what the journey, screen and tap pages group and
 * filter by on every query.
 *
 * Two indexes follow the pages that read them: `(name, screen, occurred_at)`
 * for "taps on this screen" and "views of this screen", and
 * `(session_id, occurred_at)` — replacing the bare `session_id` index, which it
 * covers — for replaying one session in order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            $table->string('screen', 96)->nullable()->after('session_id');

            $table->index(['name', 'screen', 'occurred_at']);
            $table->index(['session_id', 'occurred_at']);
            $table->dropIndex(['session_id']);
        });

        // Events from before the column: a screen event's own screen is the one
        // it opened. Nothing else can be recovered without replaying sessions.
        $name = DB::connection()->getQueryGrammar()->wrap('props->name');
        DB::table('app_events')
            ->where('name', 'screen')
            ->whereNull('screen')
            ->update(['screen' => DB::raw("SUBSTR($name, 1, 96)")]);
    }

    public function down(): void
    {
        Schema::table('app_events', function (Blueprint $table) {
            $table->index('session_id');
            $table->dropIndex(['session_id', 'occurred_at']);
            $table->dropIndex(['name', 'screen', 'occurred_at']);
            $table->dropColumn('screen');
        });
    }
};
