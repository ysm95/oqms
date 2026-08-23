@extends('reporter.layout')

@section('title', $reportType->title)
@section('screen-title', $reportType->title)

@section('content')
  <form class="reporter-form {{ $reportType->report_type_key === 'observation' ? 'observation-wizard' : '' }}" method="POST" action="{{ route('reporter.store', $reportType->report_type_key) }}" @if($reportType->report_type_key === 'observation') data-observation-wizard @endif>
    @csrf
    <input type="hidden" name="form_version" value="{{ $reportType->form_version }}">

    <section class="reporter-form-section">
      <p class="eyebrow">{{ $reportType->module }} · Version {{ $reportType->form_version }}</p>
      <h1>{{ $reportType->title }}</h1>
      <p>{{ $reportType->description }}</p>
    </section>

    @if ($errors->any())
      <div class="form-error">Please review the highlighted fields and submit again.</div>
    @endif

    @if ($reportType->report_type_key === 'observation')
      <nav class="wizard-steps" aria-label="Observation pages">
        @foreach (['Information', 'Description', 'Action Taken', 'Review'] as $index => $step)
          <button type="button" class="{{ $index === 0 ? 'active' : '' }}" data-step-button="{{ $index }}"><span>{{ $index + 1 }}</span>{{ $step }}</button>
        @endforeach
      </nav>

      <section class="wizard-page active" data-step-page="0">
        <fieldset class="choice-cards">
          <legend>Observation Type</legend>
          <label><input type="radio" name="observation_type" value="Unsafe Act" @checked(old('observation_type') === 'Unsafe Act') required><span>Unsafe Act</span><small>Behavior or action that may create harm.</small></label>
          <label><input type="radio" name="observation_type" value="Unsafe Condition" @checked(old('observation_type', 'Unsafe Condition') === 'Unsafe Condition') required><span>Unsafe Condition</span><small>Workplace, equipment or environment condition.</small></label>
        </fieldset>
        <label>Area<input name="area" value="{{ old('area') }}" placeholder="Area"></label>
        <label>Unit<input name="unit" value="{{ old('unit') }}" placeholder="Unit or station"></label>
        <label>Location<input name="location" value="{{ old('location') }}" placeholder="Station, department, area, or route"></label>
      </section>

      <section class="wizard-page" data-step-page="1">
        <label>Title<input name="title" value="{{ old('title') }}" placeholder="Short summary"></label>
        <label>Description<textarea name="description" rows="7" required placeholder="Describe what was observed.">{{ old('description') }}</textarea></label>
        <label>Potential consequence<textarea name="potential_consequence" rows="3" placeholder="What could happen if not controlled?">{{ old('potential_consequence') }}</textarea></label>
      </section>

      <section class="wizard-page" data-step-page="2">
        <fieldset class="checkbox-grid">
          <legend>Immediate actions</legend>
          @foreach (['Area made safe', 'Supervisor informed', 'Temporary control applied', 'No immediate action possible'] as $action)
            <label><input type="checkbox" name="action_taken[]" value="{{ $action }}" @checked(in_array($action, old('action_taken', []), true))> {{ $action }}</label>
          @endforeach
        </fieldset>
        <label>Action taken<textarea name="immediate_corrective_action" rows="4" placeholder="What was done immediately?">{{ old('immediate_corrective_action') }}</textarea></label>
      </section>

      <section class="wizard-page" data-step-page="3">
        @guest
          <label>Reporter name<input name="reporter_name" value="{{ old('reporter_name') }}" placeholder="Optional if anonymous"></label>
          <label>Contact<input name="reporter_contact" value="{{ old('reporter_contact') }}" placeholder="Email or phone, optional"></label>
        @endguest
        @if ($reportType->supports_anonymous)
          <label class="inline-check"><input type="checkbox" name="anonymous" value="1" @checked(old('anonymous'))> Submit anonymously</label>
        @endif
        <label class="inline-check"><input type="checkbox" name="confidential" value="1" @checked(old('confidential'))> Treat as confidential</label>
        <button class="primary-button full">Submit Observation</button>
      </section>

      <div class="wizard-actions">
        <button type="button" class="secondary-button" data-step-prev>Back</button>
        <button type="button" class="primary-button" data-step-next>Next</button>
      </div>
    @else
      <label>Title
        <input name="title" value="{{ old('title') }}" placeholder="Short summary">
        @error('title')<small>{{ $message }}</small>@enderror
      </label>

      <label>Location
        <input name="location" value="{{ old('location') }}" placeholder="Station, department, area, or route">
        @error('location')<small>{{ $message }}</small>@enderror
      </label>

      @guest
        <label>Reporter name
          <input name="reporter_name" value="{{ old('reporter_name') }}" placeholder="Optional if anonymous">
        </label>
        <label>Contact
          <input name="reporter_contact" value="{{ old('reporter_contact') }}" placeholder="Email or phone, optional">
        </label>
      @endguest

      @if ($reportType->supports_anonymous)
        <label class="inline-check"><input type="checkbox" name="anonymous" value="1" @checked(old('anonymous'))> Submit anonymously</label>
      @endif

      <label class="inline-check"><input type="checkbox" name="confidential" value="1" @checked(old('confidential') || $reportType->report_type_key === 'confidential-safety')> Treat as confidential</label>

      <label>Description
        <textarea name="description" rows="8" required placeholder="Describe what happened, what may happen, or what needs review.">{{ old('description') }}</textarea>
        @error('description')<small>{{ $message }}</small>@enderror
      </label>

      <button class="primary-button full">Submit report</button>
    @endif
  </form>
@endsection
