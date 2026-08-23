@extends('qms.layout', ['title' => 'Intelligence - QMS'])

@section('content')
<section class="view active-view">
  <div class="page-title"><div><p class="eyebrow">Executive intelligence</p><h1>System command intelligence</h1></div><span class="status-pill success">Cross-module</span></div>

  <div class="metric-grid compact-metrics">
    @foreach ($signals as $label => $value)
      <article class="metric"><span>{{ $label }}</span><strong>{{ $value }}</strong><small>Live register signal</small></article>
    @endforeach
  </div>

  <div class="content-grid">
    <article class="panel wide">
      <div class="panel-header"><h2>Readiness map</h2><span class="status-pill">Operational coverage</span></div>
      <ul class="coverage-list">
        @foreach ($readiness as $label => $ready)
          <li><strong>{{ $ready ? 'Ready' : 'Gap' }}</strong><span>{{ $label }}</span></li>
        @endforeach
      </ul>
    </article>

    <article class="panel">
      <h2>Why this is stronger</h2>
      <ul class="check-list">
        <li>Traceability across occurrence, CAPA, risk, audit, document, and supplier records</li>
        <li>Controlled AI is blocked until the paid secured provider is approved</li>
        <li>Configurable form and workflow definitions are versioned</li>
        <li>Public anonymous and confidential reporting is separated from authenticated workspace</li>
      </ul>
    </article>

    <article class="panel wide">
      <div class="panel-header"><h2>Traceability graph</h2><span class="status-pill">Linked evidence</span></div>
      <div class="table-panel"><table><thead><tr><th>Source</th><th>Relationship</th><th>Target</th></tr></thead><tbody>
        @foreach ($links as $link)
          <tr><td>{{ $link->source_reference }}</td><td>{{ $link->relationship }}</td><td>{{ $link->target_reference }}</td></tr>
        @endforeach
      </tbody></table></div>
    </article>
  </div>
</section>
@endsection
