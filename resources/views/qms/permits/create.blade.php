@extends('qms.layout', ['title' => 'New Permit - QMS'])

@section('content')
<section class="view active-view observation-create">
  <div class="page-title">
    <div>
      <p class="eyebrow">Control of Work</p>
      <h1>New Permit</h1>
    </div>
    <a class="secondary-button" href="{{ route('permits.index') }}">Permit list</a>
  </div>

  @if ($errors->any())
    <div class="form-error">Please review the required fields and submit again.</div>
  @endif

  <form class="observation-wizard" method="POST" action="{{ route('permits.store') }}" data-observation-wizard>
    @csrf
    <nav class="wizard-steps" aria-label="Permit pages">
      @foreach (['Work Information', 'Description & Risk', 'Controls', 'Approval', 'Review & Submit'] as $index => $step)
        <button type="button" class="{{ $index === 0 ? 'active' : '' }}" data-step-button="{{ $index }}"><span>{{ $index + 1 }}</span>{{ $step }}</button>
      @endforeach
    </nav>

    <div class="wizard-layout">
      <main class="panel form-panel">
        <section class="wizard-page active" data-step-page="0">
          <h2>Work Information</h2>
          <div class="form-grid">
            <label>Permit type<select name="permit_type" required>@foreach ($permitTypes as $key => $label)<option value="{{ $key }}" @selected(old('permit_type') === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Title<input name="title" value="{{ old('title') }}" placeholder="Short work title" required></label>
            <label>Requester<input name="requester" list="userPicker" value="{{ old('requester', auth()->user()->name) }}" required></label>
            <label>Department<input name="department" list="departmentPicker" value="{{ old('department') }}" required></label>
            <label>Contractor<input name="contractor" value="{{ old('contractor') }}" placeholder="Company or contractor name"></label>
            <label>Area<input name="area" list="areaPicker" value="{{ old('area') }}" required></label>
            <label>Unit<input name="unit" list="unitPicker" value="{{ old('unit') }}" required></label>
            <label>Asset / equipment<input name="asset" value="{{ old('asset') }}" placeholder="Asset tag, aircraft, vehicle or equipment"></label>
          </div>
        </section>

        <section class="wizard-page" data-step-page="1">
          <h2>Description & Risk</h2>
          <div class="form-grid">
            <label>Planned start date<input type="date" name="planned_start_date" value="{{ old('planned_start_date', now()->toDateString()) }}" required></label>
            <label>Planned start time<input type="time" name="planned_start_time" value="{{ old('planned_start_time', now()->format('H:i')) }}" required></label>
            <label>Planned end date<input type="date" name="planned_end_date" value="{{ old('planned_end_date', now()->toDateString()) }}" required></label>
            <label>Planned end time<input type="time" name="planned_end_time" value="{{ old('planned_end_time', now()->addHours(8)->format('H:i')) }}" required></label>
            <label>Initial risk<select name="risk_rating" required>@foreach (['Low', 'Medium', 'High', 'Critical'] as $risk)<option @selected(old('risk_rating', 'Medium') === $risk)>{{ $risk }}</option>@endforeach</select></label>
            <label>Residual risk<select name="residual_risk" required>@foreach (['Low', 'Medium', 'High', 'Critical'] as $risk)<option @selected(old('residual_risk', 'Low') === $risk)>{{ $risk }}</option>@endforeach</select></label>
          </div>
          <label>Work description<textarea name="work_description" rows="7" required placeholder="Describe the work, method, boundary and expected completion state.">{{ old('work_description') }}</textarea></label>
          <fieldset class="checkbox-grid">
            <legend>Hazards</legend>
            @foreach ($hazards as $hazard)
              <label><input type="checkbox" name="hazards[]" value="{{ $hazard }}" @checked(in_array($hazard, old('hazards', []), true))> {{ $hazard }}</label>
            @endforeach
          </fieldset>
        </section>

        <section class="wizard-page" data-step-page="2">
          <h2>Controls</h2>
          <div class="form-grid">
            <label class="inline-check"><input type="checkbox" name="isolation_required" value="1" @checked(old('isolation_required'))> Isolation required</label>
            <label class="inline-check"><input type="checkbox" name="gas_test_required" value="1" @checked(old('gas_test_required'))> Gas test required</label>
            <label class="inline-check"><input type="checkbox" name="fire_watch_required" value="1" @checked(old('fire_watch_required'))> Fire watch required</label>
            <label class="inline-check"><input type="checkbox" name="standby_required" value="1" @checked(old('standby_required'))> Standby person required</label>
          </div>
          <fieldset class="checkbox-grid">
            <legend>Required controls</legend>
            @foreach ($controls as $control)
              <label><input type="checkbox" name="controls[]" value="{{ $control }}" @checked(in_array($control, old('controls', []), true))> {{ $control }}</label>
            @endforeach
          </fieldset>
          <fieldset class="checkbox-grid">
            <legend>Required PPE</legend>
            @foreach ($ppe as $item)
              <label><input type="checkbox" name="required_ppe[]" value="{{ $item }}" @checked(in_array($item, old('required_ppe', []), true))> {{ $item }}</label>
            @endforeach
          </fieldset>
          <label>LOTO points<textarea name="loto_points" rows="4" placeholder="One isolation or lock point per line.">{{ old('loto_points') }}</textarea></label>
        </section>

        <section class="wizard-page" data-step-page="3">
          <h2>Approval</h2>
          <div class="form-grid">
            <label>Owner<input name="owner" list="userPicker" value="{{ old('owner') }}" required></label>
            <label>Current approver<input name="current_approver" list="userPicker" value="{{ old('current_approver') }}" placeholder="Permit reviewer"></label>
          </div>
          <fieldset class="checkbox-grid">
            <legend>Required competence</legend>
            @foreach ($training as $item)
              <label><input type="checkbox" name="required_training[]" value="{{ $item }}" @checked(in_array($item, old('required_training', []), true))> {{ $item }}</label>
            @endforeach
          </fieldset>
          <label>Linked documents<textarea name="linked_documents" rows="4" placeholder="Procedure, method statement, JSA or drawing reference. One per line.">{{ old('linked_documents') }}</textarea></label>
        </section>

        <section class="wizard-page" data-step-page="4">
          <h2>Review & Submit</h2>
          <div class="review-checklist">
            <div><strong>Work Information</strong><span>Requester, area, unit, asset and contractor are clear.</span></div>
            <div><strong>Risk</strong><span>Hazards and residual risk have been reviewed.</span></div>
            <div><strong>Controls</strong><span>Isolation, gas testing, fire watch and standby requirements are set.</span></div>
            <div><strong>Approval</strong><span>Owner and reviewer are assigned before issue.</span></div>
          </div>
          <div class="button-row">
            <button class="secondary-button" name="submit_action" value="draft">Save Draft</button>
            <button class="primary-button" name="submit_action" value="submit">Submit for Review</button>
          </div>
        </section>

        <div class="wizard-actions">
          <button type="button" class="secondary-button" data-step-prev>Back</button>
          <button type="button" class="primary-button" data-step-next>Next</button>
        </div>
      </main>

      <aside class="panel wizard-summary">
        <h2>Permit flow</h2>
        <ul class="check-list">
          <li>Drafts are editable before formal review.</li>
          <li>Approved permits must be issued before work starts.</li>
          <li>Suspended work must not continue until re-issued.</li>
          <li>Closeout records the final work condition.</li>
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
