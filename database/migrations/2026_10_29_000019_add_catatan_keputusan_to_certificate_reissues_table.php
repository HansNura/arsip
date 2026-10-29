<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_reissues', function (Blueprint $table) {
            $table->text('catatan_keputusan')->nullable()->after('alasan');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_reissues', function (Blueprint $table) {
            $table->dropColumn('catatan_keputusan');
        });
    }
};
