<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('period');
            $table->unsignedTinyInteger('present_days')->default(0);
            $table->unsignedTinyInteger('sick_days')->default(0);
            $table->unsignedTinyInteger('permitted_days')->default(0);
            $table->unsignedTinyInteger('absent_days')->default(0);
            $table->timestamps();

            $table->unique(['driver_id', 'period']);
            $table->index(['branch_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_attendances');
    }
};
