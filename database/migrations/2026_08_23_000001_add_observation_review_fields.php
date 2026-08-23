<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_occurrences', function (Blueprint $table) {
            $table->string('record_family')->default('Occurrence')->after('reference')->index();
            $table->string('observation_type')->nullable()->after('type')->index();
            $table->string('area')->nullable()->after('observation_type');
            $table->string('unit')->nullable()->after('area');
            $table->string('department_name')->nullable()->after('unit');
            $table->string('observer')->nullable()->after('reported_by');
            $table->date('observed_on')->nullable()->after('event_date');
            $table->time('observed_at')->nullable()->after('observed_on');
            $table->text('potential_consequence')->nullable()->after('description');
            $table->text('temporary_control')->nullable()->after('immediate_corrective_action');
            $table->string('review_decision')->nullable()->after('workflow_stage')->index();
            $table->text('reviewer_comments')->nullable()->after('review_decision');
            $table->string('reviewer_name')->nullable()->after('reviewer_comments');
            $table->timestamp('reviewed_at')->nullable()->after('reviewer_name');
            $table->text('reporter_visible_message')->nullable()->after('reviewed_at');
            $table->boolean('action_required')->default(false)->after('reporter_visible_message')->index();
        });
    }

    public function down(): void
    {
        Schema::table('qms_occurrences', function (Blueprint $table) {
            $table->dropColumn([
                'record_family',
                'observation_type',
                'area',
                'unit',
                'department_name',
                'observer',
                'observed_on',
                'observed_at',
                'potential_consequence',
                'temporary_control',
                'review_decision',
                'reviewer_comments',
                'reviewer_name',
                'reviewed_at',
                'reporter_visible_message',
                'action_required',
            ]);
        });
    }
};
