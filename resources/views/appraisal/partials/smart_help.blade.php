@if (isset($id))
    <div id="{{ $id }}" class="is-smart-help alert alert-info small mb-3 d-none">
    @else
        <div class="is-smart-help alert alert-info small mb-3 d-none">
@endif
{{ $message ?? 'SMART targets are measurable — include a numeric value or percentage. Examples: "Reduce average handling time to 10 minutes", "Increase CSAT to 90%".' }}
</div>
