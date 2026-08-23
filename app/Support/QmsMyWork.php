<?php

namespace App\Support;

use App\Models\QmsAction;
use App\Models\QmsAudit;
use App\Models\QmsDocument;
use App\Models\QmsIncident;
use App\Models\QmsInvestigation;
use App\Models\QmsPermit;
use App\Models\QmsPublicReport;
use App\Models\QmsReport;
use App\Models\QmsSupplier;
use App\Models\QmsTrainingRecord;
use Illuminate\Support\Collection;

class QmsMyWork
{
    public static function items(): Collection
    {
        return collect()
            ->merge(self::reports())
            ->merge(self::incidents())
            ->merge(self::permits())
            ->merge(self::actions())
            ->merge(self::investigations())
            ->merge(self::audits())
            ->merge(self::documents())
            ->merge(self::training())
            ->merge(self::suppliers())
            ->merge(self::publicReports())
            ->sortBy([
                ['sort_bucket', 'asc'],
                ['due_at', 'asc'],
                ['created_at', 'desc'],
            ])
            ->values();
    }

    public static function counts(Collection $items): array
    {
        return [
            'total' => $items->count(),
            'overdue' => $items->where('is_overdue', true)->count(),
            'due_soon' => $items->where('is_due_soon', true)->count(),
            'critical' => $items->whereIn('priority', ['Critical', 'High'])->count(),
            'modules' => $items->pluck('module')->unique()->count(),
        ];
    }

    private static function reports(): Collection
    {
        return QmsReport::query()
            ->whereIn('status', ['Submitted', 'Returned for Information'])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (QmsReport $report) => self::item(
                module: 'Reporting',
                reference: $report->reference,
                title: $report->title,
                status: $report->status,
                priority: $report->severity,
                owner: 'Screening Team',
                dueAt: $report->submitted_at?->copy()->addDays(2),
                url: route('reporting.show', $report),
                source: 'Screening queue',
                createdAt: $report->created_at,
            ));
    }

    private static function incidents(): Collection
    {
        return QmsIncident::query()
            ->whereNotIn('status', ['Closed', 'Rejected'])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (QmsIncident $incident) => self::item(
                module: 'Incident',
                reference: $incident->reference,
                title: $incident->title,
                status: $incident->workflow_stage,
                priority: $incident->severity,
                owner: $incident->owner ?: 'Unassigned',
                dueAt: $incident->accepted_at?->copy()->addDays($incident->investigation_required ? 7 : 14),
                url: route('incidents.show', $incident),
                source: $incident->source_report_reference,
                createdAt: $incident->created_at,
            ));
    }

    private static function permits(): Collection
    {
        return QmsPermit::query()
            ->whereNotIn('status', ['Closed', 'Cancelled'])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (QmsPermit $permit) => self::item(
                module: 'Permit',
                reference: $permit->reference,
                title: $permit->title,
                status: $permit->status,
                priority: $permit->risk_rating,
                owner: $permit->current_approver ?: ($permit->owner ?: 'Unassigned'),
                dueAt: $permit->planned_end_at ?: $permit->planned_start_at,
                url: route('permits.show', $permit),
                source: $permit->permit_type,
                createdAt: $permit->created_at,
            ));
    }

    private static function actions(): Collection
    {
        return QmsAction::query()
            ->whereNotIn('status', ['Closed', 'Verified'])
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (QmsAction $action) => self::item(
                module: 'Action',
                reference: $action->reference,
                title: $action->title,
                status: $action->status,
                priority: $action->priority,
                owner: $action->owner,
                dueAt: $action->due_date,
                url: route('actions.index', ['search' => $action->reference]),
                source: $action->source_reference ?: 'Standalone',
                createdAt: $action->created_at,
            ));
    }

    private static function investigations(): Collection
    {
        return QmsInvestigation::query()
            ->whereNotIn('status', ['Closed', 'Approved'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (QmsInvestigation $investigation) => self::item(
                module: 'Investigation',
                reference: $investigation->reference,
                title: $investigation->title,
                status: $investigation->status,
                priority: 'High',
                owner: $investigation->lead_investigator,
                dueAt: $investigation->created_at?->copy()->addDays(14),
                url: route('investigations.show', $investigation),
                source: $investigation->source_reference ?: 'Investigation',
                createdAt: $investigation->created_at,
            ));
    }

    private static function audits(): Collection
    {
        return QmsAudit::query()
            ->whereNotIn('status', ['Closed', 'Complete'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (QmsAudit $audit) => self::item(
                module: 'Audit',
                reference: $audit->reference,
                title: $audit->title,
                status: $audit->status,
                priority: 'Medium',
                owner: $audit->lead_auditor,
                dueAt: $audit->scheduled_date,
                url: route('audits.index', ['search' => $audit->reference]),
                source: $audit->standard ?: 'Audit program',
                createdAt: $audit->created_at,
            ));
    }

    private static function documents(): Collection
    {
        return QmsDocument::query()
            ->whereNotIn('status', ['Published', 'Archived'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (QmsDocument $document) => self::item(
                module: 'Document',
                reference: $document->reference,
                title: $document->title,
                status: $document->status,
                priority: 'Medium',
                owner: $document->owner,
                dueAt: $document->review_date,
                url: route('documents.index', ['search' => $document->reference]),
                source: 'Revision ' . $document->version,
                createdAt: $document->created_at,
            ));
    }

    private static function training(): Collection
    {
        return QmsTrainingRecord::query()
            ->where('expires_on', '<=', now()->addDays(45)->toDateString())
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (QmsTrainingRecord $record) => self::item(
                module: 'Training',
                reference: $record->reference,
                title: $record->course,
                status: $record->status,
                priority: $record->expires_on?->isPast() ? 'High' : 'Medium',
                owner: $record->person_name,
                dueAt: $record->expires_on,
                url: route('training.index', ['search' => $record->reference]),
                source: $record->competency_area,
                createdAt: $record->created_at,
            ));
    }

    private static function suppliers(): Collection
    {
        return QmsSupplier::query()
            ->where(function ($query) {
                $query->whereIn('risk_rating', ['High', 'Critical'])
                    ->orWhere('next_review_date', '<=', now()->addDays(45)->toDateString());
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (QmsSupplier $supplier) => self::item(
                module: 'Supplier',
                reference: $supplier->reference,
                title: $supplier->name,
                status: $supplier->status,
                priority: $supplier->risk_rating,
                owner: $supplier->owner,
                dueAt: $supplier->next_review_date,
                url: route('suppliers.index', ['search' => $supplier->reference]),
                source: $supplier->category,
                createdAt: $supplier->created_at,
            ));
    }

    private static function publicReports(): Collection
    {
        return QmsPublicReport::query()
            ->whereNotIn('status', ['Closed', 'Rejected'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (QmsPublicReport $report) => self::item(
                module: 'Public Intake',
                reference: $report->reference,
                title: $report->category,
                status: $report->status,
                priority: $report->confidential ? 'High' : 'Medium',
                owner: 'Intake Reviewer',
                dueAt: $report->created_at?->copy()->addDays(2),
                url: route('public-reports.index', ['search' => $report->reference]),
                source: $report->confidential ? 'Confidential' : 'External',
                createdAt: $report->created_at,
            ));
    }

    private static function item(string $module, string $reference, string $title, string $status, string $priority, string $owner, mixed $dueAt, string $url, string $source, mixed $createdAt): array
    {
        $due = $dueAt ? now()->parse($dueAt) : null;

        return [
            'module' => $module,
            'reference' => $reference,
            'title' => $title,
            'status' => $status,
            'priority' => $priority,
            'owner' => $owner,
            'source' => $source,
            'due_at' => $due,
            'created_at' => $createdAt,
            'url' => $url,
            'is_overdue' => $due ? $due->isPast() : false,
            'is_due_soon' => $due ? $due->between(now(), now()->addDays(7)) : false,
            'sort_bucket' => $due && $due->isPast() ? 0 : ($due && $due->lte(now()->addDays(7)) ? 1 : 2),
        ];
    }
}
