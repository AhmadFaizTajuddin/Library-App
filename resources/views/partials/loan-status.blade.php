@if($loan->returned_at)
    <span class="badge text-bg-secondary">Returned</span>
@elseif($loan->isOverdue())
    <span class="stamp">Overdue {{ $loan->lateDays() }} {{ \Illuminate\Support\Str::plural('day', $loan->lateDays()) }}</span>
@else
    <span class="badge text-bg-success">On loan</span>
@endif
