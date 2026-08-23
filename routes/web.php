<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Qms\ActionController;
use App\Http\Controllers\Qms\AdminController;
use App\Http\Controllers\Qms\AiController;
use App\Http\Controllers\Qms\AuditController;
use App\Http\Controllers\Qms\ComplianceController;
use App\Http\Controllers\Qms\DashboardController;
use App\Http\Controllers\Qms\DocumentController;
use App\Http\Controllers\Qms\ExportController;
use App\Http\Controllers\Qms\CapaController;
use App\Http\Controllers\Qms\FeedbackController;
use App\Http\Controllers\Qms\InspectionController;
use App\Http\Controllers\Qms\IncidentController;
use App\Http\Controllers\Qms\InvestigationController;
use App\Http\Controllers\Qms\IntelligenceController;
use App\Http\Controllers\Qms\NotificationController;
use App\Http\Controllers\Qms\MyWorkController;
use App\Http\Controllers\Qms\OccurrenceController;
use App\Http\Controllers\Qms\ObservationController;
use App\Http\Controllers\Qms\ObjectiveController;
use App\Http\Controllers\Qms\ManagementReviewController;
use App\Http\Controllers\Qms\PermitController;
use App\Http\Controllers\Qms\PlatformController;
use App\Http\Controllers\Qms\PublicReportController;
use App\Http\Controllers\Qms\NonconformanceController;
use App\Http\Controllers\Qms\ReporterController;
use App\Http\Controllers\Qms\ReportingController;
use App\Http\Controllers\Qms\RiskController;
use App\Http\Controllers\Qms\SafetyPromotionController;
use App\Http\Controllers\Qms\SearchController;
use App\Http\Controllers\Qms\SupplierController;
use App\Http\Controllers\Qms\TrainingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/portal/report', [PublicReportController::class, 'create'])->name('portal.report');
Route::post('/portal/report', [PublicReportController::class, 'store'])->name('portal.report.store');

Route::get('/reporter', [ReporterController::class, 'home'])->name('reporter.home');
Route::get('/reporter/report/{reportType}', [ReporterController::class, 'create'])->name('reporter.create');
Route::post('/reporter/report/{reportType}', [ReporterController::class, 'store'])->name('reporter.store');
Route::get('/reporter/receipts/{token}', [ReporterController::class, 'receipt'])->name('reporter.receipt');
Route::get('/api/reporter/report-types', [ReporterController::class, 'apiReportTypes'])->name('reporter.api.report-types');
Route::post('/api/reporter/offline/validate', [ReporterController::class, 'apiValidateDraft'])->name('reporter.api.offline.validate');
Route::post('/api/reporter/reports', [ReporterController::class, 'apiStore'])->name('reporter.api.store');

Route::middleware('auth')->group(function () {
    Route::get('/reporter/my-reports', [ReporterController::class, 'myReports'])->name('reporter.my-reports');
    Route::get('/reporter/notifications', [ReporterController::class, 'notifications'])->name('reporter.notifications');
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::middleware(['auth', 'internal.qms'])->group(function () {
    Route::get('/', DashboardController::class)->name('qms.dashboard');
    Route::get('/dashboard', DashboardController::class)->name('qms.dashboard.path');
    Route::get('/qms', DashboardController::class)->name('qms.index');
    Route::get('my-work', [MyWorkController::class, 'index'])->name('my-work.index');
    Route::get('search', [SearchController::class, 'index'])->name('search.index');
    Route::get('intelligence', [IntelligenceController::class, 'index'])->name('intelligence.index');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('reporting', [ReportingController::class, 'index'])->name('reporting.index');
    Route::get('reporting/{reportType}/create', [ReportingController::class, 'create'])->name('reporting.create');
    Route::get('reports/{report}', [ReportingController::class, 'show'])->name('reporting.show');
    Route::post('reports/{report}/accept', [ReportingController::class, 'accept'])->name('reporting.accept');
    Route::post('reports/{report}/reject', [ReportingController::class, 'reject'])->name('reporting.reject');

    Route::get('observations', [ObservationController::class, 'index'])->name('observations.index');
    Route::get('observations/create', [ObservationController::class, 'create'])->name('observations.create');
    Route::post('observations', [ObservationController::class, 'store'])->name('observations.store');
    Route::get('observations/{observation}', [ObservationController::class, 'show'])->name('observations.show');
    Route::patch('observations/{observation}/review', [ObservationController::class, 'review'])->name('observations.review');
    Route::post('observations/{observation}/actions', [ObservationController::class, 'storeAction'])->name('observations.actions.store');

    Route::get('permits', [PermitController::class, 'index'])->name('permits.index');
    Route::get('permits/create', [PermitController::class, 'create'])->name('permits.create');
    Route::post('permits', [PermitController::class, 'store'])->name('permits.store');
    Route::get('permits/{permit}', [PermitController::class, 'show'])->name('permits.show');
    Route::patch('permits/{permit}/transition', [PermitController::class, 'transition'])->name('permits.transition');

    Route::resource('occurrences', OccurrenceController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('occurrences/{occurrence}/advance', [OccurrenceController::class, 'advance'])->name('occurrences.advance');
    Route::post('occurrences/{occurrence}/notes', [OccurrenceController::class, 'storeNote'])->name('occurrences.notes.store');
    Route::post('occurrences/{occurrence}/recommendations', [OccurrenceController::class, 'storeRecommendation'])->name('occurrences.recommendations.store');

    Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');

    Route::get('actions', [ActionController::class, 'index'])->name('actions.index');
    Route::patch('actions/{action}', [ActionController::class, 'update'])->name('actions.update');

    Route::get('investigations', [InvestigationController::class, 'index'])->name('investigations.index');
    Route::get('investigations/{investigation}', [InvestigationController::class, 'show'])->name('investigations.show');
    Route::patch('investigations/{investigation}', [InvestigationController::class, 'update'])->name('investigations.update');
    Route::post('investigations/{investigation}/notes', [InvestigationController::class, 'storeNote'])->name('investigations.notes.store');

    Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    Route::get('inspections', [InspectionController::class, 'index'])->name('inspections.index');
    Route::get('nonconformances', [NonconformanceController::class, 'index'])->name('nonconformances.index');
    Route::get('capa', [CapaController::class, 'index'])->name('capa.index');
    Route::get('lessons-learned', [SafetyPromotionController::class, 'index'])->name('safety-promotions.index');
    Route::get('risks', [RiskController::class, 'index'])->name('risks.index');
    Route::patch('risks/{risk}', [RiskController::class, 'update'])->name('risks.update');
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::patch('documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::get('compliance', [ComplianceController::class, 'index'])->name('compliance.index');
    Route::get('ai', [AiController::class, 'index'])->name('ai.index');
    Route::post('ai/request-review', [AiController::class, 'requestReview'])->name('ai.request-review');
    Route::get('objectives', [ObjectiveController::class, 'index'])->name('objectives.index');
    Route::get('management-reviews', [ManagementReviewController::class, 'index'])->name('management-reviews.index');
    Route::get('training', [TrainingController::class, 'index'])->name('training.index');
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('public-reports', [PublicReportController::class, 'index'])->name('public-reports.index');
    Route::get('platform', [PlatformController::class, 'index'])->name('platform.index');
    Route::post('platform/forms', [PlatformController::class, 'storeForm'])->name('platform.forms.store');
    Route::post('platform/workflows', [PlatformController::class, 'storeWorkflow'])->name('platform.workflows.store');
    Route::post('platform/report-designs', [PlatformController::class, 'storeReportDesign'])->name('platform.report-designs.store');
    Route::post('platform/email-designs', [PlatformController::class, 'storeEmailDesign'])->name('platform.email-designs.store');
    Route::post('platform/notification-templates', [PlatformController::class, 'storeNotificationTemplate'])->name('platform.notification-templates.store');
    Route::post('platform/notification-designs', [PlatformController::class, 'storeNotificationDesign'])->name('platform.notification-designs.store');
    Route::post('platform/notification-rules', [PlatformController::class, 'storeNotificationRule'])->name('platform.notification-rules.store');
    Route::post('platform/permission-templates', [PlatformController::class, 'storePermissionTemplate'])->name('platform.permission-templates.store');
    Route::post('platform/numbering-rules', [PlatformController::class, 'storeNumberingRule'])->name('platform.numbering-rules.store');
    Route::post('platform/configuration-packages', [PlatformController::class, 'storeConfigurationPackage'])->name('platform.configuration-packages.store');
    Route::post('platform/data-sources', [PlatformController::class, 'storeDataSource'])->name('platform.data-sources.store');
    Route::post('platform/domain-packs', [PlatformController::class, 'storeDomainPack'])->name('platform.domain-packs.store');
    Route::post('platform/saved-views', [PlatformController::class, 'storeSavedView'])->name('platform.saved-views.store');
    Route::get('exports/occurrences', [ExportController::class, 'occurrences'])->name('exports.occurrences');
    Route::get('exports/actions', [ExportController::class, 'actions'])->name('exports.actions');
    Route::get('exports/risks', [ExportController::class, 'risks'])->name('exports.risks');
    Route::get('exports/documents', [ExportController::class, 'documents'])->name('exports.documents');
    Route::get('exports/audit-trail', [ExportController::class, 'auditTrail'])->name('exports.audit-trail');

    Route::get('admin', [AdminController::class, 'index'])->name('admin.index');
});
