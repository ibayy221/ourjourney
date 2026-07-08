<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            $table->enum('section', ['milestone', 'branch', 'gallery'])->default('gallery');
            $table->enum('type', ['photo', 'video']);
            $table->string('file_path');
            $table->string('title')->nullable();        // judul momen, dipakai untuk milestone
            $table->text('caption')->nullable();         // deskripsi singkat
            $table->string('category')->nullable();      // untuk filter di gallery (opsional)
            $table->string('chapter')->nullable();        // "Bab Satu", dst — hanya untuk milestone/branch
            $table->date('event_date')->nullable();       // tanggal momen, untuk sort kronologis
            $table->integer('order_index')->default(0);  // untuk drag-reorder manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};
