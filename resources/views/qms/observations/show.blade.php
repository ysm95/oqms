@extends('qms.layout', ['title' => $observation->reference . ' - QMS'])

@section('content')
<section class="view active-view observation-record" data-record-tabs>
  <div class="page-title">
    <div>
      <p class="eyebrow">Observation record</p>
      <h1>{{ $observation->reference }}</h1>
    </div>
    <span class="status-pill">{{ $observation->status }}</span>
  </div>

  <div class="record-head panel">
    <div>
      <h2>{{ $observation->title }}</h2>
      <p>{{ $observation->observation_type }} · {{ $observation->area }} / {{ $observation->unit }} · {{ $observation->location }}</p>
    </div>
    <strong class="risk-badge">Risk: {{ $observation->risk_rating }}</strong>
  </div>

  <nav class="record-tabs" aria-label="Observation record tabs">
    @foreach (['Summary', 'Information', 'Description', 'Review', 'Action Tracker', 'Attachments', 'History'] as $index => $tab)
      <button class="{{ $index === 0 ? 'active' : '' }}" data-record-tab="{{ $index }}">{{ $tab }}</button>
    @endforeach
  </nav>

  <section class="tab-panel active" data-record-panel="0">
    <div class="content-grid">
      <article class="panel">
        <div class="panel-header"><h2>Status</h2><span class="status-pill">{{ $observation->workflow_stage }}</span></div>
        <div class="detail-grid">
          <div><span>Type</span><strong>{{ $observation->observation_type }}</strong></div>
          <div><span>Review</span><strong>{{ $observation->review_decision ?? 'Pending' }}</strong></div>
          <div><span>Action required</span><strong>{{ $observation->action_required ? 'Yes' : 'No' }}</strong></div>
          <div><span>Observed</span><strong>{{ optional($observation->observed_on)->format('Y-m-d') }} {{ $observation->observed_at }}</strong></div>
        </div>
      </article>
      <article class="panel">
        <div class="panel-header"><h2>Next step</h2><span class="status-pill">HSE</span></div>
        <p>{{ $observation->review_decision ? 'Follow the review decision and action tracker status.' : 'Complete HSE Review to decide if the observation is valid.' }}</p>
      </article>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="1">
    <article class="panel wide">
      <div class="panel-header"><h2>Information</h2><span class="status-pill">Who / where / when</span></div>
      <div class="detail-grid">
        <div><span>Area</span><strong>{{ $observation->area }}</strong></div>
        <div><span>Unit</span><strong>{{ $observation->unit }}</strong></div>
        <div><span>Date</span><strong>{{ optional($observation->observed_on)->format('Y-m-d') }}</strong></div>
        <div><span>Time</span><strong>{{ $observation->observed_at }}</strong></div>
        <div><span>Observer</span><strong>{{ $observation->observer }}</strong></div>
        <div><span>Location</span><strong>{{ $observation->location }}</strong></div>
        <div><span>Exact location</span><strong>{{ $observation->exact_location ?? 'Not set' }}</strong></div>
        <div><span>Department</span><strong>{{ $observation->department_name }}</strong></div>
        <div><span>Confidential</span><strong>{{ $observation->confidential ? 'Yes' : 'No' }}</strong></div>
      </div>
    </article>
  </section>

  <section class="tab-panel" data-record-panel="2">
    <article class="panel wide">
      <div class="panel-header"><h2>Description</h2><span class="status-pill">{{ $observation->observation_type }}</span></div>
      <h3>What was observed?</h3>
      <p>{{ $observation->description }}</p>
      <h3>Potential consequence</h3>
      <p>{{ $observation->potential_consequence ?: 'Not entered.' }}</p>
      <h3>Action taken</h3>
      <div class="tag-row">@foreach ($observation->action_taken ?? [] as $action)<span>{{ $action }}</span>@endforeach</div>
      <p>{{ $observation->immediate_corrective_action ?: 'No immediate action entered.' }}</p>
      <h3>Temporary control</h3>
      <p>{{ $observation->temporary_control ?: 'Not entered.' }}</p>
    </article>
  </section>

  <section class="tab-panel" data-record-panel="3">
    <div class="record-layout">
      <article class="panel wide">
        <div class="panel-header"><h2>HSE Review</h2><span class="status-pill warning">{{ $observation->review_decision ?? 'Pending' }}</span></div>
        <form method="POST" action="{{ route('observations.review', $observation) }}">
          @csrf @method('PATCH')
          <fieldset class="choice-cards">
            <legend>Is the observation valid?</legend>
            @foreach (['Valid', 'Not Valid', 'Needs More Information'] as $decision)
              <label><input type="radio" name="review_decision" value="{{ $decision }}" @checked(old('review_decision', $observation->review_decision) === $decision) required><span>{{ $decision }}</span></label>
            @endforeach
          </fieldset>
          <div class="form-grid">
            <label>Risk level<select name="risk_rating" required>@foreach (['Low', 'Medium', 'High', 'Critical'] as $risk)<option @selected(old('risk_rating', $observation->risk_rating) === $risk)>{{ $risk }}</option>@endforeach</select></label>
            <label class="inline-check"><input type="checkbox" name="action_required" value="1" @checked(old('action_required', $observation->action_required))> Follow-up action required</label>
          </div>
          <label>Reviewer comments<textarea name="reviewer_comments" rows="5" required>{{ old('reviewer_comments', $observation->reviewer_comments) }}</textarea></label>
          <label>Reporter-visible message<textarea name="reporter_visible_message" rows="3" placeholder="Only use this for simple reporter updates or more information requests.">{{ old('reporter_visible_message', $observation->reporter_visible_message) }}</textarea></label>
          <div class="button-row">
            <button class="primary-button">Save Review</button>
            <a class="secondary-button" href="#action-tracker" data-record-jump="4">Continue to Actions</a>
          </div>
        </form>
      </article>
      <aside class="panel">
        <h2>Review checklist</h2>
        <ul class="check-list">
          <li>Confirm the observation is factual.</li>
          <li>Set the risk level.</li>
          <li>Use reporter message only when needed.</li>
          <li>Create actions only for required follow-up.</li>
        </ul>
      </aside>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="4" id="action-tracker">
    <div class="record-layout">
      <article class="panel wide">
        <div class="panel-header"><h2>Action Tracker</h2><span class="status-pill">Optional follow-up</span></div>
        <div class="button-row action-toolbar">
          <button class="secondary-button" type="button" data-open-action-drawer>Add Entry</button>
          <button class="secondary-button" type="button">Archive Selected</button>
          <button class="secondary-button" type="button">List All</button>
          <a class="secondary-button" href="{{ route('exports.actions') }}">Export</a>
        </div>
        <div class="table-panel">
          <table>
            <thead><tr><th>Action No</th><th>Action Type</th><th>Location</th><th>Source</th><th>Owner</th><th>Due Date</th><th>Priority</th><th>Status</th><th>Evidence</th></tr></thead>
            <tbody>
              @forelse ($actions as $action)
                <tr>
                  <td>{{ $action->reference }}</td>
                  <td>{{ $action->title }}</td>
                  <td>{{ $observation->location }}</td>
                  <td>{{ $action->source_reference }}</td>
                  <td>{{ $action->owner }}</td>
                  <td>{{ optional($action->due_date)->format('Y-m-d') }}</td>
                  <td>{{ $action->priority }}</td>
                  <td><span class="status-pill">{{ $action->status }}</span></td>
                  <td>{{ $action->evidence_required ? 'Required' : 'Optional' }}</td>
                </tr>
              @empty
                <tr><td colspan="9">No action entries. Add one only if HSE review requires follow-up.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>
      <aside class="panel action-drawer" data-action-drawer>
        <h2>Add Action Entry</h2>
        <form method="POST" action="{{ route('observations.actions.store', $observation) }}">
          @csrf
          <label>Action type<select name="action_type" required><option>Corrective Action</option><option>Preventive Action</option><option>Containment</option><option>Verification</option></select></label>
          <label>Description<textarea name="description" rows="4" required placeholder="What follow-up is required?"></textarea></label>
          <label>Owner<input name="owner" required placeholder="Person or team"></label>
          <label>Department<input name="responsible_department" value="{{ $observation->department_name }}"></label>
          <label>Due date<input type="date" name="due_date" required value="{{ now()->addDays(7)->toDateString() }}"></label>
          <label>Priority<select name="priority">@foreach (['Low', 'Medium', 'High', 'Critical'] as $priority)<option @selected($observation->risk_rating === $priority)>{{ $priority }}</option>@endforeach</select></label>
          <label class="inline-check"><input type="checkbox" name="evidence_required" value="1" checked> Evidence required</label>
          <label class="inline-check"><input type="checkbox" name="verification_required" value="1" checked> Verification required</label>
          <button class="primary-button full">Save Action</button>
        </form>
      </aside>
    </div>
  </section>

  <section class="tab-panel" data-record-panel="5">
    <article class="panel wide">
      <div class="panel-header"><h2>Attachments</h2><span class="status-pill">Evidence</span></div>
      <p>Attachment upload will use the controlled evidence service. Keep photos and files relevant to this observation.</p>
    </article>
  </section>

  <section class="tab-panel" data-record-panel="6">
    <div class="content-grid">
      <article class="panel">
        <div class="panel-header"><h2>Notes</h2><span class="status-pill">Internal</span></div>
        <ul class="timeline">@forelse ($notes as $note)<li><strong>{{ $note->author }}</strong><span>{{ $note->visibility }} - {{ $note->body }}</span></li>@empty<li><strong>No notes</strong><span>No internal notes recorded.</span></li>@endforelse</ul>
      </article>
      <article class="panel">
        <div class="panel-header"><h2>History</h2><span class="status-pill">Audit trail</span></div>
        <ul class="timeline">@forelse ($auditLogs as $log)<li><strong>{{ $log->action }}</strong><span>{{ $log->actor ?? 'System' }} - {{ $log->created_at->format('Y-m-d H:i') }}</span></li>@empty<li><strong>No audit entries</strong><span>Review and action changes will appear here.</span></li>@endforelse</ul>
      </article>
    </div>
  </section>
</section>
@endsection
