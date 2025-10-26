@php $rt = $recipientType ?? 'hr'; @endphp
<p>Hello,</p>

@if ($rt === 'manager')
    <p>A Performance Improvement Plan has been created for your team member <strong>{{ $pip->user->name }}</strong>.
        Please review and support where required.</p>
@elseif($rt === 'employee')
    <p>A Performance Improvement Plan (PIP) has been created for you (<strong>{{ $pip->user->name }}</strong>). Please
        review the plan and reach out to your manager if you have questions.</p>
@else
    <p>A new Performance Improvement Plan (PIP) has been created for <strong>{{ $pip->user->name }}</strong>.</p>
@endif

<ul>
    <li>Reason: {{ $pip->reason }}</li>
    <li>Start: {{ $pip->start_date }}</li>
    <li>End: {{ $pip->end_date }}</li>
    <li>Notes: {{ $pip->notes }}</li>
</ul>

@if (isset($manager) && $rt !== 'manager')
    @php
        try {
            $managerLink = route('users.show', $manager->id);
        } catch (Exception $e) {
            $managerLink = url('/users/' . $manager->id);
        }
        try {
            $employeeLink = isset($pip->user) ? route('users.show', $pip->user->id) : null;
        } catch (Exception $e) {
            $employeeLink = isset($pip->user) ? url('/users/' . $pip->user->id) : null;
        }
    @endphp
    <p>Manager: <a href="{{ $managerLink }}">{{ $manager->name }}</a> ({{ $manager->email ?? 'no-email' }})</p>
@endif

@if (isset($pip->user) && $employeeLink)
    <p>Employee: <a href="{{ $employeeLink }}">{{ $pip->user->name }}</a></p>
@endif

<p>You can view the PIP in the HR dashboard.</p>
<p>Regards,<br />Performance App</p>
