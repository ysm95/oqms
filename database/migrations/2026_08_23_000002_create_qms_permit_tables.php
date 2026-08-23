<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_permits', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('permit_type')->index();
            $table->string('title');
            $table->text('work_description');
            $table->string('requester')->index();
            $table->string('department')->nullable()->index();
            $table->string('contractor')->nullable()->index();
            $table->string('area')->nullable()->index();
            $table->string('unit')->nullable()->index();
            $table->string('asset')->nullable();
            $table->dateTime('planned_start_at')->nullable()->index();
            $table->dateTime('planned_end_at')->nullable()->index();
            $table->dateTime('issued_at')->nullable()->index();
            $table->dateTime('closed_at')->nullable()->index();
            $table->string('status')->default('Draft')->index();
            $table->string('risk_rating')->default('Medium')->index();
            $table->string('residual_risk')->nullable()->index();
            $table->string('owner')->nullable()->index();
            $table->string('issuer')->nullable()->index();
            $table->string('current_approver')->nullable()->index();
            $table->boolean('isolation_required')->default(false)->index();
            $table->boolean('gas_test_required')->default(false)->index();
            $table->boolean('fire_watch_required')->default(false)->index();
            $table->boolean('standby_required')->default(false)->index();
            $table->json('loto_points')->nullable();
            $table->json('hazards')->nullable();
            $table->json('controls')->nullable();
            $table->json('required_ppe')->nullable();
            $table->json('required_training')->nullable();
            $table->json('linked_documents')->nullable();
            $table->json('approval_history')->nullable();
            $table->json('field_checks')->nullable();
            $table->text('closeout_summary')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->text('extension_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('qms_permit_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qms_permit_id')->constrained('qms_permits')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor')->nullable();
            $table->string('action')->index();
            $table->string('from_status')->nullable()->index();
            $table->string('to_status')->nullable()->index();
            $table->text('note')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_permit_activities');
        Schema::dropIfExists('qms_permits');
    }
};
