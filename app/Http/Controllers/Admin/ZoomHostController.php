<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZoomHost;
use App\Models\ZoomMeeting;
use App\Services\Zoom\ZoomClient;
use Illuminate\Support\Facades\Artisan;

/**
 * The Zoom licence pool. Licences are bought in the Zoom portal, never here —
 * this screen only mirrors them and lets an admin take one out of rotation.
 */
class ZoomHostController extends Controller
{
    public function index(ZoomClient $zoom)
    {
        $hosts = ZoomHost::orderByDesc('is_active')->orderBy('id')->get();

        // How much of the pool is committed right now, so the need for another
        // licence is visible before teachers start hitting the ceiling.
        $liveNow = ZoomMeeting::active()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->count();

        return view('admin.zoom.index', [
            'hosts' => $hosts,
            'configured' => $zoom->configured(),
            'enabled' => (bool) config('zoom.enabled'),
            'liveNow' => $liveNow,
            'upcoming' => ZoomMeeting::active()->where('starts_at', '>', now())->count(),
        ]);
    }

    public function sync()
    {
        Artisan::call('zoom:sync-hosts');

        return back()->with('status', trim(Artisan::output()));
    }

    public function toggle(ZoomHost $host)
    {
        $host->update(['is_active' => ! $host->is_active]);

        return back()->with('status', 'zoom-host-updated');
    }
}
