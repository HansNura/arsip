<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->string('tanda_tangan_path')->nullable()->after('file_path');
            $table->string('nama_penandatangan')->nullable()->after('tanda_tangan_path');
            $table->string('jabatan_penandatangan')->nullable()->after('nama_penandatangan');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn([
                'tanda_tangan_path',
                'nama_penandatangan',
                'jabatan_penandatangan',
            ]);
        });
    }
};
