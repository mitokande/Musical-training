<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PianoStudioController extends Controller
{
    public function settings()
    {
        $settings = [
            'piano_studio_enabled'     => SystemSetting::get('piano_studio_enabled', true),
            'piano_studio_max_keys'    => SystemSetting::get('piano_studio_max_keys', 88),
            'piano_studio_default_octave' => SystemSetting::get('piano_studio_default_octave', 4),
            'piano_studio_sound_font'  => SystemSetting::get('piano_studio_sound_font', 'default'),
            'piano_studio_midi_enabled'=> SystemSetting::get('piano_studio_midi_enabled', true),
        ];

        return view('admin.piano-studio.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'piano_studio_enabled'        => 'boolean',
            'piano_studio_max_keys'       => 'required|integer|in:49,61,76,88',
            'piano_studio_default_octave' => 'required|integer|min:1|max:7',
            'piano_studio_sound_font'     => 'required|string',
            'piano_studio_midi_enabled'   => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value, is_bool($value) ? 'boolean' : 'string', 'piano_studio');
        }

        return back()->with('success', 'Piano Studio settings updated successfully.');
    }
}
