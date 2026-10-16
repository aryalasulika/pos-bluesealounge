<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FR-12: data pembelian yang digunakan untuk laporan tidak boleh hilang
     * tanpa jejak. Soft delete mempertahankan catatan historis (record asli
     * tetap ada di database) sementara audit log mencatat tindakan koreksi.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
