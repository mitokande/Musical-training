@extends('admin.layouts.admin')

@section('page-title', 'Calendar')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">View and manage all appointments.</p>
        <a href="{{ route('admin.appointments.create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Appointment
        </a>
    </div>

    {{-- Calendar --}}
    <div class="card p-6">
        <div id="calendar"></div>
    </div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '{{ route("admin.calendar.events") }}',
        height: 'auto',
        eventClick: function (info) {
            if (info.event.extendedProps && info.event.extendedProps.appointment_id) {
                window.location.href = '/admin/appointments/' + info.event.extendedProps.appointment_id;
            }
        },
        eventColor: '#9333ea',
        eventTextColor: '#ffffff',
        nowIndicator: true,
        editable: false,
        dayMaxEvents: true
    });
    calendar.render();
});
</script>
@endpush
