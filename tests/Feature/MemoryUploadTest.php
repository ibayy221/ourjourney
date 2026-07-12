<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Memory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemoryUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for authentication
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_admin_can_upload_single_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('photo1.jpg', 10, 'image/jpeg');

        $response = $this->actingAs($this->user)
            ->post(route('admin.memories.store'), [
                'file' => $file,
                'section' => 'gallery',
                'title' => 'Single Memory',
            ]);

        $response->assertRedirect(route('admin.memories.index'));
        $this->assertDatabaseCount('memories', 1);

        $memory = Memory::first();
        $this->assertEquals('gallery', $memory->section);
        $this->assertEquals('photo', $memory->type);
        $this->assertEquals('Single Memory', $memory->title);
        
        Storage::disk('public')->assertExists($memory->file_path);
    }

    public function test_admin_can_upload_multiple_files(): void
    {
        Storage::fake('public');

        $file1 = UploadedFile::fake()->create('photo1.jpg', 10, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('photo2.png', 10, 'image/png');
        $file3 = UploadedFile::fake()->create('video.mp4', 500, 'video/mp4');

        $response = $this->actingAs($this->user)
            ->post(route('admin.memories.store'), [
                'files' => [$file1, $file2, $file3],
                'section' => 'gallery',
                'title' => 'Multiple Memories',
            ]);

        $response->assertRedirect(route('admin.memories.index'));
        $this->assertDatabaseCount('memories', 3);

        $memories = Memory::orderBy('order_index')->get();
        $this->assertCount(3, $memories);

        $this->assertEquals(0, $memories[0]->order_index);
        $this->assertEquals(1, $memories[1]->order_index);
        $this->assertEquals(2, $memories[2]->order_index);

        $this->assertEquals('photo', $memories[0]->type);
        $this->assertEquals('photo', $memories[1]->type);
        $this->assertEquals('video', $memories[2]->type);

        foreach ($memories as $memory) {
            Storage::disk('public')->assertExists($memory->file_path);
        }
    }

    public function test_admin_can_submit_youtube_url(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.memories.store'), [
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'section' => 'gallery',
                'title' => 'Rickroll Video',
            ]);

        $response->assertRedirect(route('admin.memories.index'));
        $this->assertDatabaseCount('memories', 1);

        $memory = Memory::first();
        $this->assertEquals('gallery', $memory->section);
        $this->assertEquals('video', $memory->type);
        $this->assertEquals('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $memory->file_path);
        $this->assertTrue($memory->is_youtube);
        $this->assertEquals('dQw4w9WgXcQ', $memory->youtube_id);
        $this->assertEquals('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $memory->thumbnail_url);
    }

    public function test_admin_cannot_submit_invalid_youtube_url(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('admin.memories.store'), [
                'youtube_url' => 'https://google.com',
                'section' => 'gallery',
                'title' => 'Google Link',
            ]);

        $response->assertSessionHasErrors('youtube_url');
        $this->assertDatabaseCount('memories', 0);
    }

    public function test_old_file_deleted_when_replaced_with_youtube_url(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('photo1.jpg', 10, 'image/jpeg');

        // Store first
        $this->actingAs($this->user)
            ->post(route('admin.memories.store'), [
                'file' => $file,
                'section' => 'gallery',
            ]);

        $memory = Memory::first();
        $filePath = $memory->file_path;
        Storage::disk('public')->assertExists($filePath);

        // Update to YouTube
        $response = $this->actingAs($this->user)
            ->put(route('admin.memories.update', $memory), [
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'section' => 'gallery',
            ]);

        $response->assertRedirect(route('admin.memories.index'));
        
        $memory->refresh();
        $this->assertEquals('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $memory->file_path);
        $this->assertTrue($memory->is_youtube);

        // Assert old file deleted
        Storage::disk('public')->assertMissing($filePath);
    }
}
