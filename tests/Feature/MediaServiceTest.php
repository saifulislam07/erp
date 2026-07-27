<?php

namespace Tests\Feature;

use App\Services\MediaService;
use App\Support\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * The upload pipeline writes into public/, outside the storage disk that
 * Storage::fake() intercepts, so these tests write real files and clean up
 * after themselves.
 */
class MediaServiceTest extends TestCase
{
    private MediaService $media;

    /** @var list<string> */
    private array $written = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = app(MediaService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->written as $path) {
            $this->media->delete($path);
        }

        parent::tearDown();
    }

    public function test_an_uploaded_image_is_converted_to_webp_with_a_thumbnail(): void
    {
        $path = $this->store(UploadedFile::fake()->image('photo.jpg', 900, 700));

        $this->assertStringStartsWith('upload/products/', $path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertFileExists(public_path($path));
        $this->assertFileExists(public_path($this->media->thumbPath($path)));

        // getimagesize reports IMAGETYPE_WEBP (18) for a real WebP file.
        $this->assertSame(IMAGETYPE_WEBP, getimagesize(public_path($path))[2]);
    }

    public function test_a_large_image_is_scaled_down_to_the_configured_limit(): void
    {
        config(['erp.media.max_width' => 400, 'erp.media.thumb_width' => 100]);

        $path = $this->store(UploadedFile::fake()->image('big.jpg', 1200, 600));

        [$width, $height] = getimagesize(public_path($path));

        $this->assertSame(400, $width);
        $this->assertSame(200, $height, 'aspect ratio should be preserved');
    }

    public function test_a_small_image_is_not_upscaled(): void
    {
        config(['erp.media.max_width' => 1600]);

        $path = $this->store(UploadedFile::fake()->image('small.jpg', 120, 90));

        $this->assertSame([120, 90], array_slice(getimagesize(public_path($path)), 0, 2));
    }

    public function test_stored_paths_are_grouped_by_module_and_month(): void
    {
        $path = $this->store(UploadedFile::fake()->image('logo.png', 200, 200), 'branding');

        $this->assertStringStartsWith('upload/branding/'.now()->format('Y/m').'/', $path);
    }

    public function test_deleting_removes_the_image_and_its_thumbnail(): void
    {
        $path = $this->media->storeImage(UploadedFile::fake()->image('gone.jpg', 200, 200), 'products');
        $thumb = $this->media->thumbPath($path);

        $this->media->delete($path);

        $this->assertFileDoesNotExist(public_path($path));
        $this->assertFileDoesNotExist(public_path($thumb));
    }

    public function test_url_falls_back_to_the_placeholder_when_nothing_is_stored(): void
    {
        $this->assertStringContainsString('placeholder.svg', $this->media->url(null));
    }

    public function test_url_still_resolves_paths_from_before_this_pipeline(): void
    {
        // Records created earlier point at the `public` storage disk.
        $this->assertStringContainsString('storage/products/old.jpg', $this->media->url('products/old.jpg'));
    }

    public function test_the_sanitiser_keeps_formatting_and_drops_anything_executable(): void
    {
        $this->assertSame(
            '<p>Fresh <strong>rice</strong></p>',
            HtmlSanitizer::clean('<p>Fresh <strong>rice</strong></p><script>steal()</script>')
        );

        $this->assertSame('<p>hi</p>', HtmlSanitizer::clean('<p onclick="steal()">hi</p>'));
        $this->assertSame('<a>link</a>', HtmlSanitizer::clean('<a href="javascript:steal()">link</a>'));
        $this->assertNull(HtmlSanitizer::clean('<p><br></p>'), 'an untouched editor should store nothing');
    }

    private function store(UploadedFile $file, string $module = 'products'): string
    {
        $path = $this->media->storeImage($file, $module);
        $this->written[] = $path;

        return $path;
    }
}
