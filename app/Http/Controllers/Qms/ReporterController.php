<?php

namespace App\Http\Controllers\Qms;

use App\Http\Controllers\Controller;
use App\Models\QmsPublicReport;
use App\Support\QmsNumbering;
use App\Support\ReporterAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReporterController extends Controller
{
    public function __construct(private readonly ReporterAccess $access)
    {
    }

    public function home(Request $request)
    {
        return view('reporter.home', [
            'reportTypes' => $this->access->allowedReportTypes($request->user()),
            'query' => $request->string('search')->toString(),
        ]);
    }

    public function create(Request $request, string $reportType)
    {
        $rule = $this->access->findAllowed($reportType, $request->user());
        abort_unless($rule, 404);

        return view('reporter.create', [
            'reportType' => $rule,
        ]);
    }

    public function store(Request $request, string $reportType)
    {
        $rule = $this->access->findAllowed($reportType, $request->user());
        abort_unless($rule, 404);

        $data = $this->validateSubmission($request);
        $formVersion = (int) ($data['form_version'] ?? $rule->form_version);

        abort_if($formVersion !== (int) $rule->form_version, Response::HTTP_CONFLICT, 'This saved draft uses an older form version. Refresh the form before submitting.');

        $report = $this->createReporterReport($request, $rule, $data, 'web');

        return redirect()->route('reporter.receipt', $report->receipt_token);
    }

    public function receipt(string $token)
    {
        $report = QmsPublicReport::query()
            ->where('receipt_token', $token)
            ->firstOrFail();

        return view('reporter.receipt', [
            'report' => $report,
        ]);
    }

    public function myReports(Request $request)
    {
        $reports = QmsPublicReport::query()
            ->where('reporter_user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('reporter.my-reports', [
            'reports' => $reports,
        ]);
    }

    public function notifications(Request $request)
    {
        $reports = QmsPublicReport::query()
            ->where('reporter_user_id', $request->user()->id)
            ->whereNotNull('reporter_visible_messages')
            ->latest()
            ->paginate(12);

        return view('reporter.notifications', [
            'reports' => $reports,
        ]);
    }

    public function apiReportTypes(Request $request)
    {
        return response()->json([
            'data' => $this->access->allowedReportTypes($request->user())
                ->map(fn ($rule) => $this->access->apiPayload($rule))
                ->values(),
        ]);
    }

    public function apiValidateDraft(Request $request)
    {
        $data = $request->validate([
            'report_type_key' => ['required', 'string', 'max:120'],
            'form_version' => ['required', 'integer', 'min:1'],
        ]);

        $rule = $this->access->findAllowed($data['report_type_key'], $request->user());
        abort_unless($rule, 404);

        return response()->json([
            'compatible' => (int) $data['form_version'] === (int) $rule->form_version,
            'current_form_version' => $rule->form_version,
            'report_type_key' => $rule->report_type_key,
        ]);
    }

    public function apiStore(Request $request)
    {
        $data = $this->validateSubmission($request, true);
        $rule = $this->access->findAllowed($data['report_type_key'], $request->user());
        abort_unless($rule, 404);

        if ((int) $data['form_version'] !== (int) $rule->form_version) {
            return response()->json([
                'message' => 'This offline draft uses an older form version. Refresh the form before submitting.',
                'current_form_version' => $rule->form_version,
            ], Response::HTTP_CONFLICT);
        }

        $report = $this->createReporterReport($request, $rule, $data, 'mobile-api');

        return response()->json([
            'reference' => $report->reference,
            'receipt_url' => route('reporter.receipt', $report->receipt_token),
            'public_status' => $report->public_status,
        ], Response::HTTP_CREATED);
    }

    private function validateSubmission(Request $request, bool $api = false): array
    {
        return $request->validate([
            'report_type_key' => [$api ? 'required' : 'nullable', 'string', 'max:120'],
            'form_version' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:180'],
            'reporter_name' => ['nullable', 'string', 'max:160'],
            'reporter_contact' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:160'],
            'anonymous' => ['nullable', 'boolean'],
            'confidential' => ['nullable', 'boolean'],
            'description' => ['required', 'string', 'max:5000'],
            'observation_type' => ['nullable', 'in:Unsafe Act,Unsafe Condition'],
            'area' => ['nullable', 'string', 'max:160'],
            'unit' => ['nullable', 'string', 'max:120'],
            'potential_consequence' => ['nullable', 'string', 'max:3000'],
            'action_taken' => ['nullable', 'array'],
            'action_taken.*' => ['string', 'max:160'],
            'immediate_corrective_action' => ['nullable', 'string', 'max:3000'],
            'client_context' => ['nullable', 'array'],
        ]);
    }

    private function createReporterReport(Request $request, $rule, array $data, string $channel): QmsPublicReport
    {
        return DB::transaction(function () use ($request, $rule, $data, $channel) {
            $anonymous = (bool) ($data['anonymous'] ?? false);
            $confidential = (bool) (($data['confidential'] ?? false) || $rule->report_type_key === 'confidential-safety');

            return QmsPublicReport::create([
                'reference' => QmsNumbering::next('NUM-PUB', 'Reporter Intake', 'PUB'),
                'report_type_key' => $rule->report_type_key,
                'reporter_user_id' => $request->user()?->id,
                'reporter_name' => $anonymous ? null : ($data['reporter_name'] ?? $request->user()?->name),
                'reporter_contact' => $anonymous ? null : ($data['reporter_contact'] ?? $request->user()?->email),
                'category' => $rule->type,
                'location' => $data['location'] ?? null,
                'anonymous' => $anonymous,
                'confidential' => $confidential,
                'status' => 'New',
                'receipt_token' => Str::random(48),
                'public_status' => 'Submitted',
                'form_version' => (int) ($data['form_version'] ?? $rule->form_version),
                'description' => $data['description'],
                'submitted_payload' => [
                    'title' => $data['title'] ?? $rule->title,
                    'report_type_title' => $rule->title,
                    'priority' => $rule->priority,
                    'module' => $rule->module,
                    'observation_type' => $data['observation_type'] ?? null,
                    'area' => $data['area'] ?? null,
                    'unit' => $data['unit'] ?? null,
                    'potential_consequence' => $data['potential_consequence'] ?? null,
                    'action_taken' => $data['action_taken'] ?? [],
                    'immediate_corrective_action' => $data['immediate_corrective_action'] ?? null,
                ],
                'client_context' => array_merge($data['client_context'] ?? [], [
                    'channel' => $channel,
                    'submitted_from' => 'reporter-product',
                ]),
                'reporter_visible_messages' => [
                    [
                        'visibility' => 'REPORTER_VISIBLE',
                        'message' => 'Your report was received by the QMS team.',
                        'created_at' => now()->toIso8601String(),
                    ],
                ],
            ]);
        });
    }
}
