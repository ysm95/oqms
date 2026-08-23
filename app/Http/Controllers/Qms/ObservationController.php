<?php

namespace App\Http\Controllers\Qms;

use App\Http\Controllers\Controller;
use App\Models\QmsAction;
use App\Models\QmsAuditLog;
use App\Models\QmsLocation;
use App\Models\QmsOccurrence;
use App\Models\QmsRecordNote;
use App\Models\User;
use App\Support\QmsAuditTrail;
use App\Support\QmsNumbering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        $query = QmsOccurrence::query()
            ->where('record_family', 'Observation')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('reference', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('observation_type', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('observer', 'like', "%{$search}%");
            });
        }

        foreach (['status', 'review_decision', 'risk_rating'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter));
            }
        }

        return view('qms.observations.index', [
            'observations' => $query->paginate(12)->withQueryString(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'review_decision' => $request->string('review_decision')->toString(),
                'risk_rating' => $request->string('risk_rating')->toString(),
            ],
            'counts' => [
                'new' => QmsOccurrence::where('record_family', 'Observation')->where('status', 'Submitted')->count(),
                'valid' => QmsOccurrence::where('record_family', 'Observation')->where('review_decision', 'Valid')->count(),
                'info' => QmsOccurrence::where('record_family', 'Observation')->where('review_decision', 'Needs More Information')->count(),
                'actions' => QmsAction::whereIn('source_reference', QmsOccurrence::where('record_family', 'Observation')->select('reference'))->whereNotIn('status', ['Closed', 'Verified'])->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('qms.observations.create', [
            'locations' => QmsLocation::where('active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'areas' => ['Flight Operations', 'Ground Operations', 'Engineering', 'Cabin', 'Cargo', 'Catering', 'Head Office', 'Station', 'Training'],
            'units' => ['MCT', 'SLL', 'DXB', 'OCC', 'Ramp', 'Hangar', 'HQ1', 'HQ2'],
            'departments' => ['Safety', 'HSE', 'Quality', 'Engineering', 'Flight Operations', 'Ground Operations', 'Cabin Services', 'Cargo'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'observation_type' => ['required', 'in:Unsafe Act,Unsafe Condition'],
            'area' => ['required', 'string', 'max:160'],
            'unit' => ['required', 'string', 'max:120'],
            'observed_on' => ['required', 'date'],
            'observed_at' => ['required', 'date_format:H:i'],
            'observer' => ['required', 'string', 'max:160'],
            'location' => ['required', 'string', 'max:160'],
            'exact_location' => ['nullable', 'string', 'max:255'],
            'department_name' => ['required', 'string', 'max:160'],
            'confidential' => ['nullable', 'boolean'],
            'event_title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'potential_consequence' => ['nullable', 'string', 'max:3000'],
            'immediate_corrective_action' => ['nullable', 'string', 'max:3000'],
            'temporary_control' => ['nullable', 'string', 'max:3000'],
            'action_taken' => ['nullable', 'array'],
            'action_taken.*' => ['string', 'max:160'],
        ]);

        $observation = DB::transaction(function () use ($request, $data) {
            $observation = QmsOccurrence::create([
                'reference' => QmsNumbering::next('NUM-OBS', 'Observations', 'OBS'),
                'record_family' => 'Observation',
                'title' => $data['event_title'],
                'type' => 'Observation',
                'observation_type' => $data['observation_type'],
                'area' => $data['area'],
                'unit' => $data['unit'],
                'event_date' => $data['observed_on'],
                'observed_on' => $data['observed_on'],
                'observed_at' => $data['observed_at'],
                'location' => $data['location'],
                'exact_location' => $data['exact_location'] ?? null,
                'department_name' => $data['department_name'],
                'reported_by' => $data['observer'],
                'observer' => $data['observer'],
                'description' => $data['description'],
                'potential_consequence' => $data['potential_consequence'] ?? null,
                'immediate_corrective_action' => $data['immediate_corrective_action'] ?? null,
                'temporary_control' => $data['temporary_control'] ?? null,
                'action_taken' => $data['action_taken'] ?? [],
                'status' => 'Submitted',
                'workflow_stage' => 'HSE Review',
                'risk_rating' => 'Medium',
                'confidential' => (bool) ($data['confidential'] ?? false),
                'reported_at' => now(),
            ]);

            QmsAuditTrail::record($request, $observation, 'observation_submitted', [], [
                'observation_type' => $observation->observation_type,
                'workflow_stage' => $observation->workflow_stage,
            ], 'Observation submitted for HSE review.');

            return $observation;
        });

        return redirect()->route('observations.show', $observation)->with('status', 'Observation submitted for HSE review.');
    }

    public function show(QmsOccurrence $observation)
    {
        abort_unless($observation->record_family === 'Observation', 404);

        return view('qms.observations.show', [
            'observation' => $observation,
            'actions' => QmsAction::where('source_reference', $observation->reference)->latest()->get(),
            'notes' => QmsRecordNote::where('record_type', QmsOccurrence::class)->where('record_id', $observation->id)->latest()->get(),
            'auditLogs' => QmsAuditLog::where('auditable_type', QmsOccurrence::class)->where('auditable_id', $observation->id)->latest()->get(),
        ]);
    }

    public function review(Request $request, QmsOccurrence $observation)
    {
        abort_unless($observation->record_family === 'Observation', 404);

        $data = $request->validate([
            'review_decision' => ['required', 'in:Valid,Not Valid,Needs More Information'],
            'reviewer_comments' => ['required', 'string', 'max:3000'],
            'risk_rating' => ['required', 'in:Low,Medium,High,Critical'],
            'reporter_visible_message' => ['nullable', 'string', 'max:1000'],
            'action_required' => ['nullable', 'boolean'],
        ]);

        $oldValues = $observation->only(['review_decision', 'reviewer_comments', 'risk_rating', 'status', 'workflow_stage', 'action_required']);
        $status = match ($data['review_decision']) {
            'Valid' => 'Accepted',
            'Not Valid' => 'Closed',
            default => 'Returned for Information',
        };

        $stage = ($data['review_decision'] === 'Valid' && ($data['action_required'] ?? false)) ? 'Action Tracker' : 'HSE Review';
        if ($data['review_decision'] === 'Not Valid') {
            $stage = 'Closed';
        }

        $observation->update([
            'review_decision' => $data['review_decision'],
            'reviewer_comments' => $data['reviewer_comments'],
            'risk_rating' => $data['risk_rating'],
            'reporter_visible_message' => $data['reporter_visible_message'] ?? null,
            'action_required' => (bool) ($data['action_required'] ?? false),
            'reviewer_name' => $request->user()?->name ?? 'HSE Reviewer',
            'reviewed_at' => now(),
            'status' => $status,
            'workflow_stage' => $stage,
        ]);

        QmsAuditTrail::record($request, $observation, 'hse_review_completed', $oldValues, [
            'review_decision' => $observation->review_decision,
            'risk_rating' => $observation->risk_rating,
            'action_required' => $observation->action_required,
        ], 'HSE review completed for observation.');

        return back()->with('status', 'HSE review saved.');
    }

    public function storeAction(Request $request, QmsOccurrence $observation)
    {
        abort_unless($observation->record_family === 'Observation', 404);

        $data = $request->validate([
            'action_type' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:3000'],
            'owner' => ['required', 'string', 'max:160'],
            'responsible_department' => ['nullable', 'string', 'max:160'],
            'due_date' => ['required', 'date'],
            'priority' => ['required', 'in:Low,Medium,High,Critical'],
            'evidence_required' => ['nullable', 'boolean'],
            'verification_required' => ['nullable', 'boolean'],
        ]);

        $action = QmsAction::create([
            'reference' => QmsNumbering::next('NUM-ACT', 'Actions', 'ACT'),
            'source_reference' => $observation->reference,
            'title' => $data['action_type'] . ' - ' . $observation->reference,
            'description' => $data['description'],
            'required_outcome' => ($data['verification_required'] ?? false) ? 'Verify control effectiveness before closure.' : 'Complete assigned follow-up.',
            'owner' => $data['owner'],
            'responsible_department' => $data['responsible_department'] ?? $observation->department_name,
            'priority' => $data['priority'],
            'risk_relevance' => $observation->risk_rating,
            'evidence_required' => (bool) ($data['evidence_required'] ?? false),
            'status' => 'Open',
            'progress' => 0,
            'due_date' => $data['due_date'],
            'assigned_at' => now(),
            'evidence' => null,
        ]);

        $observation->update([
            'action_required' => true,
            'workflow_stage' => 'Action Tracker',
        ]);

        QmsAuditTrail::record($request, $observation, 'observation_action_created', [], [
            'action_reference' => $action->reference,
            'owner' => $action->owner,
            'priority' => $action->priority,
        ], 'Action Tracker entry created from Observation.');

        return back()->with('status', 'Action entry added to Observation.');
    }
}
