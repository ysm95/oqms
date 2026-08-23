<?php

namespace Database\Seeders;

use App\Http\Controllers\Qms\ReportingController;
use App\Models\QmsNumberingRule;
use App\Models\QmsReportTypeRule;
use Illuminate\Database\Seeder;

class QmsReporterProductSeeder extends Seeder
{
    public function run(): void
    {
        QmsReportTypeRule::updateOrCreate(['report_type_key' => 'observation'], [
            'title' => 'Observation',
            'type' => 'Observation',
            'module' => 'Safety',
            'priority' => 'Safety',
            'description' => 'Report an Unsafe Act or Unsafe Condition for HSE review.',
            'published' => true,
            'requires_auth' => false,
            'supports_anonymous' => true,
            'allowed_roles' => ['Reporter', 'Public', 'Safety Admin', 'Quality Admin', 'Super Admin'],
            'allowed_departments' => [],
            'form_version' => 1,
            'effective_from' => now()->subDay()->toDateString(),
            'effective_until' => null,
            'sort_order' => 1,
            'status' => 'Active',
        ]);

        foreach (ReportingController::reportTypes() as $key => $type) {
            $reporterFacing = in_array($key, ['air-safety', 'ground-occurrence', 'hazard', 'confidential-safety'], true);
            $publicFacing = in_array($key, ['ground-occurrence', 'hazard', 'confidential-safety'], true);

            QmsReportTypeRule::updateOrCreate(['report_type_key' => $key], [
                'title' => $type['title'],
                'type' => $type['type'],
                'module' => $type['module'],
                'priority' => $type['priority'],
                'description' => $type['description'],
                'published' => true,
                'requires_auth' => ! $publicFacing,
                'supports_anonymous' => (bool) ($type['confidential'] ?? false) || $publicFacing,
                'allowed_roles' => $reporterFacing
                    ? array_values(array_filter(['Reporter', $publicFacing ? 'Public' : null, 'Safety Admin', 'Quality Admin', 'Super Admin']))
                    : ['Safety Admin', 'Quality Admin', 'Super Admin'],
                'allowed_departments' => [],
                'form_version' => 1,
                'effective_from' => now()->subDay()->toDateString(),
                'effective_until' => null,
                'sort_order' => $reporterFacing ? array_search($key, ['air-safety', 'ground-occurrence', 'hazard', 'confidential-safety'], true) + 10 : 100,
                'status' => 'Active',
            ]);
        }

        QmsNumberingRule::updateOrCreate(['code' => 'NUM-PUB'], [
            'module' => 'Reporter Intake',
            'prefix' => 'PUB',
            'pattern' => '{PREFIX}-{YYYY}-{SEQ:6}',
            'next_sequence' => 1,
            'reset_annually' => true,
            'status' => 'Active',
        ]);

        QmsNumberingRule::updateOrCreate(['code' => 'NUM-OBS'], [
            'module' => 'Observations',
            'prefix' => 'OBS',
            'pattern' => '{PREFIX}-{YYYY}-{SEQ:6}',
            'next_sequence' => 1,
            'reset_annually' => true,
            'status' => 'Active',
        ]);
    }
}
