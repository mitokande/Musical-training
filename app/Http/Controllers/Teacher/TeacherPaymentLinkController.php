<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherPaymentLinkRequest;
use App\Models\TeacherPaymentLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherPaymentLinkController extends Controller
{
    public function store(StoreTeacherPaymentLinkRequest $request): RedirectResponse
    {
        $profile = $request->user()->teacherProfile;
        abort_if(! $profile, 404);
        $this->authorize('managePaymentLinks', $profile);

        $profile->paymentLinks()->create($request->validated() + [
            'sort_order' => ($profile->paymentLinks()->max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('status', 'payment-link-saved');
    }

    public function update(StoreTeacherPaymentLinkRequest $request, TeacherPaymentLink $paymentLink): RedirectResponse
    {
        $this->authorize('managePaymentLinks', $paymentLink->teacherProfile);

        $paymentLink->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'payment-link-saved');
    }

    public function destroy(Request $request, TeacherPaymentLink $paymentLink): RedirectResponse
    {
        $this->authorize('managePaymentLinks', $paymentLink->teacherProfile);

        $paymentLink->delete();

        return back()->with('status', 'payment-link-deleted');
    }
}
