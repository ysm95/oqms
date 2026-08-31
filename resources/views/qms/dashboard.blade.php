@extends('qms.layout', ['title' => 'QMS Dashboard'])

@section('content')
<section class="view active-view home-dashboard">
  <div class="page-title dashboard-title">
    <div>
      <p class="eyebrow">Role-aware home</p>
      <h1>What needs attention</h1>
    </div>
    <div class="button-row">
      <a class="secondary-button" href="{{ route('my-work.index') }}">My Work</a>
      <a class="secondary-button" href="{{ route('observations.create') }}">Observation</a>
      <a class="secondary-button" href="{{ route('permits.create') }}">Permit</a>
      <a class="primary-button" href="{{ route('reporting.index') }}">Review reports</a>
    </div>
  </div>

  <div class="dashboard-focus">
    <a class="focus-card" href="{{ route('reporting.index') }}">
      <span>Reports</span>
      <strong>{{ $metrics['openReports'] }}</strong>
      <small>awaiting screening</small>
    </a>
    <a class="focus-card" href="{{ route('incidents.index') }}">
      <span>Incidents</span>
      <strong>{{ $metrics['openIncidents'] }}</strong>
      <small>open accepted events</small>
    </a>
    <a class="focus-card" href="{{ route('permits.index') }}">
      <span>Permits</span>
      <strong>{{ $metrics['activePermits'] }}</strong>
      <small>approved, active or suspended</small>
    </a>
    <a class="focus-card urgent" href="{{ route('actions.index') }}">
      <span>Overdue actions</span>
      <strong>{{ $metrics['overdueActions'] }}</strong>
      <small>actions past due</small>
    </a>
  </div>

  <div class="dashboard-layout">
    <article class="panel wide dashboard-panel">
      <div class="panel-header">
        <h2>Priority work</h2>
        <a class="secondary-button" href="{{ route('my-work.index') }}">Open queue</a>
      </div>
      <div class="priority-list">
        @forelse ($priorityReports as $report)
          <a href="{{ route('reporting.show', $report) }}">
            <span class="status-pill">Report</span>
            <strong>{{ $report->reference }}</strong>
            <span>{{ $report->title }}</span>
            <small>{{ $report->status }}</small>
          </a>
        @empty
          <div class="quiet-row"><strong>No reports waiting</strong><span>Screening queue is clear.</span></div>
        @endforelse

        @forelse ($priorityPermits as $permit)
          <a href="{{ route('permits.show', $permit) }}">
            <span class="status-pill">Permit</span>
            <strong>{{ $permit->reference }}</strong>
            <span>{{ $permit->title }}</span>
            <small>{{ $permit->status }}</small>
          </a>
        @empty
          <div class="quiet-row"><strong>No permits waiting</strong><span>Control of Work has no immediate queue.</span></div>
        @endforelse

        @forelse ($priorityActions as $action)
          <a href="{{ route('actions.index', ['search' => $action->reference]) }}">
            <span class="status-pill">Action</span>
            <strong>{{ $action->reference }}</strong>
            <span>{{ $action->title }}</span>
            <small>{{ $action->priority }} · {{ $action->status }}</small>
          </a>
        @empty
          <div class="quiet-row"><strong>No open actions</strong><span>There are no action records in the active queue.</span></div>
        @endforelse
      </div>
    </article>

    <aside class="panel dashboard-panel">
      <div class="panel-header">
        <h2>Operational signals</h2>
        <span class="status-pill">Live records</span>
      </div>
      <ul class="signal-list compact-signals">
        @foreach ($workload as $label => $value)
          <li><span>{{ $label }}</span><strong>{{ $value }}</strong></li>
        @endforeach
      </ul>
    </aside>

    <article class="panel wide dashboard-panel">
      <div class="panel-header">
        <h2>Safety flow</h2>
        <a class="secondary-button" href="{{ route('observations.index') }}">Observations</a>
      </div>
      <div class="compact-flow">
        @foreach ($workflowStages as $stage)
          <div>
            <strong>{{ $stage }}</strong>
            <span>{{ $occurrences->where('workflow_stage', $stage)->count() }}</span>
          </div>
        @endforeach
      </div>
      <div class="simple-record-list">
        @forelse ($occurrences->take(4) as $occurrence)
          <a href="{{ $occurrence->record_family === 'Observation' ? route('observations.show', $occurrence) : route('occurrences.show', $occurrence) }}">
            <strong>{{ $occurrence->reference }}</strong>
            <span>{{ $occurrence->title }}</span>
            <small>{{ $occurrence->workflow_stage }} · {{ $occurrence->risk_rating }}</small>
          </a>
        @empty
          <div class="quiet-row"><strong>No open safety records</strong><span>New reports and observations will appear here.</span></div>
        @endforelse
      </div>
    </article>

    <aside class="panel dashboard-panel">
      <div class="panel-header">
        <h2>System readiness</h2>
        <a class="secondary-button" href="{{ route('platform.index') }}">Configure</a>
      </div>
      <ul class="readiness-list">
        <li><span>Report designs</span><strong>{{ $metrics['reportDesigns'] }}</strong><small>Published layouts</small></li>
        <li><span>Notification rules</span><strong>{{ $metrics['notificationDesigns'] }}</strong><small>Published templates</small></li>
        <li><span>High risks</span><strong>{{ $metrics['highRisks'] }}</strong><small>Risk register</small></li>
        <li><span>Open NCR / CAPA</span><strong>{{ $metrics['openNcr'] + $metrics['openCapa'] }}</strong><small>Quality follow-up</small></li>
      </ul>
      <div class="coverage-strip" aria-label="Product coverage">
        <span>SHELL</span>
        <span>OBS</span>
        <span>REPORT</span>
        <span>ASSURE</span>
      </div>
    </aside>
  </div>
</section>
@endsection
