<?php

namespace App\Http\Controllers\Qms;

use App\Http\Controllers\Controller;
use App\Models\QmsAuditLog;
use App\Models\QmsLocation;
use App\Models\QmsPermit;
use App\Models\QmsPermitActivity;
use App\Models\User;
use App\Support\QmsAuditTrail;
use App\Support\QmsNumbering;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermitController extends Controller
{
    public function index(Request $request)
    {
        $query = QmsPermit::query()->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('reference', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('work_description', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('requester', 'like', "%{$search}%")
                    ->orWhere('contractor', 'like', "%{$search}%");
            });
        }

        foreach (['status', 'permit_type', 'area'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter));
            }
        }

        $boardStatuses = ['Draft', 'Pending Review', 'Approved', 'Active', 'Suspended'];

        return view('qms.permits.index', [
            'permits' => $query->paginate(12)->withQueryString(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'permit_type' => $request->string('permit_type')->toString(),
                'area' => $request->string('area')->toString(),
            ],
            'permitTypes' => self::permitTypes(),
            'statuses' => self::statuses(),
            'counts' => [
                'active' => QmsPermit::where('status', 'Active')->count(),
                'pending' => QmsPermit::whereIn('status', ['Draft', 'Pending Review', 'Approved'])->count(),
                'expiring' => QmsPermit::where('status', 'Active')
                    ->whereBetween('planned_end_at', [now(), now()->addHours(24)])
                    ->count(),
                'suspended' => QmsPermit::where('status', 'Suspended')->count(),
            ],
            'board' => collect($boardStatuses)->mapWithKeys(fn ($status) => [
                $status => QmsPermit::where('status', $status)->latest()->limit(5)->get(),
            ]),
        ]);
    }

    public function create()
    {
        return view('qms.permits.create', [
            'permitTypes' => self::permitTypes(),
            'hazards' => self::hazards(),
            'controls' => self::controls(),
            'ppe' => self::ppe(),
            'training' => self::training(),
            'areas' => ['Flight Operations', 'Ground Operations', 'Engineering', 'Cabin', 'Cargo', 'Catering', 'Head Office', 'Station', 'Training'],
            'units' => ['MCT', 'SLL', 'DXB', 'OCC', 'Ramp', 'Hangar', 'HQ1', 'HQ2'],
            'departments' => ['Safety', 'HSE', 'Quality', 'Engineering', 'Flight Operations', 'Ground Operations', 'Cabin Services', 'Cargo'],
            'locations' => QmsLocation::where('active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'submit_action' => ['required', Rule::in(['draft', 'submit'])],
            'permit_type' => ['required', Rule::in(array_keys(self::permitTypes()))],
            'title' => ['required', 'string', 'max:180'],
            'work_description' => ['required', 'string', 'max:5000'],
            'requester' => ['required', 'string', 'max:160'],
            'department' => ['required', 'string', 'max:160'],
            'contractor' => ['nullable', 'string', 'max:160'],
            'area' => ['required', 'string', 'max:160'],
            'unit' => ['required', 'string', 'max:120'],
            'asset' => ['nullable', 'string', 'max:160'],
            'planned_start_date' => ['required', 'date'],
            'planned_start_time' => ['required', 'date_format:H:i'],
            'planned_end_date' => ['required', 'date'],
            'planned_end_time' => ['required', 'date_format:H:i'],
            'risk_rating' => ['required', Rule::in(['Low', 'Medium', 'High', 'Critical'])],
            'residual_risk' => ['required', Rule::in(['Low', 'Medium', 'High', 'Critical'])],
            'owner' => ['required', 'string', 'max:160'],
            'current_approver' => ['nullable', 'string', 'max:160'],
            'hazards' => ['nullable', 'array'],
            'hazards.*' => ['string', 'max:160'],
            'controls' => ['nullable', 'array'],
            'controls.*' => ['string', 'max:160'],
            'required_ppe' => ['nullable', 'array'],
            'required_ppe.*' => ['string', 'max:160'],
            'required_training' => ['nullable', 'array'],
            'required_training.*' => ['string', 'max:160'],
            'loto_points' => ['nullable', 'string', 'max:3000'],
            'linked_documents' => ['nullable', 'string', 'max:3000'],
            'isolation_required' => ['nullable', 'boolean'],
            'gas_test_required' => ['nullable', 'boolean'],
            'fire_watch_required' => ['nullable', 'boolean'],
            'standby_required' => ['nullable', 'boolean'],
        ]);

        $plannedStart = Carbon::parse($data['planned_start_date'] . ' ' . $data['planned_start_time']);
        $plannedEnd = Carbon::parse($data['planned_end_date'] . ' ' . $data['planned_end_time']);

        if ($plannedEnd->lessThanOrEqualTo($plannedStart)) {
            return back()
                ->withErrors(['planned_end_date' => 'Planned end must be after planned start.'])
                ->withInput();
        }

        $permit = DB::transaction(function () use ($request, $data, $plannedStart, $plannedEnd) {
            $status = $data['submit_action'] === 'submit' ? 'Pending Review' : 'Draft';

            $permit = QmsPermit::create([
                'reference' => QmsNumbering::next('NUM-PTW', 'Permit Issuing', 'PTW'),
                'permit_type' => $data['permit_type'],
                'title' => $data['title'],
                'work_description' => $data['work_description'],
                'requester' => $data['requester'],
                'department' => $data['department'],
                'contractor' => $data['contractor'] ?? null,
                'area' => $data['area'],
                'unit' => $data['unit'],
                'asset' => $data['asset'] ?? null,
                'planned_start_at' => $plannedStart,
                'planned_end_at' => $plannedEnd,
                'status' => $status,
                'risk_rating' => $data['risk_rating'],
                'residual_risk' => $data['residual_risk'],
                'owner' => $data['owner'],
                'current_approver' => $data['current_approver'] ?? 'Permit Reviewer',
                'isolation_required' => (bool) ($data['isolation_required'] ?? false),
                'gas_test_required' => (bool) ($data['gas_test_required'] ?? false),
                'fire_watch_required' => (bool) ($data['fire_watch_required'] ?? false),
                'standby_required' => (bool) ($data['standby_required'] ?? false),
                'loto_points' => $this->lines($data['loto_points'] ?? null),
                'hazards' => $data['hazards'] ?? [],
                'controls' => $data['controls'] ?? [],
                'required_ppe' => $data['required_ppe'] ?? [],
                'required_training' => $data['required_training'] ?? [],
                'linked_documents' => $this->lines($data['linked_documents'] ?? null),
                'approval_history' => [],
                'field_checks' => [],
            ]);

            $this->recordActivity($request, $permit, $status === 'Draft' ? 'created_as_draft' : 'submitted_for_review', null, $status, 'Permit created.');

            QmsAuditTrail::record($request, $permit, 'permit_created', [], [
                'status' => $permit->status,
                'permit_type' => $permit->permit_type,
                'risk_rating' => $permit->risk_rating,
            ], 'Permit record created.');

            return $permit;
        });

        return redirect()->route('permits.show', $permit)->with('status', 'Permit saved.');
    }

    public function show(QmsPermit $permit)
    {
        return view('qms.permits.show', [
            'permit' => $permit->load('activities'),
            'auditLogs' => QmsAuditLog::where('auditable_type', QmsPermit::class)
                ->where('auditable_id', $permit->id)
                ->latest()
                ->get(),
            'transitions' => QmsPermit::allowedTransitionsFor($permit->status),
        ]);
    }

    public function transition(Request $request, QmsPermit $permit)
    {
        $this->authorizeTransition($request);

        $data = $request->validate([
            'action' => ['required', Rule::in(['submit', 'approve', 'issue', 'suspend', 'extend', 'close', 'cancel'])],
            'transition_note' => ['nullable', 'string', 'max:3000'],
            'planned_end_at' => ['nullable', 'date'],
            'closeout_summary' => ['nullable', 'string', 'max:3000'],
        ]);

        $allowed = QmsPermit::allowedTransitionsFor($permit->status);
        abort_unless(isset($allowed[$data['action']]), 422, 'This permit action is not allowed from the current status.');

        if ($data['action'] === 'suspend' && blank($data['transition_note'] ?? null)) {
            return back()->withErrors(['transition_note' => 'Suspension reason is required.']);
        }

        if ($data['action'] === 'close' && blank($data['closeout_summary'] ?? null)) {
            return back()->withErrors(['closeout_summary' => 'Closeout summary is required.']);
        }

        DB::transaction(function () use ($request, $permit, $data, $allowed) {
            $fromStatus = $permit->status;
            $toStatus = $allowed[$data['action']];
            $oldValues = $permit->only(['status', 'issued_at', 'closed_at', 'current_approver', 'planned_end_at']);
            $approvalHistory = $permit->approval_history ?? [];
            $fieldChecks = $permit->field_checks ?? [];

            $updates = [
                'status' => $toStatus,
            ];

            if ($data['action'] === 'approve') {
                $updates['issuer'] = $request->user()?->name;
                $updates['current_approver'] = 'Permit Issuer';
                $approvalHistory[] = $this->historyEntry($request, 'Approved', $data['transition_note'] ?? null);
            }

            if ($data['action'] === 'issue') {
                $updates['issued_at'] = now();
                $updates['issuer'] = $request->user()?->name;
                $updates['current_approver'] = null;
                $fieldChecks[] = $this->historyEntry($request, 'Issued to field', $data['transition_note'] ?? null);
            }

            if ($data['action'] === 'suspend') {
                $updates['suspension_reason'] = $data['transition_note'];
                $fieldChecks[] = $this->historyEntry($request, 'Suspended', $data['transition_note']);
            }

            if ($data['action'] === 'extend') {
                $updates['extension_reason'] = $data['transition_note'] ?? null;
                if (! blank($data['planned_end_at'] ?? null)) {
                    $updates['planned_end_at'] = Carbon::parse($data['planned_end_at']);
                }
                $fieldChecks[] = $this->historyEntry($request, 'Extended', $data['transition_note'] ?? null);
            }

            if ($data['action'] === 'close') {
                $updates['closed_at'] = now();
                $updates['closeout_summary'] = $data['closeout_summary'];
                $updates['current_approver'] = null;
                $fieldChecks[] = $this->historyEntry($request, 'Closed', $data['closeout_summary']);
            }

            if ($data['action'] === 'submit') {
                $updates['current_approver'] = $permit->current_approver ?: 'Permit Reviewer';
                $approvalHistory[] = $this->historyEntry($request, 'Submitted', $data['transition_note'] ?? null);
            }

            if ($data['action'] === 'cancel') {
                $updates['current_approver'] = null;
                $approvalHistory[] = $this->historyEntry($request, 'Cancelled', $data['transition_note'] ?? null);
            }

            $updates['approval_history'] = $approvalHistory;
            $updates['field_checks'] = $fieldChecks;

            $permit->update($updates);

            $this->recordActivity($request, $permit, $data['action'], $fromStatus, $toStatus, $data['transition_note'] ?? $data['closeout_summary'] ?? null, $updates);

            QmsAuditTrail::record($request, $permit, 'permit_' . $data['action'], $oldValues, [
                'status' => $permit->status,
                'issued_at' => $permit->issued_at,
                'closed_at' => $permit->closed_at,
                'current_approver' => $permit->current_approver,
                'planned_end_at' => $permit->planned_end_at,
            ], 'Permit lifecycle action completed.');
        });

        return back()->with('status', 'Permit status updated.');
    }

    private function authorizeTransition(Request $request): void
    {
        abort_unless(in_array($request->user()?->qms_role, ['Super Admin', 'Safety Admin', 'HSE Admin'], true), 403);
    }

    private function recordActivity(Request $request, QmsPermit $permit, string $action, ?string $fromStatus, string $toStatus, ?string $note = null, array $payload = []): void
    {
        QmsPermitActivity::create([
            'qms_permit_id' => $permit->id,
            'user_id' => $request->user()?->id,
            'actor' => $request->user()?->name,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'payload' => $payload ?: null,
        ]);
    }

    private function historyEntry(Request $request, string $action, ?string $note): array
    {
        return [
            'action' => $action,
            'actor' => $request->user()?->name,
            'at' => now()->toIso8601String(),
            'note' => $note,
        ];
    }

    private function lines(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private static function permitTypes(): array
    {
        return [
            'Hot Work' => 'Hot Work',
            'Cold Work' => 'Cold Work',
            'Confined Space' => 'Confined Space',
            'Electrical Isolation' => 'Electrical Isolation',
            'Working at Height' => 'Working at Height',
            'Excavation' => 'Excavation',
            'Lifting Operation' => 'Lifting Operation',
            'Line Breaking' => 'Line Breaking',
            'General Work' => 'General Work',
        ];
    }

    private static function statuses(): array
    {
        return ['Draft', 'Pending Review', 'Approved', 'Active', 'Suspended', 'Closed', 'Cancelled'];
    }

    private static function hazards(): array
    {
        return ['Energy isolation', 'Fire or ignition', 'Toxic atmosphere', 'Working at height', 'Dropped objects', 'Vehicle movement', 'Manual handling', 'Chemical exposure', 'Simultaneous operations'];
    }

    private static function controls(): array
    {
        return ['Toolbox talk completed', 'Area barricaded', 'Gas test completed', 'LOTO applied', 'Fire watch assigned', 'Standby person assigned', 'Method statement reviewed', 'Emergency access confirmed', 'Supervisor authorization'];
    }

    private static function ppe(): array
    {
        return ['Safety helmet', 'Safety shoes', 'Eye protection', 'Gloves', 'Hearing protection', 'Harness', 'Respiratory protection', 'FR coverall'];
    }

    private static function training(): array
    {
        return ['Permit receiver', 'Gas tester', 'Fire watch', 'Confined space entrant', 'Working at height', 'LOTO awareness', 'Lifting supervisor'];
    }
}
