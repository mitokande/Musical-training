<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($request->settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function activityLog(Request $request)
    {
        $activities = Activity::with('causer')
            ->when($request->log_name, fn($q, $name) => $q->where('log_name', $name))
            ->when($request->causer_id, fn($q, $id) => $q->where('causer_id', $id))
            ->when($request->search, fn($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->latest()
            ->paginate(30);

        return view('admin.settings.activity-log', compact('activities'));
    }
}
