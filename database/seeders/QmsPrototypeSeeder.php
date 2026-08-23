<?php

namespace Database\Seeders;

use App\Models\QmsAction;
use App\Models\QmsAccessScope;
use App\Models\QmsAttachment;
use App\Models\QmsAiProvider;
use App\Models\QmsAudit;
use App\Models\QmsCapaCase;
use App\Models\QmsComplianceChange;
use App\Models\QmsComplianceFramework;
use App\Models\QmsConfigurationPackage;
use App\Models\QmsDataSource;
use App\Models\QmsDocument;
use App\Models\QmsDomainPack;
use App\Models\QmsEmailDesign;
use App\Models\QmsElectronicSignature;
use App\Models\QmsFeedbackItem;
use App\Models\QmsFormDefinition;
use App\Models\QmsFinding;
use App\Models\QmsIncident;
use App\Models\QmsInspection;
use App\Models\QmsInvestigation;
use App\Models\QmsIntegrationEvent;
use App\Models\QmsKeyUserAssignment;
use App\Models\QmsManagementReview;
use App\Models\QmsModuleLicense;
use App\Models\QmsNotification;
use App\Models\QmsNotificationDesign;
use App\Models\QmsNotificationGroup;
use App\Models\QmsNotificationRule;
use App\Models\QmsNotificationTemplate;
use App\Models\QmsNonconformance;
use App\Models\QmsNumberingRule;
use App\Models\QmsObjective;
use App\Models\QmsOfflineProfile;
use App\Models\QmsOccurrence;
use App\Models\QmsPermissionTemplate;
use App\Models\QmsRecordLink;
use App\Models\QmsRecordSimilarity;
use App\Models\QmsRecommendation;
use App\Models\QmsReport;
use App\Models\QmsRetentionRule;
use App\Models\QmsReportDesign;
use App\Models\QmsRisk;
use App\Models\QmsSafetyPromotion;
use App\Models\QmsSavedView;
use App\Models\QmsStandard;
use App\Models\QmsStandardRequirement;
use App\Models\QmsSupplier;
use App\Models\QmsSyncAdapter;
use App\Models\QmsSystemMonitor;
use App\Models\QmsSystemSetting;
use App\Models\QmsTaxonomyTerm;
use App\Models\QmsTrainingRecord;
use App\Models\QmsWorkflowDefinition;
use App\Models\User;
use Illuminate\Database\Seeder;

class QmsPrototypeSeeder extends Seeder
{
    public function run(): void
    {
        QmsOccurrence::updateOrCreate(['reference' => 'QMS-2026-00435'], [
            'record_family' => 'Occurrence',
            'report_key' => 'ground-occurrence',
            'title' => 'Unsafe condition near scaffolding',
            'event_title' => 'Unsafe condition near scaffolding',
            'type' => 'Ground safety',
            'location' => 'OQB Locations',
            'area_fleet' => 'Engineering / Ground Operations',
            'exact_location' => 'CAE 135 equipment area',
            'reported_by' => 'Mazin Al Farsi',
            'description' => 'A rusted pipe was observed and there was no signage displayed in an area where scaffolding erection was in progress.',
            'status' => 'Submitted',
            'workflow_stage' => 'HSE Review',
            'risk_rating' => 'High',
            'confidential' => false,
            'mor' => false,
            'event_categories' => ['Compliance / Regulatory', 'Human Factors'],
            'aircraft_type' => null,
            'aircraft_registration' => null,
            'flight_number' => null,
            'flight_cancelled' => false,
            'personnel_involved' => ['staff_1' => 'Contractor employee'],
            'flight_plan_details' => null,
            'action_taken' => ['Informed supervisor'],
            'immediate_corrective_action' => 'Advised crew to display signage and barricade the area.',
            'feedback_to_reporter' => 'HSE review initiated.',
            'event_date' => now()->toDateString(),
            'reported_at' => now(),
        ]);

        QmsOccurrence::updateOrCreate(['reference' => 'OBS-2026-000245'], [
            'record_family' => 'Observation',
            'report_key' => 'observation',
            'title' => 'Missing barricade at temporary work area',
            'event_title' => 'Missing barricade at temporary work area',
            'type' => 'Observation',
            'observation_type' => 'Unsafe Condition',
            'area' => 'Ground Operations',
            'unit' => 'MCT',
            'department_name' => 'HSE',
            'location' => 'Ramp',
            'area_fleet' => 'Ground Operations',
            'exact_location' => 'Gate 4 maintenance access',
            'reported_by' => 'Yahya Al Naaimi',
            'observer' => 'Yahya Al Naaimi',
            'description' => 'Temporary barricade was missing from an active work area and people could enter without warning.',
            'potential_consequence' => 'Personnel could enter the work area and be exposed to moving equipment or maintenance activity.',
            'status' => 'Submitted',
            'workflow_stage' => 'HSE Review',
            'risk_rating' => 'Medium',
            'review_decision' => null,
            'reviewer_comments' => null,
            'reviewer_name' => null,
            'reviewed_at' => null,
            'reporter_visible_message' => null,
            'action_required' => false,
            'confidential' => false,
            'mor' => false,
            'event_categories' => ['Observation', 'Unsafe Condition'],
            'aircraft_type' => null,
            'aircraft_registration' => null,
            'flight_number' => null,
            'flight_cancelled' => false,
            'personnel_involved' => [],
            'flight_plan_details' => null,
            'action_taken' => ['Supervisor informed'],
            'immediate_corrective_action' => 'Area supervisor was informed and asked to restore the barricade.',
            'temporary_control' => 'Temporary warning cone placed until proper barricade is restored.',
            'feedback_to_reporter' => null,
            'event_date' => now()->toDateString(),
            'observed_on' => now()->toDateString(),
            'observed_at' => now()->format('H:i'),
            'reported_at' => now(),
        ]);

        $seedReport = QmsReport::updateOrCreate(['reference' => 'REP-2026-000421'], [
            'report_key' => 'ground-occurrence',
            'title' => 'Unsafe condition near scaffolding',
            'type' => 'Ground safety',
            'category' => 'Ground safety',
            'classification' => 'Voluntary',
            'severity' => 'High',
            'status' => 'Accepted',
            'workflow_stage' => 'Accepted',
            'location' => 'OQB Locations',
            'department' => 'Engineering / Ground Operations',
            'reported_by' => 'Mazin Al Farsi',
            'reporter_user_id' => User::where('email', 'mazin.alfarsi@qms.test')->value('id'),
            'anonymous' => false,
            'confidential' => false,
            'mandatory' => false,
            'description' => 'A rusted pipe was observed and there was no signage displayed in an area where scaffolding erection was in progress.',
            'payload' => ['source' => 'Seeded v3 reporting desk', 'legacy_occurrence_reference' => 'QMS-2026-00435'],
            'reported_at' => now()->subDay(),
            'submitted_at' => now()->subDay(),
            'screened_at' => now(),
            'screening_notes' => 'Accepted during baseline seeding to demonstrate report-to-incident separation.',
        ]);

        QmsIncident::updateOrCreate(['source_report_id' => $seedReport->id], [
            'reference' => 'INC-2026-000183',
            'source_report_reference' => $seedReport->reference,
            'title' => $seedReport->title,
            'type' => $seedReport->type,
            'classification' => 'Safety Event',
            'severity' => 'High',
            'status' => 'Open',
            'workflow_stage' => 'Classification',
            'owner' => 'Safety Manager',
            'department' => $seedReport->department,
            'location' => $seedReport->location,
            'investigation_required' => true,
            'closure_blocked' => true,
            'source_snapshot' => [
                'report_reference' => $seedReport->reference,
                'reported_by' => $seedReport->reported_by,
                'description' => $seedReport->description,
                'confidential' => $seedReport->confidential,
                'payload' => $seedReport->payload,
            ],
            'accepted_at' => now(),
            'accepted_by' => User::where('email', 'admin@qms.test')->value('id'),
        ]);

        QmsAction::updateOrCreate(['reference' => 'CAPA-2026-00077'], [
            'source_reference' => 'QMS-2026-00435',
            'title' => 'Revise barricade control checklist',
            'description' => 'Review barricade checklist and update contractor controls for scaffolding zones.',
            'required_outcome' => 'Updated checklist issued and briefed to affected teams.',
            'owner' => 'Engineering',
            'responsible_department' => 'Engineering',
            'priority' => 'High',
            'risk_relevance' => 'High',
            'evidence_required' => true,
            'status' => 'Open',
            'progress' => 20,
            'due_date' => now()->addDays(3)->toDateString(),
            'assigned_at' => now()->subDay(),
            'notified_at' => now()->subDay(),
        ]);

        QmsAction::updateOrCreate(['reference' => 'ACT-2026-00118'], [
            'source_reference' => 'QMS-2026-00435',
            'title' => 'Brief contractors on signage requirements',
            'description' => 'Conduct toolbox briefing for signage and barricade requirements.',
            'required_outcome' => 'Attendance evidence and briefing material attached.',
            'owner' => 'HSE',
            'responsible_department' => 'HSE',
            'priority' => 'Medium',
            'risk_relevance' => 'Medium',
            'evidence_required' => true,
            'status' => 'In progress',
            'progress' => 50,
            'due_date' => now()->addDays(5)->toDateString(),
            'assigned_at' => now()->subDay(),
            'notified_at' => now()->subDay(),
            'accepted_at' => now()->subHours(18),
        ]);

        QmsInvestigation::updateOrCreate(['reference' => 'INV-2026-00012'], [
            'source_reference' => 'QMS-2026-00435',
            'title' => 'Scaffolding signage and barricade control review',
            'lead_investigator' => 'HSE Reviewer',
            'status' => 'Open',
            'scope' => 'Review worksite controls, contractor briefing, and supervisory verification.',
            'findings' => 'Preliminary finding: area control verification was not documented before work started.',
        ]);

        QmsAudit::updateOrCreate(['reference' => 'AUD-2026-00008'], [
            'title' => 'August internal QMS/SMS assurance audit',
            'standard' => 'ISO 9001:2015 / ICAO SMS',
            'lead_auditor' => 'Quality Admin',
            'status' => 'Planned',
            'scheduled_date' => now()->addDays(14)->toDateString(),
        ]);

        $inspection = QmsInspection::updateOrCreate(['reference' => 'INSP-2026-00014'], [
            'title' => 'Ramp FOD and GSE control inspection',
            'inspection_type' => 'Ramp inspection',
            'station' => 'MCT',
            'inspector' => 'HSE Reviewer',
            'status' => 'In progress',
            'passed_count' => 12,
            'failed_count' => 1,
            'not_applicable_count' => 2,
            'checklist_snapshot' => [
                'version' => 1,
                'items' => ['FOD controls', 'GSE parking', 'Chocks', 'PPE', 'Housekeeping'],
            ],
            'evidence_summary' => ['photos' => 1, 'actions_created' => 1],
            'scheduled_date' => now()->toDateString(),
        ]);

        QmsFinding::updateOrCreate(['reference' => 'FND-2026-00031'], [
            'source_type' => QmsInspection::class,
            'source_id' => $inspection->id,
            'source_reference' => $inspection->reference,
            'finding_type' => 'Observation',
            'classification' => 'Ground operations',
            'criterion' => 'Internal ramp housekeeping checklist',
            'objective_evidence' => 'One unsecured item observed in the marked GSE staging area during inspection.',
            'finding_statement' => 'Ramp housekeeping verification was incomplete in one inspected staging area.',
            'owner' => 'Ground Operations',
            'status' => 'Open',
        ]);

        QmsNonconformance::updateOrCreate(['reference' => 'NCR-2026-00019'], [
            'requirement_reference' => 'ORG-QMS-AUDIT-01',
            'objective_evidence' => 'Audit sample found one temporary-work checklist without supervisor verification evidence.',
            'nonconformity_statement' => 'The temporary-work control process was not fully implemented for the sampled work package.',
            'source' => 'Internal audit',
            'classification' => 'Process nonconformance',
            'severity' => 'Medium',
            'containment' => 'Supervisor review completed for the affected work package.',
            'correction' => 'Checklist evidence was completed and stored against the work record.',
            'owner' => 'Engineering',
            'due_date' => now()->addDays(21)->toDateString(),
            'root_cause_required' => true,
            'corrective_action_required' => true,
            'effectiveness_required' => true,
            'closure_authority' => 'Quality Manager',
            'status' => 'Root cause',
        ]);

        QmsCapaCase::updateOrCreate(['reference' => 'CAPA-CASE-2026-00007'], [
            'source_reference' => 'NCR-2026-00019',
            'problem_statement' => 'Temporary work verification evidence is not consistently captured before work starts.',
            'containment' => 'Affected work packages reviewed and supervisors briefed.',
            'root_cause_tools' => ['5 Whys', 'Barrier analysis'],
            'root_cause_statement' => 'Pre-task verification responsibility was understood but not reinforced by the checklist workflow.',
            'corrective_action_plan' => 'Update checklist, add supervisor prompt, and review three subsequent work packages.',
            'phase' => 'Implementation',
            'owner' => 'Quality',
            'due_date' => now()->addDays(30)->toDateString(),
            'effectiveness_criteria' => 'Three consecutive sampled work packages include verification evidence before release.',
            'effectiveness_due_date' => now()->addDays(60)->toDateString(),
            'status' => 'Open',
        ]);

        QmsRisk::updateOrCreate(['reference' => 'RSK-2026-00031'], [
            'hazard' => 'Contractor work area not clearly segregated',
            'owner' => 'Engineering',
            'rating' => 'High',
            'controls' => 'Barricade, signage, toolbox talk, supervisor verification.',
            'review_date' => now()->addMonth()->toDateString(),
        ]);

        QmsDocument::updateOrCreate(['reference' => 'DOC-HSE-001'], [
            'title' => 'Contractor HSE Manual',
            'version' => 'v2.0',
            'owner' => 'HSE',
            'status' => 'Review',
            'review_date' => now()->addMonths(2)->toDateString(),
        ]);

        QmsObjective::updateOrCreate(['reference' => 'OBJ-2026-00001'], [
            'title' => 'Reduce overdue CAPA actions',
            'owner' => 'Quality',
            'measure' => 'Overdue actions at month end',
            'target' => '<= 2 overdue actions',
            'current_value' => '1 overdue action',
            'period' => 'Monthly',
            'status' => 'On track',
            'review_date' => now()->addMonth()->toDateString(),
        ]);

        QmsObjective::updateOrCreate(['reference' => 'SPI-2026-00002'], [
            'title' => 'Improve voluntary safety reporting',
            'owner' => 'Safety',
            'measure' => 'Voluntary reports per quarter',
            'target' => '>= 12 reports',
            'current_value' => '8 reports',
            'period' => 'Quarterly',
            'status' => 'Watch',
            'review_date' => now()->addWeeks(3)->toDateString(),
        ]);

        QmsManagementReview::updateOrCreate(['reference' => 'MR-2026-00001'], [
            'title' => 'Q3 QMS/SMS Management Review',
            'chair' => 'QMS Administrator',
            'meeting_date' => now()->addWeeks(4)->toDateString(),
            'status' => 'Planned',
            'inputs' => ['Audit results', 'CAPA performance', 'Risk register', 'Training status', 'Supplier performance'],
            'decisions' => 'Agenda prepared for leadership review.',
            'actions_summary' => 'Actions will be assigned after review.',
        ]);

        QmsTrainingRecord::updateOrCreate(['reference' => 'TRN-2026-00044'], [
            'person_name' => 'Omar Al Harthy',
            'course' => 'Contractor HSE Induction',
            'competency_area' => 'HSE awareness',
            'completed_on' => now()->subMonth()->toDateString(),
            'expires_on' => now()->addMonths(11)->toDateString(),
            'status' => 'Current',
        ]);

        QmsTrainingRecord::updateOrCreate(['reference' => 'TRN-2026-00045'], [
            'person_name' => 'Mazin Al Farsi',
            'course' => 'Internal Auditor Refresher',
            'competency_area' => 'Audit',
            'completed_on' => now()->subMonths(10)->toDateString(),
            'expires_on' => now()->addMonth()->toDateString(),
            'status' => 'Expiring soon',
        ]);

        QmsSupplier::updateOrCreate(['reference' => 'SUP-2026-00012'], [
            'name' => 'Training Engineering LLC',
            'category' => 'Contractor',
            'owner' => 'Engineering',
            'risk_rating' => 'High',
            'status' => 'Approved with controls',
            'next_review_date' => now()->addMonth()->toDateString(),
            'notes' => 'Supplier linked to contractor HSE and work-area control monitoring.',
        ]);

        QmsComplianceFramework::updateOrCreate(['code' => 'SMS-ICAO'], [
            'name' => 'ICAO Safety Management System',
            'owner' => 'Safety',
            'status' => 'Active',
            'requirements' => [
                'Safety policy and objectives',
                'Safety risk management',
                'Safety assurance',
                'Safety promotion',
            ],
        ]);

        QmsComplianceFramework::updateOrCreate(['code' => 'ISO-9001'], [
            'name' => 'ISO 9001 Quality Management',
            'owner' => 'Quality',
            'status' => 'Active',
            'requirements' => [
                'Context and interested parties',
                'Leadership and accountability',
                'Operational control',
                'Performance evaluation',
                'Improvement and corrective action',
            ],
        ]);

        $standards = [
            ['ICAO-SMS', 'ICAO Safety Management System Concepts', 'ICAO', 'Aviation', 'Annex 19 / SMM mapping', 'Reference model', null, null, 'Safety', 'https://www.icao.int/safety-management/SMI/SMM'],
            ['ISO-9001-2015', 'Quality management systems requirements', 'ISO', 'International', '2015', 'Current', '2015-09-01', null, 'Quality', 'https://www.iso.org/standard/62085.html'],
            ['ISO-9001-2026', 'Quality management systems requirements', 'ISO', 'International', '2026', 'Under publication', null, '2027-09-01', 'Quality', 'https://www.iso.org/standard/88464.html'],
            ['ISO-14001-2026', 'Environmental management systems', 'ISO', 'International', '2026', 'Published', '2026-04-15', null, 'HSE', 'https://www.iso.org/standard/14001'],
            ['ISO-19011-2026', 'Guidelines for auditing management systems', 'ISO', 'International', '2026', 'Published', '2026-05-01', null, 'Assurance', 'https://www.iso.org/standard/19011'],
            ['ISO-45001-2018', 'Occupational health and safety management systems', 'ISO', 'International', '2018', 'Current', '2018-03-01', null, 'HSE', 'https://www.iso.org/standard/63787.html'],
        ];

        foreach ($standards as [$code, $title, $issuer, $jurisdiction, $edition, $status, $effectiveDate, $transitionDeadline, $owner, $sourceUrl]) {
            $standard = QmsStandard::updateOrCreate(['code' => $code], [
                'title' => $title,
                'issuer' => $issuer,
                'jurisdiction' => $jurisdiction,
                'edition' => $edition,
                'publication_status' => $status,
                'effective_date' => $effectiveDate,
                'transition_deadline' => $transitionDeadline,
                'applicability' => 'Configured by organization scope and licensed reference material.',
                'owner' => $owner,
                'source_url' => $sourceUrl,
                'document_reference' => null,
                'change_history' => [
                    ['date' => now()->toDateString(), 'note' => 'Registered as metadata only; no licensed clause text stored.'],
                ],
            ]);

            QmsStandardRequirement::updateOrCreate([
                'qms_standard_id' => $standard->id,
                'requirement_key' => $code . '-MAP-001',
            ], [
                'heading' => 'Internal controlled interpretation required',
                'internal_interpretation' => 'Store organization-owned interpretation, controls and evidence links after authorized review.',
                'controls' => ['Owner assigned', 'Evidence mapped', 'Audit criteria linked'],
                'evidence' => ['Controlled document', 'Audit record', 'Action record'],
                'mapped_documents' => ['DOC-HSE-001'],
                'mapped_forms' => ['FORM-DOR-001'],
                'mapped_risks' => ['RSK-2026-00031'],
                'mapped_audits' => ['AUD-2026-00008'],
                'mapped_actions' => ['ACT-2026-00118'],
                'status' => 'Mapped',
            ]);
        }

        $iso9001Future = QmsStandard::where('code', 'ISO-9001-2026')->first();
        if ($iso9001Future) {
            QmsComplianceChange::updateOrCreate(['reference' => 'CHG-STD-2026-00001'], [
                'qms_standard_id' => $iso9001Future->id,
                'change_type' => 'Edition transition',
                'status' => 'Assessment required',
                'summary' => 'Assess impact of the future ISO 9001 edition on internal controls, documents, forms, audits and training.',
                'impacted_areas' => ['Documents', 'Forms', 'Audits', 'Training', 'Risk register'],
                'actions_required' => ['Assign owner', 'Compare internal mappings', 'Create transition actions after publication'],
                'due_date' => now()->addMonths(2)->toDateString(),
            ]);
        }

        foreach ([
            ['event-phase', 'ORG-2026', 'PHASE-TAXI', 'Taxi-out', 'Aviation phase taxonomy term for reporting forms.', 'ICAO/ADREP-compatible internal mapping', 'TAXI', ['cict_taxonomy' => 'event phase']],
            ['event-phase', 'ORG-2026', 'PHASE-APPROACH', 'Approach', 'Aviation phase taxonomy term for reporting forms.', 'ICAO/ADREP-compatible internal mapping', 'APR', ['cict_taxonomy' => 'event phase']],
            ['finding-type', 'ORG-2026', 'FINDING-NC', 'Nonconformity', 'Finding classification requiring evidence and criterion.', 'Organization', null, ['requires_ncr_review' => true]],
            ['finding-type', 'ORG-2026', 'FINDING-OFI', 'Opportunity for Improvement', 'Improvement finding without automatic CAPA escalation.', 'Organization', null, ['automatic_capa' => false]],
            ['root-cause-tool', 'ORG-2026', 'RCA-5WHY', '5 Whys', 'Reusable human-led investigation tool.', 'Organization', null, ['ai_may_suggest' => true, 'human_approval_required' => true]],
            ['root-cause-tool', 'ORG-2026', 'RCA-SHELL', 'SHELL', 'Human-factors analysis structure for aviation safety.', 'Organization', null, ['blame_avoidance' => true]],
        ] as [$taxonomy, $version, $code, $label, $description, $source, $externalCode, $mapping]) {
            QmsTaxonomyTerm::updateOrCreate([
                'taxonomy' => $taxonomy,
                'taxonomy_version' => $version,
                'code' => $code,
            ], [
                'label' => $label,
                'description' => $description,
                'source' => $source,
                'external_code' => $externalCode,
                'mapping' => $mapping,
                'effective_from' => now()->startOfYear()->toDateString(),
                'effective_to' => null,
                'active' => true,
            ]);
        }

        $occurrence = QmsOccurrence::where('reference', 'QMS-2026-00435')->first();
        $action = QmsAction::where('reference', 'CAPA-2026-00077')->first();
        $risk = QmsRisk::where('reference', 'RSK-2026-00031')->first();

        if ($occurrence && $action) {
            QmsRecordLink::updateOrCreate([
                'source_type' => QmsOccurrence::class,
                'source_id' => $occurrence->id,
                'target_type' => QmsAction::class,
                'target_id' => $action->id,
            ], [
                'relationship' => 'Generated CAPA',
                'source_reference' => $occurrence->reference,
                'target_reference' => $action->reference,
            ]);
        }

        if ($occurrence && $risk) {
            QmsRecordLink::updateOrCreate([
                'source_type' => QmsOccurrence::class,
                'source_id' => $occurrence->id,
                'target_type' => QmsRisk::class,
                'target_id' => $risk->id,
            ], [
                'relationship' => 'Risk signal',
                'source_reference' => $occurrence->reference,
                'target_reference' => $risk->reference,
            ]);
        }

        $admin = User::where('email', 'admin@qms.test')->first();
        QmsNotification::updateOrCreate([
            'title' => 'HSE review required',
            'source_reference' => 'QMS-2026-00435',
        ], [
            'user_id' => $admin?->id,
            'body' => 'Ground safety occurrence is waiting for screening and assignment.',
            'read_at' => null,
        ]);

        QmsNotification::updateOrCreate([
            'title' => 'Document review due',
            'source_reference' => 'DOC-HSE-001',
        ], [
            'user_id' => $admin?->id,
            'body' => 'Contractor HSE Manual is in review status.',
            'read_at' => null,
        ]);

        QmsAiProvider::updateOrCreate(['name' => 'Entity Secure AI - Pending Approval'], [
            'provider_type' => 'Paid secured enterprise API',
            'model_name' => 'Entity-trained controlled model',
            'training_scope' => 'Entity-trained approved QMS/SMS knowledge only',
            'security_tier' => 'Paid secured enterprise',
            'data_residency' => 'Contract-controlled hosting region',
            'is_approved' => false,
            'is_enabled' => false,
            'governance_notes' => 'AI remains blocked until legal, IT security, DPA, paid provider, and entity training controls are approved.',
        ]);

        QmsFormDefinition::updateOrCreate(['code' => 'FORM-DOR-001'], [
            'name' => 'Dispatch Occurrence Report',
            'version' => 1,
            'module' => 'Occurrence',
            'status' => 'Published',
            'schema' => [
                'required' => ['title', 'reported_by', 'event_date', 'location', 'description'],
                'sections' => ['Header', 'Commander voyage details', 'Aircraft and flight details', 'Action taken'],
                'conditional' => ['flight_fields_when' => 'aviation report type'],
            ],
            'change_note' => 'Initial controlled form definition.',
        ]);

        QmsFormDefinition::updateOrCreate(['code' => 'FORM-PUBLIC-001'], [
            'name' => 'Public Safety Reporting Intake',
            'version' => 1,
            'module' => 'Public Portal',
            'status' => 'Published',
            'schema' => [
                'required' => ['category', 'description'],
                'supports' => ['anonymous', 'confidential'],
            ],
            'change_note' => 'Public voluntary/confidential intake form.',
        ]);

        QmsWorkflowDefinition::updateOrCreate(['code' => 'WF-OCC-001'], [
            'name' => 'Occurrence to CAPA Closure',
            'version' => 1,
            'module' => 'Occurrence',
            'status' => 'Published',
            'stages' => ['Submitted', 'HSE Review', 'Investigation', 'CAPA', 'Verification', 'Closed'],
            'rules' => [
                'high_risk_requires_investigation' => true,
                'closure_requires_action_verification' => true,
                'confidential_identity_restricted' => true,
            ],
            'change_note' => 'Core SMS/QMS occurrence workflow.',
        ]);

        QmsWorkflowDefinition::updateOrCreate(['code' => 'WF-DOC-001'], [
            'name' => 'Controlled Document Lifecycle',
            'version' => 1,
            'module' => 'Documents',
            'status' => 'Published',
            'stages' => ['Draft', 'Review', 'Approved', 'Published', 'Archived'],
            'rules' => ['published_requires_version' => true, 'review_date_required' => true],
            'change_note' => 'Documented information lifecycle.',
        ]);

        $this->call(QmsReporterProductSeeder::class);


        QmsReportDesign::updateOrCreate(['code' => 'RPT-OCC-001'], [
            'name' => 'Occurrence Register and Risk Summary',
            'version' => 1,
            'module' => 'Occurrences',
            'status' => 'Published',
            'layout' => [
                'sections' => ['Header', 'Filters', 'Occurrence register', 'Risk summary', 'CAPA summary', 'Audit evidence'],
                'columns' => ['Reference', 'Title', 'Type', 'Stage', 'Risk', 'Reported By', 'Owner', 'Due Date'],
                'grouping' => ['Workflow stage', 'Risk rating', 'Department'],
                'confidentiality' => 'Mask anonymous and confidential reporter identity unless authorized.',
            ],
            'data_sources' => ['qms_occurrences', 'qms_actions', 'qms_risks', 'qms_audit_logs'],
            'output_formats' => ['Screen', 'CSV', 'PDF', 'Excel'],
            'change_note' => 'Default production occurrence register design.',
        ]);

        QmsReportDesign::updateOrCreate(['code' => 'RPT-CAPA-001'], [
            'name' => 'CAPA Effectiveness and Overdue Report',
            'version' => 1,
            'module' => 'Actions',
            'status' => 'Published',
            'layout' => [
                'sections' => ['Header', 'Overdue actions', 'Verification queue', 'Effectiveness review', 'Closure evidence'],
                'columns' => ['Reference', 'Source', 'Action', 'Owner', 'Priority', 'Status', 'Due Date'],
                'grouping' => ['Owner', 'Priority', 'Status'],
                'confidentiality' => 'Show source references only to authorized action owners.',
            ],
            'data_sources' => ['qms_actions', 'qms_occurrences', 'qms_record_notes'],
            'output_formats' => ['Screen', 'CSV', 'PDF'],
            'change_note' => 'Default production CAPA design.',
        ]);

        QmsNotificationDesign::updateOrCreate(['code' => 'MSG-OCC-001'], [
            'name' => 'Occurrence Submitted',
            'version' => 1,
            'module' => 'Occurrences',
            'event_trigger' => 'occurrence.submitted',
            'status' => 'Published',
            'recipients' => [
                'to' => ['HSE Reviewer', 'Occurrence Owner'],
                'cc' => ['Reporter', 'Department Manager'],
            ],
            'conditions' => [
                'rules' => ['status:Submitted', 'risk:any'],
                'restricted_identity' => 'Respect anonymous/confidential flags.',
            ],
            'subject_template' => '[{{reference}}] {{title}} requires QMS review',
            'body_template' => 'Record {{reference}} is waiting for {{stage}}. Review location, risk, evidence, and immediate action.',
            'change_note' => 'Default occurrence submitted message.',
        ]);

        QmsNotificationDesign::updateOrCreate(['code' => 'MSG-CAPA-001'], [
            'name' => 'CAPA Due or Overdue',
            'version' => 1,
            'module' => 'Actions',
            'event_trigger' => 'action.due',
            'status' => 'Published',
            'recipients' => [
                'to' => ['Action Owner'],
                'cc' => ['QMS Manager', 'Source Record Owner'],
            ],
            'conditions' => [
                'rules' => ['status:not Closed', 'due_date:within 3 days or overdue'],
                'restricted_identity' => 'Respect confidential source records.',
            ],
            'subject_template' => '[{{reference}}] CAPA action requires attention',
            'body_template' => 'Action {{reference}} is {{status}} and due on {{due_date}}. Update progress, evidence, or verification.',
            'change_note' => 'Default CAPA reminder and escalation message.',
        ]);

        $emailDesign = QmsEmailDesign::updateOrCreate(['code' => 'EMAIL-OCC-001'], [
            'name' => 'QMS Record Action Email Layout',
            'version' => 1,
            'status' => 'Published',
            'builder_schema' => [
                'components' => ['Logo', 'Heading', 'Record information', 'Action button', 'Footer'],
                'editor' => 'Approved visual builder adapter pending procurement.',
                'layout' => 'Responsive single-column transactional email.',
            ],
            'html_snapshot' => null,
            'variables' => ['user.name', 'record.reference', 'record.title', 'record.status', 'url.view_record'],
            'change_note' => 'Default portable email layout foundation.',
        ]);

        $template = QmsNotificationTemplate::updateOrCreate(['code' => 'NTF-OCC-001'], [
            'name' => 'Occurrence Requires Review',
            'version' => 1,
            'module' => 'Occurrences',
            'status' => 'Published',
            'email_design_id' => $emailDesign->id,
            'subject_template' => '[{{record.reference}}] {{record.title}} requires review',
            'body_template' => 'Hello {{user.name}}, record {{record.reference}} is at {{record.status}} and requires your review. Open {{url.view_record}}.',
            'allowed_variables' => ['user.name', 'record.reference', 'record.title', 'record.status', 'url.view_record'],
            'change_note' => 'Default separated notification content template.',
        ]);

        $group = QmsNotificationGroup::updateOrCreate(['code' => 'NG-SAFETY-KEY-USERS'], [
            'name' => 'Safety Key Users',
            'owner' => 'Safety Manager',
            'status' => 'Active',
            'description' => 'Scoped safety reviewers and escalation recipients.',
        ]);

        $group->members()->updateOrCreate([
            'member_type' => 'role',
            'member_reference' => 'Safety Admin',
        ], [
            'display_name' => 'Safety Admin role',
        ]);

        QmsNotificationRule::updateOrCreate(['code' => 'RULE-OCC-MAJOR-001'], [
            'name' => 'Major occurrence review escalation',
            'module' => 'Occurrences',
            'event_trigger' => 'occurrence.accepted',
            'status' => 'Published',
            'notification_template_id' => $template->id,
            'conditions' => ['all' => ['risk_rating:High or Critical', 'status:Accepted']],
            'recipients' => ['targets' => ['Safety Key Users', 'Department Manager', 'Occurrence Owner']],
            'channels' => ['In-App', 'Email'],
            'timing' => ['schedule' => 'Immediately; +3 days if not reviewed; +7 days manager escalation'],
            'change_note' => 'Default rule builder output for high-risk occurrence acceptance.',
        ]);

        QmsPermissionTemplate::updateOrCreate(['code' => 'PERM-SAFETY-KEY-USER'], [
            'name' => 'Safety Key User',
            'status' => 'Active',
            'permissions' => ['occurrences.view.department', 'occurrences.review', 'recommendations.create', 'actions.assign', 'actions.escalate'],
            'default_scopes' => ['DEPARTMENT', 'ASSIGNED'],
            'description' => 'Can monitor and process incidents within assigned safety scope without global administration access.',
        ]);

        QmsAccessScope::updateOrCreate([
            'principal_type' => 'role',
            'principal_reference' => 'Safety Admin',
            'module' => 'Occurrences',
            'scope_type' => 'ALL',
        ], [
            'scope_value' => 'Safety',
            'status' => 'Active',
        ]);

        $yahya = User::where('email', 'yahya.alnaaimi@qms.test')->first();
        if ($yahya) {
            QmsKeyUserAssignment::updateOrCreate([
                'user_id' => $yahya->id,
                'module' => 'Occurrences',
                'scope_type' => 'DEPARTMENT',
                'scope_value' => 'Safety',
            ], [
                'capabilities' => ['monitor', 'review', 'recommend', 'assign_actions', 'escalate'],
                'effective_from' => now()->toDateString(),
                'effective_until' => null,
                'status' => 'Active',
            ]);
        }

        QmsRecommendation::updateOrCreate(['reference' => 'REC-2026-00021'], [
            'source_reference' => 'QMS-2026-00435',
            'investigation_reference' => 'INV-2026-00012',
            'finding' => 'Scaffolding area lacked consistent barricade and signage controls.',
            'root_cause' => 'Contractor access control and pre-task verification were not consistently applied.',
            'recommendation' => 'Introduce mandatory pre-task signage verification for temporary work zones.',
            'rationale' => 'A formal verification step reduces recurrence and improves supervisor accountability.',
            'priority' => 'High',
            'safety_relevance' => 'Ground Safety',
            'owner' => 'HSE',
            'status' => 'Review',
            'approval_decision' => 'Pending',
        ]);

        QmsSystemSetting::updateOrCreate(['key' => 'brand.primary'], [
            'group' => 'Branding',
            'value' => ['organization' => 'QMS.ysaidea.com', 'system' => 'Enterprise Quality Management System', 'primary_color' => '#0867a8'],
            'is_sensitive' => false,
            'status' => 'Active',
            'change_note' => 'Default branding control center setting.',
        ]);

        foreach ([
            ['QMS-CORE', 'QMS Core'],
            ['SMS', 'Safety Management System'],
            ['HSE', 'Health, Safety and Environment'],
            ['RISK', 'Enterprise Risk Management'],
            ['AUDIT', 'Audit Management'],
            ['AI', 'Controlled AI'],
        ] as [$code, $name]) {
            QmsModuleLicense::updateOrCreate(['code' => $code], [
                'name' => $name,
                'enabled' => $code !== 'AI',
                'status' => $code === 'AI' ? 'Pending Approval' : 'Active',
                'expires_on' => now()->addYear()->toDateString(),
                'limits' => ['users' => 250, 'storage_gb' => 100],
                'notes' => $code === 'AI' ? 'AI is disabled until provider governance is approved.' : 'Initial production module license.',
            ]);
        }

        foreach ([
            ['NUM-REP', 'Reporting', 'REP'],
            ['NUM-INC', 'Incidents', 'INC'],
            ['NUM-OBS', 'Observations', 'OBS'],
            ['NUM-NCR', 'Non-Conformance', 'NCR'],
            ['NUM-AUD', 'Audits', 'AUD'],
            ['NUM-ACT', 'Actions', 'ACT'],
            ['NUM-FBK', 'Feedback', 'FBK'],
        ] as [$code, $module, $prefix]) {
            QmsNumberingRule::updateOrCreate(['code' => $code], [
                'module' => $module,
                'prefix' => $prefix,
                'pattern' => '{PREFIX}-{YYYY}-{SEQ:6}',
                'next_sequence' => 1,
                'reset_annually' => true,
                'status' => 'Active',
            ]);
        }

        QmsConfigurationPackage::updateOrCreate(['code' => 'CFG-BASELINE-001'], [
            'name' => 'Production baseline configuration',
            'version' => 1,
            'status' => 'Validated',
            'payload' => ['includes' => ['forms', 'workflows', 'notifications', 'numbering', 'permission templates']],
            'effective_date' => now()->toDateString(),
            'validation_summary' => 'Baseline dependency checks completed for the production foundation.',
        ]);

        QmsRetentionRule::updateOrCreate(['code' => 'RET-SAFETY-STD'], [
            'module' => 'Occurrences',
            'classification' => 'Safety Record',
            'retention_years' => 10,
            'legal_hold_allowed' => true,
            'disposition' => 'Archive',
            'status' => 'Active',
        ]);

        QmsAttachment::updateOrCreate(['record_reference' => 'QMS-2026-00435', 'original_name' => 'evidence-placeholder.txt'], [
            'record_type' => QmsOccurrence::class,
            'record_id' => QmsOccurrence::where('reference', 'QMS-2026-00435')->value('id'),
            'uploaded_by' => $admin?->id,
            'stored_path' => 'secure-evidence/evidence-placeholder.txt',
            'mime_type' => 'text/plain',
            'size_bytes' => 0,
            'content_hash' => hash('sha256', 'placeholder'),
            'classification' => 'Internal',
            'scan_status' => 'Pending',
            'quarantined' => false,
            'metadata' => ['note' => 'Metadata-only secure attachment foundation.'],
        ]);

        QmsElectronicSignature::updateOrCreate(['record_reference' => 'DOC-HSE-001', 'meaning' => 'Document review acknowledgement'], [
            'record_type' => QmsDocument::class,
            'record_id' => QmsDocument::where('reference', 'DOC-HSE-001')->value('id'),
            'user_id' => $admin?->id,
            'signer_name' => $admin?->name ?? 'QMS Administrator',
            'record_version' => '1.4',
            'snapshot_hash' => hash('sha256', 'DOC-HSE-001-v1.4'),
            'auth_context' => ['method' => 'session', 'reauth_required' => false],
            'reason' => 'Seeded signature architecture example.',
            'signed_at' => now(),
        ]);

        QmsIntegrationEvent::updateOrCreate(['idempotency_key' => 'baseline-qms-config-published'], [
            'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            'event_type' => 'configuration.published',
            'source_module' => 'Administration',
            'status' => 'Pending',
            'payload' => ['package' => 'CFG-BASELINE-001'],
            'attempts' => 0,
            'available_at' => now(),
        ]);

        QmsRecordSimilarity::updateOrCreate([
            'source_reference' => 'REP-2026-000421',
            'candidate_reference' => 'INC-2026-000183',
        ], [
            'score' => 88,
            'matched_on' => ['location', 'type', 'narrative keywords'],
            'decision' => 'Linked by reviewer',
            'decided_by' => $admin?->id,
            'decided_at' => now(),
        ]);

        QmsSafetyPromotion::updateOrCreate(['reference' => 'LESSON-2026-00003'], [
            'title' => 'Temporary work zones need visible controls',
            'source_reference' => 'QMS-2026-00435',
            'deidentified_learning' => 'Teams should verify signage and barriers before temporary work starts, especially near active equipment routes.',
            'audience' => ['Engineering', 'Ground Operations', 'HSE'],
            'confidentiality_review' => 'Completed',
            'approval_status' => 'Draft',
            'published_at' => null,
        ]);

        QmsFeedbackItem::updateOrCreate(['reference' => 'FBK-2026-000900'], [
            'user_id' => $admin?->id,
            'context' => 'Reporter home',
            'feedback_type' => 'Improvement idea',
            'message' => 'Keep help and product feedback separate from safety reporting so users do not create the wrong record type.',
            'status' => 'New',
            'visibility' => 'Support',
            'metadata' => ['separate_from_safety_reporting' => true],
        ]);

        foreach ([
            [
                'DS-USERS-LOCAL',
                'Local Users Lookup',
                'Local Database',
                null,
                'users',
                'id',
                'name',
                ['email'],
                ['name', 'email'],
                ['active:true'],
                'current_user_scope',
                'default',
                'indexed_local',
                'on_change',
                50,
                'show_governed_empty_state',
                'Active',
                'Used by Reported By, action owner, approver, auditor and investigator selectors.',
            ],
            [
                'DS-ENTRA-USERS',
                'Microsoft Entra Synchronized Users',
                'Entra Sync',
                'entra-directory-sync',
                'directory_users',
                'entra_object_id',
                'display_name',
                ['employee_id', 'department', 'station'],
                ['display_name', 'employee_id', 'mail'],
                ['account_enabled:true', 'policy_scope:qms_allowed'],
                'directory_policy_scope',
                'tenant_default',
                'indexed_local',
                'scheduled_incremental',
                75,
                'fallback_to_last_successful_sync',
                'Draft',
                'Preferred architecture: synchronize permitted directory attributes locally before form lookup.',
            ],
            [
                'DS-FLEET-REGISTRY',
                'Aircraft and Fleet Registry',
                'Reference Data',
                'fleet-reference',
                'aircraft',
                'aircraft_id',
                'registration',
                ['aircraft_type', 'fleet', 'status'],
                ['registration', 'aircraft_type', 'fleet'],
                ['active:true'],
                'module_scope',
                'default',
                'indexed_local',
                'scheduled',
                100,
                'show_no_active_aircraft',
                'Active',
                'Used by aviation reporting forms with cascading aircraft type dependency.',
            ],
            [
                'DS-SUPPLIERS',
                'Approved Suppliers and Service Providers',
                'Local Database',
                null,
                'qms_suppliers',
                'id',
                'name',
                ['reference', 'risk_rating', 'status'],
                ['name', 'reference', 'category'],
                ['status:not Disqualified'],
                'supplier_scope',
                'default',
                'indexed_local',
                'on_change',
                50,
                'show_governed_empty_state',
                'Active',
                'Used by supplier NCR, SCAR, audit and external evidence workflows.',
            ],
        ] as [$code, $name, $sourceType, $connector, $entity, $keyField, $displayField, $secondary, $searchFields, $filters, $permissionScope, $organizationScope, $cachePolicy, $refreshPolicy, $maxResults, $failurePolicy, $status, $notes]) {
            QmsDataSource::updateOrCreate(['code' => $code], [
                'name' => $name,
                'source_type' => $sourceType,
                'connector' => $connector,
                'entity' => $entity,
                'key_field' => $keyField,
                'display_field' => $displayField,
                'secondary_display_fields' => $secondary,
                'search_fields' => $searchFields,
                'filters' => ['rules' => $filters],
                'permission_scope' => $permissionScope,
                'organization_scope' => $organizationScope,
                'cache_policy' => $cachePolicy,
                'refresh_policy' => $refreshPolicy,
                'max_results' => $maxResults,
                'failure_policy' => $failurePolicy,
                'status' => $status,
                'governance_notes' => $notes,
            ]);
        }

        foreach ([
            ['PACK-CORE-QMS', 'Core Enterprise QMS', 'Core', 'QMS-CORE', true, 'Active', ['Document control', 'Audit', 'NCR', 'CAPA', 'Objectives', 'Management review']],
            ['PACK-AVIATION', 'Aviation Safety and Quality Pack', 'Aviation', 'SMS', true, 'Active', ['Occurrence reporting', 'Hazard and risk', 'Investigation', 'SHELL', 'Safety assurance']],
            ['PACK-SUPPLIER', 'Supplier Quality Pack', 'Supplier', 'SUPPLIER', true, 'UAT', ['Supplier qualification', 'Approved supplier list', 'SCAR', 'Scorecards', 'External evidence portal']],
            ['PACK-MANUFACTURING', 'Manufacturing / Product Quality Pack', 'Manufacturing', 'MFG', false, 'Planned', ['Inspection plans', 'FMEA', 'SPC', '8D', 'Lot and serial traceability']],
            ['PACK-SERVICE', 'Service Quality Pack', 'Service', 'SERVICE', false, 'Planned', ['Service failure', 'Complaints', 'SLA quality', 'Customer feedback']],
            ['PACK-LAB', 'Laboratory / Calibration Pack', 'Laboratory', 'LAB', false, 'Planned', ['Calibration certificates', 'Out-of-tolerance events', 'Measurement traceability']],
            ['PACK-FUTURE-REG', 'Future Regulated Extension Pack', 'Future Regulated', 'REG', false, 'Planned', ['Validation evidence', 'Specialized e-record controls', 'Explicit regulatory scope']],
        ] as [$code, $name, $category, $licenseCode, $enabled, $status, $capabilities]) {
            QmsDomainPack::updateOrCreate(['code' => $code], [
                'name' => $name,
                'category' => $category,
                'license_code' => $licenseCode,
                'enabled' => $enabled,
                'status' => $status,
                'capabilities' => $capabilities,
                'shared_engines' => ['Workflow', 'Actions', 'Approvals', 'Notifications', 'Attachments', 'Audit Trail', 'Numbering', 'Reporting', 'Analytics', 'AI Gateway'],
                'governance_notes' => 'Domain capability pack uses shared engines and remains independently licensable.',
            ]);
        }

        QmsSyncAdapter::updateOrCreate(['code' => 'SYNC-ENTRA-USERS'], [
            'name' => 'Microsoft Entra user synchronization',
            'provider' => 'Microsoft Entra ID',
            'purpose' => 'User, group, manager and department lookup foundation',
            'status' => 'Not configured',
            'field_mapping' => ['id' => 'entra_object_id', 'name' => 'displayName', 'email' => 'mail', 'department' => 'department'],
            'sync_policy' => ['mode' => 'scheduled_incremental', 'fallback' => 'local_cache', 'delete_policy' => 'deactivate_only'],
            'last_success_at' => null,
            'last_error' => 'Credentials and tenant approval required before activation.',
        ]);

        QmsOfflineProfile::updateOrCreate(['code' => 'OFF-OCC-QUICK'], [
            'name' => 'Quick occurrence reporting offline profile',
            'module' => 'Occurrences',
            'enabled' => false,
            'allowed_operations' => ['Create draft', 'Attach evidence metadata', 'Queue submission'],
            'sync_rules' => ['encrypt_local_cache' => true, 'validate_form_version' => true, 'recheck_permissions_on_sync' => true],
            'conflict_policy' => 'server_authoritative_review',
            'status' => 'Design',
        ]);

        foreach ([
            ['MON-QUEUE', 'Queue and failed job monitor', 'Operations', 'Ready', ['failed_jobs', 'retry_queue', 'dead_letter_review'], 'Migration foundation ready; worker service to be configured on VPS.'],
            ['MON-SCHEDULER', 'Scheduler health monitor', 'Operations', 'Ready', ['schedule_last_run', 'missed_tasks', 'report_delivery'], 'Scheduler command should run every minute on VPS.'],
            ['MON-AI', 'Controlled AI governance monitor', 'AI', 'Blocked', ['provider_approval', 'usage_budget', 'audit_log'], 'AI remains blocked until paid secured provider is approved.'],
            ['MON-EXPORTS', 'Sensitive export monitor', 'Security', 'Ready', ['export_audit', 'large_export_queue', 'permission_check'], 'Queued export architecture prepared for production hardening.'],
        ] as [$code, $name, $area, $status, $checks, $result]) {
            QmsSystemMonitor::updateOrCreate(['code' => $code], [
                'name' => $name,
                'area' => $area,
                'status' => $status,
                'checks' => $checks,
                'last_result' => $result,
                'checked_at' => now(),
            ]);
        }

        QmsSavedView::updateOrCreate(['name' => 'Executive high-risk watch', 'module' => 'Intelligence'], [
            'owner' => 'QMS Administrator',
            'filters' => ['risk' => ['High', 'Critical'], 'status' => ['Open', 'In progress']],
            'shared' => true,
        ]);
    }
}
