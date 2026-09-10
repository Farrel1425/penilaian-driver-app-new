<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('requires_sim')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        $now = now();
        $driverCategoryId = DB::table('employee_categories')->insertGetId([
            'name' => 'Driver',
            'requires_sim' => true,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('drivers', function (Blueprint $table): void {
            $table->foreignId('employee_category_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('employee_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        DB::table('drivers')->whereNull('employee_category_id')->update(['employee_category_id' => $driverCategoryId]);
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('employee_category_id');
        });

        Schema::dropIfExists('employee_categories');
    }
};
