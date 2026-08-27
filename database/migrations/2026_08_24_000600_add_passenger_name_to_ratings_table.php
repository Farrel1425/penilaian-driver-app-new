<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table): void {
            $table->string('passenger_name', 100)->nullable()->after('driver_id');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table): void {
            $table->dropColumn('passenger_name');
        });
    }
};
