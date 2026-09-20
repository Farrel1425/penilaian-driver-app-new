<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('job_vacancies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruitment_period_id')->constrained()->restrictOnDelete();
            $table->string('category', 100);
            $table->string('work_type', 100);
            $table->string('title');
            $table->text('description');
            $table->text('qualification');
            $table->text('compensation');
            $table->unsignedInteger('quota');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('job_vacancy_branch', function (Blueprint $table): void {
            $table->foreignId('job_vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->primary(['job_vacancy_id', 'branch_id']);
        });

        Schema::create('job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->string('full_name');
            $table->text('nik');
            $table->char('nik_hash', 64);
            $table->string('whatsapp', 30);
            $table->string('email');
            $table->string('domicile');
            $table->text('experience')->nullable();
            $table->string('document_path');
            $table->string('document_original_name');
            $table->string('status', 20)->default('new')->index();
            $table->timestamp('consent_at');
            $table->timestamps();

            $table->unique(['job_vacancy_id', 'nik_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_vacancy_branch');
        Schema::dropIfExists('job_vacancies');
        Schema::dropIfExists('recruitment_periods');
    }
};
