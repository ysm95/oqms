@extends('qms.layout', ['title' => 'QMS Dashboard'])

@section('content')
<section class="view active-view">
  <div class="page-title">
    <div>
      <p class="eyebrow">Role-aware home</p>
      <h1>What needs attention</h1>
    </div>
    <div class="button-row">
      <a class="secondary-button" href="{{ route('observations.index') }}">Observations</a>
      <a class="secondary-button" href="{{ route('public-reports.index') }}">Public intake</a>
      <a class="primary-button" href="{{ route('reporting.index') }}">Review reports</a>
    </div>
  </div>

  <div class="metric-grid">
    <article class="metric"><span>Open reports</span><strong>{{ $metrics['openReports'] }}</strong><small>Screening backlog</small></article>
    <article class="metric"><span>Open incidents</span><strong>{{ $metrics['openIncidents'] }}</strong><small>Accepted reports only</small></article>
    <article class="metric"><span>Open occurrences</span><strong>{{ $metrics['openOccurrences'] }}</strong><small>Legacy workspace</small></article>
    <article class="metric"><span>Overdue CAPA</span><strong>{{ $metrics['overdueActions'] }}</strong><small>Owner action queue</small></article>
    <article class="metric"><span>High risks</span><strong>{{ $metrics['highRisks'] }}</strong><small>Risk register</small></article>
    <article class="metric"><span>Open findings</span><strong>{{ $metrics['openFindings'] }}</strong><small>Audit and inspection</small></article>
    <article class="metric"><span>Open NCR</span><strong>{{ $metrics['openNcr'] }}</strong><small>Requirement evidence</small></article>
    <article class="metric"><span>Open CAPA</span><strong>{{ $metrics['openCapa'] }}</strong><small>Effectiveness pending</small></article>
    <article class="metric"><span>Compliance changes</span><strong>{{ $metrics['complianceChanges'] }}</strong><small>Impact assessment</small></article>
    <article class="metric"><span>Audit readiness</span><strong>{{ $metrics['auditReadiness'] }}%</strong><small>Evidence mapping</small></article>
    <article class="metric"><span>Unread alerts</span><strong>{{ $metrics['unreadNotifications'] }}</strong><small>Notification inbox</small></article>
    <article class="metric"><span>Public intake</span><strong>{{ $metrics['publicReports'] }}</strong><small>Open external reports</small></article>
    <article class="metric"><span>Training due</span><strong>{{ $metrics['trainingDue'] }}</strong><small>Next 45 days</small></article>
    <article class="metric"><span>Supplier watch</span><strong>{{ $metrics['supplierWatch'] }}</strong><small>High risk vendors</small></article>
    <article class="metric"><span>Report designs</span><strong>{{ $metrics['reportDesigns'] }}</strong><small>Published layouts</small></article>
    <article class="metric"><span>Notification rules</span><strong>{{ $metrics['notificationDesigns'] }}</strong><small>Published templates</small></article>
  </div>

  <div class="content-grid">
    <article class="panel wide">
      <div class="panel-header"><h2>Reports, incidents and assurance</h2><span class="status-pill">Original report preserved</span></div>
      <div class="content-grid compact-grid">
        <div>
          <h3>Recent reports</h3>
          <ul class="timeline">
            @foreach ($reports as $report)
              <li><strong><a href="{{ route('reporting.show', $report) }}">{{ $report->reference }}</a></strong><span>{{ $report->status }} - {{ $report->title }}</span></li>
            @endforeach
          </ul>
        </div>
        <div>
          <h3>Recent incidents</h3>
          <ul class="timeline">
            @foreach ($incidents as $incident)
              <li><strong><a href="{{ route('incidents.show', $incident) }}">{{ $incident->reference }}</a></strong><span>{{ $incident->workflow_stage }} - {{ $incident->title }}</span></li>
            @endforeach
          </ul>
        </div>
      </div>
      <h3>Safety workflow board</h3>
      <div class="kanban">
        @foreach ($workflowStages as $stage)
          <div class="lane">
            <h3>{{ $stage }}</h3>
            @foreach ($occurrences->where('workflow_stage', $stage)->take(3) as $occurrence)
              <a class="work-card {{ $occurrence->risk_rating === 'High' ? 'high' : '' }}" href="{{ $occurrence->record_family === 'Observation' ? route('observations.show', $occurrence) : route('occurrences.show', $occurrence) }}">
                {{ $occurrence->title }}<span>{{ $occurrence->reference }}</span>
              </a>
            @endforeach
            @if ($occurrences->where('workflow_stage', $stage)->isEmpty())
              <div class="empty-lane">No records</div>
            @endif
          </div>
        @endforeach
      </div>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>v7 product coverage</h2><span class="status-pill success">Core</span></div>
      <ul class="coverage-list">
        <li><strong>SHELL</strong><span>One QMS shell with role-aware navigation, search, create, help and notifications</span></li>
        <li><strong>OBS</strong><span>Observations capture Unsafe Act and Unsafe Condition reports through simple pages and HSE review</span></li>
        <li><strong>TRUST</strong><span>Reporter experience protects confidential/anonymous submissions and hides internal workflow</span></li>
        <li><strong>REPORT</strong><span>Original report remains separate from enriched incident and safety records</span></li>
        <li><strong>ASSURE</strong><span>Audit, inspection, finding, NCR and CAPA are separate controlled concepts</span></li>
        <li><strong>STD</strong><span>Standards and taxonomy are versioned records, not hard-coded source constants</span></li>
        <li><strong>AI</strong><span>AI remains embedded, permission-aware and blocked until paid secured provider approval</span></li>
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Assurance queue</h2><a class="secondary-button" href="{{ route('audits.index') }}">Audits</a></div>
      <ul class="timeline">
        @foreach ($audits as $audit)
          <li><strong>{{ $audit->reference }}</strong><span>{{ $audit->title }} - {{ $audit->status }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Inspection findings</h2><a class="secondary-button" href="{{ route('inspections.index') }}">Inspections</a></div>
      <ul class="timeline">
        @forelse ($findings as $finding)
          <li><strong>{{ $finding->reference }}</strong><span>{{ $finding->finding_type }} - {{ $finding->status }}</span></li>
        @empty
          <li><strong>Clear</strong><span>No open findings require attention.</span></li>
        @endforelse
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Lessons learned</h2><a class="secondary-button" href="{{ route('safety-promotions.index') }}">Lessons</a></div>
      <ul class="timeline">
        @forelse ($lessons as $lesson)
          <li><strong>{{ $lesson->reference }}</strong><span>{{ $lesson->approval_status }} - {{ $lesson->title }}</span></li>
        @empty
          <li><strong>No draft lessons</strong><span>Safety promotion starts after confidentiality review.</span></li>
        @endforelse
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Risk watch</h2><a class="secondary-button" href="{{ route('risks.index') }}">Risks</a></div>
      <ul class="timeline">
        @foreach ($risks as $risk)
          <li><strong>{{ $risk->rating }}</strong><span>{{ $risk->hazard }} - {{ $risk->owner }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Controlled documents</h2><a class="secondary-button" href="{{ route('documents.index') }}">Documents</a></div>
      <ul class="timeline">
        @foreach ($documents as $document)
          <li><strong>{{ $document->reference }}</strong><span>{{ $document->title }} - {{ $document->status }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Notifications</h2><a class="secondary-button" href="{{ route('notifications.index') }}">Inbox</a></div>
      <ul class="timeline">
        @foreach ($notifications as $notification)
          <li><strong>{{ $notification->title }}</strong><span>{{ $notification->source_reference ?? 'QMS' }} - {{ $notification->created_at->format('Y-m-d H:i') }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Public reports</h2><a class="secondary-button" href="{{ route('public-reports.index') }}">Review</a></div>
      <ul class="timeline">
        @foreach ($publicReports as $report)
          <li><strong>{{ $report->reference }}</strong><span>{{ $report->category }} - {{ $report->confidential ? 'Confidential' : $report->status }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Training watch</h2><a class="secondary-button" href="{{ route('training.index') }}">Training</a></div>
      <ul class="timeline">
        @foreach ($training as $record)
          <li><strong>{{ $record->reference }}</strong><span>{{ $record->person_name }} - {{ $record->status }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Objectives / suppliers</h2><a class="secondary-button" href="{{ route('objectives.index') }}">Objectives</a></div>
      <ul class="timeline">
        @foreach ($objectives as $objective)
          <li><strong>{{ $objective->reference }}</strong><span>{{ $objective->title }} - {{ $objective->status }}</span></li>
        @endforeach
        @foreach ($suppliers as $supplier)
          <li><strong>{{ $supplier->reference }}</strong><span>{{ $supplier->name }} - {{ $supplier->risk_rating }}</span></li>
        @endforeach
      </ul>
    </article>
  </div>
</section>
@endsection
