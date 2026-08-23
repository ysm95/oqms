@extends('qms.layout', ['title' => 'Permit Issuing - QMS'])

@section('content')
<section class="view active-view">
  <div class="page-title">
    <div>
      <p class="eyebrow">Safety</p>
      <h1>Permit Issuing</h1>
    </div>
    <a class="primary-button" href="{{ route('permits.create') }}">New permit</a>
  </div>

  <div class="metric-grid compact-metrics">
    <article class="metric"><span>Active</span><strong>{{ $counts['active'] }}</strong><small>Issued work permits</small></article>
    <article class="metric"><span>Pending</span><strong>{{ $counts['pending'] }}</strong><small>Draft, review or approved</small></article>
    <article class="metric"><span>Expiring</span><strong>{{ $counts['expiring'] }}</strong><small>Due within 24 hours</small></article>
    <article class="metric"><span>Suspended</span><strong>{{ $counts['suspended'] }}</strong><small>Work stopped or held</small></article>
  </div>

  <form class="filter-bar unified-filter" method="GET" action="{{ route('permits.index') }}">
    <input name="search" type="search" value="{{ $filters['search'] }}" placeholder="Search permit, area, requester, contractor">
    <select name="status">
      <option value="">Any status</option>
      @foreach ($statuses as $status)
        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
      @endforeach
    </select>
    <select name="permit_type">
      <option value="">Any type</option>
      @foreach ($permitTypes as $key => $label)
        <option value="{{ $key }}" @selected($filters['permit_type'] === $key)>{{ $label }}</option>
      @endforeach
    </select>
    <input name="area" value="{{ $filters['area'] }}" placeholder="Area">
    <button class="secondary-button">Filter</button>
    <a class="secondary-button" href="{{ route('permits.index') }}">Clear</a>
  </form>

  <article class="panel wide">
    <div class="panel-header"><h2>Permit board</h2><span class="status-pill">Control of Work</span></div>
    <div class="kanban">
      @foreach ($board as $status => $items)
        <div class="kanban-column">
          <h3>{{ $status }}</h3>
          @forelse ($items as $permit)
            <a class="kanban-card" href="{{ route('permits.show', $permit) }}">
              <strong>{{ $permit->reference }}</strong>
              <span>{{ $permit->title }}</span>
              <small>{{ $permit->area ?: 'Area not set' }} · {{ $permit->risk_rating }}</small>
            </a>
          @empty
            <p>No permits.</p>
          @endforelse
        </div>
      @endforeach
    </div>
  </article>

  <article class="panel wide">
    <div class="panel-header"><h2>Permit list</h2><span class="status-pill">Issue, suspend, close</span></div>
    <div class="table-panel">
      <table>
        <thead><tr><th>Reference</th><th>Type</th><th>Work</th><th>Area / Unit</th><th>Owner</th><th>Planned Window</th><th>Risk</th><th>Status</th></tr></thead>
        <tbody>
          @forelse ($permits as $permit)
            <tr>
              <td><a href="{{ route('permits.show', $permit) }}">{{ $permit->reference }}</a></td>
              <td>{{ $permit->permit_type }}</td>
              <td>{{ $permit->title }}</td>
              <td>{{ $permit->area }} / {{ $permit->unit }}</td>
              <td>{{ $permit->owner ?: 'Unassigned' }}</td>
              <td>{{ optional($permit->planned_start_at)->format('Y-m-d H:i') }} - {{ optional($permit->planned_end_at)->format('Y-m-d H:i') }}</td>
              <td><span class="risk-badge">{{ $permit->risk_rating }}</span></td>
              <td><span class="status-pill">{{ $permit->status }}</span></td>
            </tr>
          @empty
            <tr><td colspan="8">No permits found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $permits->links() }}</div>
  </article>
</section>
@endsection
