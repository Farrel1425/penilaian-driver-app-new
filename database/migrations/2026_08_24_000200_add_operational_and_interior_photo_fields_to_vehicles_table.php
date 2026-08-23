<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->date('stnk_expired_at')->nullable()->after('contract_expired_at');
            $table->date('kir_expired_at')->nullable()->after('stnk_expired_at');
            $table->string('interior_photo')->nullable()->after('photo');
        });

        foreach ([
            'bensin' => 'gasoline',
            'listrik' => 'electric',
        ] as $previous => $current) {
            DB::table('vehicles')->where('fuel_type', $previous)->update(['fuel_type' => $current]);
        }

        foreach ([
            'Pembelian' => 'purchase',
            'Sewa' => 'leasing',
            'Leasing/Sewa' => 'leasing',
            'Hibah' => 'grant',
            'Transfer Internal' => 'internal_transfer',
            'Lainnya' => 'other',
        ] as $previous => $current) {
            DB::table('vehicles')->where('acquisition_source', $previous)->update(['acquisition_source' => $current]);
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropColumn(['stnk_expired_at', 'kir_expired_at', 'interior_photo']);
        });
    }
};
