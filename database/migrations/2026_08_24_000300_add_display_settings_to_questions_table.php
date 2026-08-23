<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->text('instruction')->nullable()->after('question');
            $table->string('placeholder')->nullable()->after('instruction');
            $table->string('rating_min_label')->nullable()->after('placeholder');
            $table->string('rating_max_label')->nullable()->after('rating_min_label');
            $table->string('icon_path')->nullable()->after('rating_max_label');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropColumn([
                'instruction',
                'placeholder',
                'rating_min_label',
                'rating_max_label',
                'icon_path',
            ]);
        });
    }
};
