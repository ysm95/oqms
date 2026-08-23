@extends('reporter.layout')

@section('title', 'QMS Reporter')
@section('screen-title', 'Reporting')

@section('content')
  <section class="reporter-hero">
    <p class="eyebrow">QMS Reporter</p>
    <h1>Report an observation or concern</h1>
    <p>Start with Observation for Unsafe Act or Unsafe Condition. Your receipt shows only reporter-visible updates.</p>
  </section>

  <form class="reporter-search" method="GET" action="{{ route('reporter.home') }}">
    <input type="search" name="search" value="{{ $query }}" placeholder="Search report types">
    <button class="secondary-button">Search</button>
  </form>

  <section class="reporter-card-grid" aria-label="Authorized report types">
    @php
      $filteredReportTypes = $reportTypes->filter(fn ($type) => $query === '' || str_contains(strtolower($type->title . ' ' . $type->type . ' ' . $type->description), strtolower($query)));
    @endphp

    @forelse ($filteredReportTypes as $type)
      <a class="reporter-type-card" href="{{ route('reporter.create', $type->report_type_key) }}">
        <span>{{ $type->report_type_key === 'observation' ? 'OBS' : strtoupper(substr($type->module, 0, 3)) }}</span>
        <strong>{{ $type->title }}</strong>
        <small>{{ $type->description }}</small>
      </a>
    @empty
      <div class="reporter-empty">No authorized report types match this search.</div>
    @endforelse
  </section>

  <section class="reporter-quick-links" id="reporter-help">
    @auth
      <a href="{{ route('reporter.my-reports') }}"><strong>My submitted reports</strong><span>Receipt history and public status</span></a>
    @endauth
    <div><strong>Drafts</strong><span>Mobile drafts stay on your device until submitted</span></div>
    @auth
      <a href="{{ route('feedback.index') }}"><strong>Help and feedback</strong><span>Feedback is separate from safety reporting</span></a>
    @else
      <div><strong>Help</strong><span>For urgent danger, use emergency channels first</span></div>
    @endauth
  </section>
@endsection
