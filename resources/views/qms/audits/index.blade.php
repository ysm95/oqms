@extends('qms.layout', ['title' => 'Audits - QMS'])

@section('content')
<section class="view active-view">
  <div class="page-title"><div><p class="eyebrow">Safety assurance</p><h1>Audits</h1></div><span class="status-pill success">Assurance ready</span></div>

  <form class="filter-bar" method="GET" action="{{ route('audits.index') }}">
    <input name="search" type="search" value="{{ request('search') }}" placeholder="Search audit by %text%, standard, auditor">
    <select name="status"><option value="">All statuses</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
    <button class="secondary-button">Filter</button>
    <a class="secondary-button" href="{{ route('audits.index') }}">Clear</a>
  </form>

  <div class="table-panel"><table><thead><tr><th>Reference</th><th>Audit</th><th>Standard</th><th>Lead auditor</th><th>Status</th><th>Scheduled</th></tr></thead><tbody>
    @foreach ($audits as $audit)
      <tr><td>{{ $audit->reference }}</td><td>{{ $audit->title }}</td><td>{{ $audit->standard }}</td><td>{{ $audit->lead_auditor }}</td><td><span class="status-pill">{{ $audit->status }}</span></td><td>{{ optional($audit->scheduled_date)->format('Y-m-d') }}</td></tr>
    @endforeach
  </tbody></table></div>
  @if ($audits->isEmpty())<div class="empty-state">No audits match this filter.</div>@endif
  <div class="pager">{{ $audits->links() }}</div>
</section>
@endsection
