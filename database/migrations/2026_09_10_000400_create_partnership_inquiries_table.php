<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('whatsapp', 30);
            $table->string('email');
            $table->string('service');
            $table->string('estimated_need')->nullable();
            $table->string('contract_duration')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_inquiries');
    }
};
