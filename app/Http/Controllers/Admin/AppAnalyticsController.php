<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppEvent;
use App\Models\AppInstall;
use App\Models\User;
use App\Services\Telemetry\AnalyticsRange;
use App\Services\Telemetry\AppAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Admin → Mobile App: what learners do in the app, from its own telemetry.
 *
 * Every page but Activity is an aggregate over a date range, computed live by
 * AppAnalytics and cached for a few minutes — long enough that clicking
 * between tabs is instant, short enough that a release morning is watchable.
 * Activity is the raw stream for one learner or install, never cached.
 */
class AppAnalyticsController extends Controller
{
    private const CACHE_SECONDS = 600;

    public function __construct(private readonly AppAnalytics $analytics) {}

    public function overview(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $data = Cache::remember($range->key('overview'), self::CACHE_SECONDS, fn () => $this->analytics->overview($range));

        return view('admin.app-analytics.overview', ['range' => $range] + $data);
    }

    public function funnels(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $onboarding = Cache::remember($range->key('onboarding'), self::CACHE_SECONDS, fn () => $this->analytics->onboardingFunnel($range));
        $paywall = Cache::remember($range->key('paywall'), self::CACHE_SECONDS, fn () => $this->analytics->paywallFunnel($range));

        return view('admin.app-analytics.funnels', compact('range', 'onboarding', 'paywall'));
    }

    public function retention(Request $request)
    {
        // Cohorts need history to mean anything: twelve weeks unless asked.
        $range = AnalyticsRange::fromRequest($request, defaultDays: 84);
        $retention = Cache::remember($range->key('retention'), self::CACHE_SECONDS, fn () => $this->analytics->retention($range));

        return view('admin.app-analytics.retention', compact('range', 'retention'));
    }

    public function learning(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $lessons = Cache::remember($range->key('lessons'), self::CACHE_SECONDS, fn () => $this->analytics->lessons($range));
        $concepts = Cache::remember($range->key('concepts'), self::CACHE_SECONDS, fn () => $this->analytics->concepts($range));

        return view('admin.app-analytics.learning', compact('range', 'lessons', 'concepts'));
    }

    /**
     * The event stream for one learner or one install, newest first.
     *
     * `q` is a member id, an e-mail address (a deleted account's original one
     * too) or an install id. With nothing asked, the latest events from anyone.
     */
    public function activity(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $user = null;
        $install = null;

        $events = AppEvent::query()->with('user:id,name,email')->orderByDesc('occurred_at')->orderByDesc('id');

        if ($q !== '') {
            if (Str::isUuid($q)) {
                $install = AppInstall::with('user:id,name,email')->where('install_id', strtolower($q))->first();
                $events->where('install_id', strtolower($q));
            } else {
                $user = User::withTrashed()
                    ->when(
                        ctype_digit($q),
                        fn ($query) => $query->whereKey((int) $q),
                        fn ($query) => $query->where('email', $q)->orWhere('deleted_email', $q),
                    )
                    ->first();
                $events->where('user_id', $user?->id ?? 0);
            }
        }

        $installs = $user
            ? AppInstall::where('user_id', $user->id)->orderByDesc('last_seen_at')->get()
            : collect($install ? [$install] : []);

        return view('admin.app-analytics.activity', [
            'q' => $q,
            'user' => $user,
            'installs' => $installs,
            'events' => $events->paginate(100)->withQueryString(),
        ]);
    }
}
