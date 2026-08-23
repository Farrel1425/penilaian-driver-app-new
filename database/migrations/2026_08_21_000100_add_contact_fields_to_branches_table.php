<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->string('regency')->nullable()->after('address');
            $table->string('pic_name')->nullable()->after('regency');
            $table->string('phone', 30)->nullable()->after('pic_name');
            $table->string('email')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropColumn(['regency', 'pic_name', 'phone', 'email']);
        });
    }
};
