@extends('qms.layout', ['title' => 'Observations - QMS'])

@section('content')
<section class="view active-view">
  <div class="page-title">
    <div>
      <p class="eyebrow">Safety</p>
      <h1>Observations</h1>
    </div>
    <a class="primary-button" href="{{ route('observations.create') }}">New observation</a>
  </div>

  <div class="metric-grid compact-metrics">
    <article class="metric"><span>New</span><strong>{{ $counts['new'] }}</strong><small>Waiting for HSE review</small></article>
    <article class="metric"><span>Valid</span><strong>{{ $counts['valid'] }}</strong><small>Reviewed observations</small></article>
    <article class="metric"><span>More information</span><strong>{{ $counts['info'] }}</strong><small>Returned to reporter</small></article>
    <article class="metric"><span>Open actions</span><strong>{{ $counts['actions'] }}</strong><small>Action Tracker</small></article>
  </div>

  <form class="filter-bar unified-filter" method="GET" action="{{ route('observations.index') }}">
    <input name="search" type="search" value="{{ $filters['search'] }}" placeholder="Search observation, area, unit, location, observer">
    <select name="status">
      <option value="">Any status</option>
      @foreach (['Submitted', 'Accepted', 'Returned for Information', 'Closed'] as $status)
        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
      @endforeach
    </select>
    <select name="review_decision">
      <option value="">Any review</option>
      @foreach (['Valid', 'Not Valid', 'Needs More Information'] as $decision)
        <option value="{{ $decision }}" @selected($filters['review_decision'] === $decision)>{{ $decision }}</option>
      @endforeach
    </select>
    <select name="risk_rating">
      <option value="">Any risk</option>
      @foreach (['Low', 'Medium', 'High', 'Critical'] as $risk)
        <option value="{{ $risk }}" @selected($filters['risk_rating'] === $risk)>{{ $risk }}</option>
      @endforeach
    </select>
    <button class="secondary-button">Filter</button>
    <a class="secondary-button" href="{{ route('observations.index') }}">Clear</a>
  </form>

  <article class="panel wide">
    <div class="panel-header"><h2>Observation list</h2><span class="status-pill">Unsafe Act / Unsafe Condition</span></div>
    <div class="table-panel">
      <table>
        <thead><tr><th>Reference</th><th>Type</th><th>Area / Unit</th><th>Location</th><th>Review</th><th>Risk</th><th>Status</th></tr></thead>
        <tbody>
          @forelse ($observations as $observation)
            <tr>
              <td><a href="{{ route('observations.show', $observation) }}">{{ $observation->reference }}</a></td>
              <td>{{ $observation->observation_type }}</td>
              <td>{{ $observation->area }} / {{ $observation->unit }}</td>
              <td>{{ $observation->location }}</td>
              <td>{{ $observation->review_decision ?? 'Pending' }}</td>
              <td><span class="risk-badge">{{ $observation->risk_rating }}</span></td>
              <td><span class="status-pill">{{ $observation->status }}</span></td>
            </tr>
          @empty
            <tr><td colspan="7">No observations found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $observations->links() }}</div>
  </article>
</section>
@endsection
