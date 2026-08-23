@extends('qms.layout', ['title' => $permit->reference . ' - QMS'])

@section('content')
<section class="view active-view observation-record" data-record-tabs>
  <div class="page-title">
    <div>
      <p class="eyebrow">Permit record</p>
      <h1>{{ $permit->reference }}</h1>
    </div>
    <span class="status-pill">{{ $permit->status }}</span>
  </div>

  <div class="record-head panel">
    <div>
      <h2>{{ $permit->title }}</h2>
      <p>{{ $permit->permit_type }} · {{ $permit->area }} / {{ $permit->unit }} · {{ $permit->asset ?: 'No asset set' }}</p>
    </div>
    <strong class="risk-badge">Risk: {{ $permit->risk_rating }}</strong>
  </div>

  <nav class="record-tabs" aria-label="Permit record tabs">
    @foreach (['Summary', 'Work Information', 'Risk & Controls', 'Approval', 'Field Execution', 'History'] as $index => $tab)
      <button class="{{ $index === 0 ? 'active' : '' }}" data-record-tab="{{ $index }}">{{ $tab }}</button>
    @endforeach
  </nav>

  <section class="tab-panel active" data-record-panel="0">
    <div class="content-grid">
      <article class="panel">
        <div class="panel-header"><h2>Status</h2><span class="status-pill">{{ $permit->status }}</span></div>
        <div class="detail-grid">
          <div><span>Type</span><strong>{{ $permit->permit_type }}</strong></div>
          <div><span>Owner</span><strong>{{ $permit->owner ?: 'Unassigned' }}</strong></div>
          <div><span>Approver</span><strong>{{ $permit->current_approver ?: 'Not required' }}</strong></div>
          <div><span>Window</span><strong>{{ optional($permit->planned_start_at)->format('Y-m-d H:i') }} - {{ optional($permit->planned_end_at)->format('Y-m-d H:i') }}</strong></div>
        </div>
      </article>
      <article class="panel">
        <div class="panel-header"><h2>Next step</h2><span class="status-pill">Lifecycle</span></div>
        <p>
          @if ($permit->status === 'Draft')
            Submit the permit for review when the work details and controls are ready.
          @elseif ($permit->status === 'Pending Review')
            Complete review and approve only when controls are adequate.
          @elseif ($permit->status === 'Approved')
            Issue the permit before field work begins.
          @elseif ($permit->status === 'Active')
            Monitor the work window, suspend if conditions change, or close when work is complete.
          @elseif ($permit->status === 'Suspended')
            Re-issue only after the suspension reason is cleared.
          @else
            This permit is {{ strtolower($permit->status) }}.
          @endif
        </p>
      </article>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="1">
    <article class="panel wide">
      <div class="panel-header"><h2>Work Information</h2><span class="status-pill">Who / where / when</span></div>
      <div class="detail-grid">
        <div><span>Requester</span><strong>{{ $permit->requester }}</strong></div>
        <div><span>Department</span><strong>{{ $permit->department ?: 'Not set' }}</strong></div>
        <div><span>Contractor</span><strong>{{ $permit->contractor ?: 'Internal work' }}</strong></div>
        <div><span>Area</span><strong>{{ $permit->area ?: 'Not set' }}</strong></div>
        <div><span>Unit</span><strong>{{ $permit->unit ?: 'Not set' }}</strong></div>
        <div><span>Asset</span><strong>{{ $permit->asset ?: 'Not set' }}</strong></div>
        <div><span>Issued at</span><strong>{{ optional($permit->issued_at)->format('Y-m-d H:i') ?: 'Not issued' }}</strong></div>
        <div><span>Closed at</span><strong>{{ optional($permit->closed_at)->format('Y-m-d H:i') ?: 'Open' }}</strong></div>
      </div>
      <h3>Work description</h3>
      <p>{{ $permit->work_description }}</p>
    </article>
  </section>

  <section class="tab-panel" data-record-panel="2">
    <div class="content-grid">
      <article class="panel">
        <div class="panel-header"><h2>Risk</h2><span class="status-pill">{{ $permit->risk_rating }}</span></div>
        <div class="detail-grid">
          <div><span>Initial risk</span><strong>{{ $permit->risk_rating }}</strong></div>
          <div><span>Residual risk</span><strong>{{ $permit->residual_risk ?: 'Not set' }}</strong></div>
          <div><span>Isolation</span><strong>{{ $permit->isolation_required ? 'Required' : 'Not required' }}</strong></div>
          <div><span>Gas test</span><strong>{{ $permit->gas_test_required ? 'Required' : 'Not required' }}</strong></div>
          <div><span>Fire watch</span><strong>{{ $permit->fire_watch_required ? 'Required' : 'Not required' }}</strong></div>
          <div><span>Standby</span><strong>{{ $permit->standby_required ? 'Required' : 'Not required' }}</strong></div>
        </div>
      </article>
      <article class="panel">
        <div class="panel-header"><h2>Controls</h2><span class="status-pill">Required</span></div>
        <h3>Hazards</h3>
        <div class="tag-row">@forelse ($permit->hazards ?? [] as $item)<span>{{ $item }}</span>@empty<span>None recorded</span>@endforelse</div>
        <h3>Controls</h3>
        <div class="tag-row">@forelse ($permit->controls ?? [] as $item)<span>{{ $item }}</span>@empty<span>None recorded</span>@endforelse</div>
        <h3>PPE</h3>
        <div class="tag-row">@forelse ($permit->required_ppe ?? [] as $item)<span>{{ $item }}</span>@empty<span>None recorded</span>@endforelse</div>
      </article>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="3">
    <div class="record-layout">
      <article class="panel wide">
        <div class="panel-header"><h2>Approval</h2><span class="status-pill">{{ $permit->status }}</span></div>
        @if ($transitions)
          <form method="POST" action="{{ route('permits.transition', $permit) }}">
            @csrf @method('PATCH')
            <div class="form-grid">
              <label>Action<select name="action" required>
                @foreach ($transitions as $action => $target)
                  <option value="{{ $action }}">{{ ucfirst($action) }} -> {{ $target }}</option>
                @endforeach
              </select></label>
              <label>Extend until<input type="datetime-local" name="planned_end_at" value="{{ optional($permit->planned_end_at)->format('Y-m-d\TH:i') }}"></label>
            </div>
            <label>Decision note<textarea name="transition_note" rows="4" placeholder="Required for suspension. Recommended for approval and cancellation."></textarea></label>
            <label>Closeout summary<textarea name="closeout_summary" rows="4" placeholder="Required only when closing the permit."></textarea></label>
            <button class="primary-button">Apply Action</button>
          </form>
        @else
          <p>No further lifecycle actions are available for this permit.</p>
        @endif
      </article>
      <aside class="panel">
        <h2>Approval history</h2>
        <ul class="timeline">
          @forelse ($permit->approval_history ?? [] as $entry)
            <li><strong>{{ $entry['action'] ?? 'Updated' }}</strong><span>{{ $entry['actor'] ?? 'System' }} - {{ $entry['at'] ?? '' }}</span></li>
          @empty
            <li><strong>No approval entries</strong><span>Review decisions appear here.</span></li>
          @endforelse
        </ul>
      </aside>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="4">
    <div class="content-grid">
      <article class="panel">
        <div class="panel-header"><h2>Execution Controls</h2><span class="status-pill">Field</span></div>
        <h3>LOTO points</h3>
        <div class="tag-row">@forelse ($permit->loto_points ?? [] as $item)<span>{{ $item }}</span>@empty<span>None recorded</span>@endforelse</div>
        <h3>Training</h3>
        <div class="tag-row">@forelse ($permit->required_training ?? [] as $item)<span>{{ $item }}</span>@empty<span>None recorded</span>@endforelse</div>
        <h3>Linked documents</h3>
        <div class="tag-row">@forelse ($permit->linked_documents ?? [] as $item)<span>{{ $item }}</span>@empty<span>None recorded</span>@endforelse</div>
      </article>
      <article class="panel">
        <div class="panel-header"><h2>Field checks</h2><span class="status-pill">Issue / suspend / close</span></div>
        <ul class="timeline">
          @forelse ($permit->field_checks ?? [] as $entry)
            <li><strong>{{ $entry['action'] ?? 'Updated' }}</strong><span>{{ $entry['actor'] ?? 'System' }} - {{ $entry['at'] ?? '' }}</span></li>
          @empty
            <li><strong>No field checks</strong><span>Issue, suspension and closeout events appear here.</span></li>
          @endforelse
        </ul>
      </article>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="5">
    <div class="content-grid">
      <article class="panel">
        <div class="panel-header"><h2>Permit activity</h2><span class="status-pill">History</span></div>
        <ul class="timeline">
          @forelse ($permit->activities as $activity)
            <li><strong>{{ $activity->action }}</strong><span>{{ $activity->from_status ?: 'New' }} -> {{ $activity->to_status }} · {{ $activity->actor ?? 'System' }} · {{ $activity->created_at->format('Y-m-d H:i') }}</span></li>
          @empty
            <li><strong>No activity</strong><span>Permit lifecycle changes will appear here.</span></li>
          @endforelse
        </ul>
      </article>
      <article class="panel">
        <div class="panel-header"><h2>Audit trail</h2><span class="status-pill">System</span></div>
        <ul class="timeline">
          @forelse ($auditLogs as $log)
            <li><strong>{{ $log->action }}</strong><span>{{ $log->actor ?? 'System' }} - {{ $log->created_at->format('Y-m-d H:i') }}</span></li>
          @empty
            <li><strong>No audit entries</strong><span>Audit records will appear here.</span></li>
          @endforelse
        </ul>
      </article>
    </div>
  </section>
</section>
@endsection
