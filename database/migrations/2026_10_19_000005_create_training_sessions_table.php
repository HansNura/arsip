<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('pertemuan_ke');
            $table->date('tanggal');
            $table->string('topik')->nullable();
            $table->string('pengajar')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'pertemuan_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
