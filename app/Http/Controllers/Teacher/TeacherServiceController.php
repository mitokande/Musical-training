<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherServiceRequest;
use App\Models\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherServiceController extends Controller
{
    public function store(StoreTeacherServiceRequest $request): RedirectResponse
    {
        $profile = $request->user()->teacherProfile;
        abort_if(! $profile, 404);
        $this->authorize('update', $profile);

        $profile->services()->create($request->validated() + [
            'sort_order' => ($profile->services()->max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('status', 'service-saved');
    }

    public function update(StoreTeacherServiceRequest $request, TeacherService $service): RedirectResponse
    {
        $this->authorize('update', $service->teacherProfile);

        $service->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'service-saved');
    }

    public function destroy(Request $request, TeacherService $service): RedirectResponse
    {
        $this->authorize('update', $service->teacherProfile);

        $service->delete();

        return back()->with('status', 'service-deleted');
    }
}
