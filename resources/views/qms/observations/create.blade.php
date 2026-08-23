@extends('qms.layout', ['title' => 'New Observation - QMS'])

@section('content')
<section class="view active-view observation-create">
  <div class="page-title">
    <div>
      <p class="eyebrow">Safety observation</p>
      <h1>New Observation</h1>
    </div>
    <a class="secondary-button" href="{{ route('observations.index') }}">Observation list</a>
  </div>

  @if ($errors->any())
    <div class="form-error">Please review the required fields and submit again.</div>
  @endif

  <form class="observation-wizard" method="POST" action="{{ route('observations.store') }}" data-observation-wizard>
    @csrf
    <nav class="wizard-steps" aria-label="Observation pages">
      @foreach (['Information', 'Description', 'Action Taken', 'Attachments', 'Review & Submit'] as $index => $step)
        <button type="button" class="{{ $index === 0 ? 'active' : '' }}" data-step-button="{{ $index }}"><span>{{ $index + 1 }}</span>{{ $step }}</button>
      @endforeach
    </nav>

    <div class="wizard-layout">
      <main class="panel form-panel">
        <section class="wizard-page active" data-step-page="0">
          <h2>Information</h2>
          <p class="form-hint">Start with who, where and when. This keeps Observation fast for reviewers.</p>
          <fieldset class="choice-cards">
            <legend>Observation Type</legend>
            <label><input type="radio" name="observation_type" value="Unsafe Act" @checked(old('observation_type') === 'Unsafe Act') required><span>Unsafe Act</span><small>Behavior or action that may create harm.</small></label>
            <label><input type="radio" name="observation_type" value="Unsafe Condition" @checked(old('observation_type', 'Unsafe Condition') === 'Unsafe Condition') required><span>Unsafe Condition</span><small>Workplace, equipment or environment condition.</small></label>
          </fieldset>
          <div class="form-grid">
            <label>Area<input name="area" list="areaPicker" value="{{ old('area') }}" required></label>
            <label>Unit<input name="unit" list="unitPicker" value="{{ old('unit') }}" required></label>
            <label>Date<input type="date" name="observed_on" value="{{ old('observed_on', now()->toDateString()) }}" required></label>
            <label>Time<input type="time" name="observed_at" value="{{ old('observed_at', now()->format('H:i')) }}" required></label>
            <label>Observer<input name="observer" list="userPicker" value="{{ old('observer', auth()->user()->name) }}" required></label>
            <label>Location<input name="location" list="locationPicker" value="{{ old('location') }}" required></label>
            <label>Exact location<input name="exact_location" value="{{ old('exact_location') }}" placeholder="Gate, bay, stand, room or work area"></label>
            <label>Department<input name="department_name" list="departmentPicker" value="{{ old('department_name') }}" required></label>
          </div>
          <label class="inline-check"><input type="checkbox" name="confidential" value="1" @checked(old('confidential'))> Treat this observation as confidential</label>
        </section>

        <section class="wizard-page" data-step-page="1">
          <h2>Description</h2>
          <div class="form-grid single">
            <label>Observation title<input name="event_title" value="{{ old('event_title') }}" placeholder="Short factual summary" required></label>
            <label>What was observed?<textarea name="description" rows="7" required placeholder="Describe exactly what was observed.">{{ old('description') }}</textarea></label>
            <label>Potential consequence<textarea name="potential_consequence" rows="4" placeholder="What could happen if this is not controlled?">{{ old('potential_consequence') }}</textarea></label>
          </div>
        </section>

        <section class="wizard-page" data-step-page="2">
          <h2>Action Taken</h2>
          <fieldset class="checkbox-grid">
            <legend>Immediate actions</legend>
            @foreach (['Area made safe', 'Supervisor informed', 'Temporary control applied', 'Work stopped', 'No immediate action possible'] as $action)
              <label><input type="checkbox" name="action_taken[]" value="{{ $action }}" @checked(in_array($action, old('action_taken', []), true))> {{ $action }}</label>
            @endforeach
          </fieldset>
          <label>Immediate action taken<textarea name="immediate_corrective_action" rows="5" placeholder="What was done immediately?">{{ old('immediate_corrective_action') }}</textarea></label>
          <label>Temporary control<textarea name="temporary_control" rows="4" placeholder="Temporary barrier, warning, isolation, briefing or other control.">{{ old('temporary_control') }}</textarea></label>
        </section>

        <section class="wizard-page" data-step-page="3">
          <h2>Attachments</h2>
          <div class="attachment-placeholder">
            <strong>Attachments will be linked after the record is created.</strong>
            <span>Use this page to confirm which evidence is available: photos, documents, location notes or other support.</span>
          </div>
          <p class="form-hint">Do not upload unrelated or personal files. Confidential observations are restricted to authorized reviewers.</p>
        </section>

        <section class="wizard-page" data-step-page="4">
          <h2>Review and Submit</h2>
          <div class="review-checklist">
            <div><strong>Information</strong><span>Area, unit, date, time, observer and department are required.</span></div>
            <div><strong>Description</strong><span>Keep the description factual and clear.</span></div>
            <div><strong>Action Taken</strong><span>Record what was done immediately, even if no action was possible.</span></div>
            <div><strong>After submit</strong><span>HSE review decides validity and whether follow-up actions are needed.</span></div>
          </div>
          <button class="primary-button">Submit Observation</button>
        </section>

        <div class="wizard-actions">
          <button type="button" class="secondary-button" data-step-prev>Back</button>
          <button type="button" class="primary-button" data-step-next>Next</button>
        </div>
      </main>

      <aside class="panel wizard-summary">
        <h2>Draft summary</h2>
        <ul class="check-list">
          <li>Observation stays separate from Incident unless escalated.</li>
          <li>HSE review is required before actions are created.</li>
          <li>Reporter-visible updates stay simple.</li>
        </ul>
      </aside>
    </div>

    <datalist id="areaPicker">@foreach ($areas as $area)<option value="{{ $area }}"></option>@endforeach</datalist>
    <datalist id="unitPicker">@foreach ($units as $unit)<option value="{{ $unit }}"></option>@endforeach</datalist>
    <datalist id="departmentPicker">@foreach ($departments as $department)<option value="{{ $department }}"></option>@endforeach</datalist>
    <datalist id="userPicker">@foreach ($users as $user)<option value="{{ $user->name }}"></option>@endforeach</datalist>
    <datalist id="locationPicker">@foreach ($locations as $location)<option value="{{ $location->name }}"></option>@endforeach</datalist>
  </form>
</section>
@endsection
