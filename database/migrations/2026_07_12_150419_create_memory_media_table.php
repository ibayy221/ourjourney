<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memory_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memory_id')->constrained('memories')->cascadeOnDelete();
            $table->string('file_path');
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // Migrate existing memories to memory_media
        $memories = DB::table('memories')->get();
        foreach ($memories as $memory) {
            if (!empty($memory->file_path)) {
                DB::table('memory_media')->insert([
                    'memory_id'   => $memory->id,
                    'file_path'   => $memory->file_path,
                    'type'        => $memory->type ?? 'photo',
                    'order_index' => 0,
                    'created_at'  => $memory->created_at ?? now(),
                    'updated_at'  => $memory->updated_at ?? now(),
                ]);
            }
        }

        // Drop file_path and type columns from memories table
        Schema::table('memories', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add file_path and type to memories
        Schema::table('memories', function (Blueprint $table) {
            $table->string('file_path')->nullable();
            $table->enum('type', ['photo', 'video'])->nullable();
        });

        // Copy back first media item to memories
        $mediaList = DB::table('memory_media')->orderBy('order_index')->get()->groupBy('memory_id');
        foreach ($mediaList as $memoryId => $media) {
            $first = $media->first();
            DB::table('memories')->where('id', $memoryId)->update([
                'file_path' => $first->file_path,
                'type'      => $first->type,
            ]);
        }

        Schema::dropIfExists('memory_media');
    }
};
