<?php

namespace App\Http\Controllers\Qms;

use App\Http\Controllers\Controller;
use App\Models\QmsAction;
use App\Models\QmsCapaCase;
use App\Models\QmsFinding;
use App\Models\QmsIncident;
use App\Models\QmsNonconformance;
use App\Models\QmsOccurrence;
use App\Models\QmsPermit;
use App\Models\QmsPublicReport;
use App\Models\QmsReport;
use App\Models\QmsReportDesign;
use App\Models\QmsRisk;
use App\Models\QmsSupplier;
use App\Models\QmsTrainingRecord;
use App\Models\QmsWorkflowDefinition;
use App\Models\QmsNotificationDesign;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $occurrenceWorkflow = QmsWorkflowDefinition::where('code', 'WF-OCC-001')->first();
        $workflowStages = $occurrenceWorkflow?->stages ?: ['Draft', 'Submitted', 'HSE Review', 'Investigation', 'CAPA', 'Verification', 'Closed'];
        $openReports = QmsReport::whereIn('status', ['Submitted', 'Returned for Information'])->count();
        $openIncidents = QmsIncident::whereNotIn('status', ['Closed', 'Rejected'])->count();
        $activePermits = QmsPermit::whereIn('status', ['Approved', 'Active', 'Suspended'])->count();
        $overdueActions = QmsAction::where('due_date', '<', now()->toDateString())->whereNotIn('status', ['Closed', 'Verified'])->count();

        return view('qms.dashboard', [
            'metrics' => [
                'openReports' => $openReports,
                'openIncidents' => $openIncidents,
                'activePermits' => $activePermits,
                'overdueActions' => $overdueActions,
                'openOccurrences' => QmsOccurrence::whereNotIn('status', ['Closed', 'Rejected'])->count(),
                'highRisks' => QmsRisk::whereIn('rating', ['High', 'Critical'])->count(),
                'publicReports' => QmsPublicReport::whereNotIn('status', ['Closed', 'Rejected'])->count(),
                'trainingDue' => QmsTrainingRecord::where('expires_on', '<=', now()->addDays(45)->toDateString())->count(),
                'supplierWatch' => QmsSupplier::whereIn('risk_rating', ['High', 'Critical'])->count(),
                'openFindings' => QmsFinding::whereNotIn('status', ['Closed', 'Verified'])->count(),
                'openNcr' => QmsNonconformance::whereNotIn('status', ['Closed', 'Verified'])->count(),
                'openCapa' => QmsCapaCase::whereNotIn('status', ['Closed', 'Effective'])->count(),
                'reportDesigns' => QmsReportDesign::where('status', 'Published')->count(),
                'notificationDesigns' => QmsNotificationDesign::where('status', 'Published')->count(),
            ],
            'workflowStages' => $workflowStages,
            'occurrences' => QmsOccurrence::whereNotIn('status', ['Closed', 'Rejected'])->latest()->limit(8)->get(),
            'priorityReports' => QmsReport::whereIn('status', ['Submitted', 'Returned for Information'])->latest()->limit(4)->get(),
            'priorityPermits' => QmsPermit::whereIn('status', ['Pending Review', 'Approved', 'Active', 'Suspended'])->latest()->limit(4)->get(),
            'priorityActions' => QmsAction::whereNotIn('status', ['Closed', 'Verified'])->latest()->limit(4)->get(),
            'workload' => [
                'Public intake' => QmsPublicReport::whereNotIn('status', ['Closed', 'Rejected'])->count(),
                'Training due' => QmsTrainingRecord::where('expires_on', '<=', now()->addDays(45)->toDateString())->count(),
                'Supplier watch' => QmsSupplier::whereIn('risk_rating', ['High', 'Critical'])->count(),
                'Open findings' => QmsFinding::whereNotIn('status', ['Closed', 'Verified'])->count(),
            ],
        ]);
    }
}
