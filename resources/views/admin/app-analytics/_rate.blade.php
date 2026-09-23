{{-- A percentage, or a dash when there was nothing to divide by. --}}
@if ($value === null)
    <span class="text-gray-300">—</span>
@else
    {{ rtrim(rtrim(number_format($value, 1), '0'), '.') }}%
@endif
