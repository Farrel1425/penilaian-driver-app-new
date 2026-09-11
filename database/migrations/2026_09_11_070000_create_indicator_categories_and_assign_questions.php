<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('target_type')->index();
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['target_type', 'name']);
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignId('indicator_category_id')
                ->nullable()
                ->after('indicator')
                ->constrained('indicator_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        $now = now();
        $sortOrders = [];
        $indicators = DB::table('questions')
            ->select('target_type', 'indicator')
            ->whereNotNull('indicator')
            ->where('indicator', '!=', '')
            ->distinct()
            ->orderBy('target_type')
            ->orderBy('indicator')
            ->get();

        foreach ($indicators as $indicator) {
            $targetType = (string) $indicator->target_type;
            $sortOrders[$targetType] = ($sortOrders[$targetType] ?? 0) + 1;
            $categoryId = DB::table('indicator_categories')->insertGetId([
                'name' => $indicator->indicator,
                'target_type' => $targetType,
                'status' => 'active',
                'sort_order' => $sortOrders[$targetType],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('questions')
                ->where('target_type', $targetType)
                ->where('indicator', $indicator->indicator)
                ->update(['indicator_category_id' => $categoryId]);
        }

        Schema::table('questions', function (Blueprint $table): void {
            $table->dropColumn('indicator');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->string('indicator')->nullable()->after('question');
        });

        DB::table('questions')
            ->whereNotNull('indicator_category_id')
            ->orderBy('id')
            ->chunkById(100, function ($questions): void {
                $categoryNames = DB::table('indicator_categories')
                    ->whereIn('id', $questions->pluck('indicator_category_id'))
                    ->pluck('name', 'id');

                foreach ($questions as $question) {
                    DB::table('questions')->where('id', $question->id)->update([
                        'indicator' => $categoryNames[$question->indicator_category_id] ?? null,
                    ]);
                }
            });

        Schema::table('questions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('indicator_category_id');
        });

        Schema::dropIfExists('indicator_categories');
    }
};
