<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores uploaded images as WebP under the project's public/upload directory.
 *
 * Layout: public/{root}/{module}/{yyyy}/{mm}/{name}.webp
 * with a matching {name}-thumb.webp alongside every full-size image.
 *
 * Paths returned and accepted by this service are always relative to public/
 * (e.g. "upload/products/2026/07/ab12cd.webp") so they can be handed straight
 * to asset() and stored in a single varchar column.
 */
class MediaService
{
    /**
     * Source formats accepted for conversion.
     */
    private const READERS = [
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_GIF => 'imagecreatefromgif',
        IMAGETYPE_WEBP => 'imagecreatefromwebp',
        IMAGETYPE_BMP => 'imagecreatefrombmp',
    ];

    /**
     * Convert an uploaded image to WebP and store it under the given module.
     *
     * @param  string  $module  Folder name, e.g. "products" or "settings"
     * @return string Path relative to public/, ready for asset()
     */
    public function storeImage(UploadedFile $file, string $module): string
    {
        $image = $this->read($file);

        $config = config('erp.media');
        $directory = $this->directory($module);
        $name = $this->uniqueName($file);

        $full = $this->resize($image, (int) $config['max_width']);
        $this->write($full, public_path($directory.'/'.$name.'.webp'), (int) $config['quality']);

        $thumb = $this->resize($image, (int) $config['thumb_width']);
        $this->write($thumb, public_path($directory.'/'.$name.'-thumb.webp'), (int) $config['quality']);

        imagedestroy($image);

        if ($full !== $image) {
            imagedestroy($full);
        }

        if ($thumb !== $image) {
            imagedestroy($thumb);
        }

        return $directory.'/'.$name.'.webp';
    }

    /**
     * Store a non-image upload (PDF invoice, receipt scan) unchanged.
     *
     * @return string Path relative to public/
     */
    public function storeFile(UploadedFile $file, string $module): string
    {
        $directory = $this->directory($module);
        $name = $this->uniqueName($file).'.'.strtolower($file->getClientOriginalExtension() ?: 'bin');

        $file->move(public_path($directory), $name);

        return $directory.'/'.$name;
    }

    /**
     * Store an image when the upload is an image and the raw file otherwise.
     * Used by the fields that accept "image or PDF" (receipts, invoices).
     */
    public function store(UploadedFile $file, string $module): string
    {
        return $this->isConvertibleImage($file)
            ? $this->storeImage($file, $module)
            : $this->storeFile($file, $module);
    }

    /**
     * Delete a stored file and its thumbnail. Safe to call with null or with a
     * path that no longer exists.
     */
    public function delete(?string $path): void
    {
        if (! $path || ! $this->isManaged($path)) {
            return;
        }

        foreach ([$path, $this->thumbPath($path)] as $candidate) {
            $absolute = public_path($candidate);

            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }
    }

    /**
     * Public URL for a stored path, falling back to the placeholder when the
     * record has no image.
     */
    public function url(?string $path, bool $thumb = false): string
    {
        if (! $path) {
            return asset('assets/img/placeholder.svg');
        }

        // Records created before the media pipeline still point at the
        // `public` storage disk.
        if (! $this->isManaged($path)) {
            return asset('storage/'.$path);
        }

        $candidate = $thumb ? $this->thumbPath($path) : $path;

        if (! is_file(public_path($candidate))) {
            $candidate = $path;
        }

        return asset($candidate);
    }

    /**
     * Whether the upload is an image this service can convert.
     */
    public function isConvertibleImage(UploadedFile $file): bool
    {
        $info = @getimagesize($file->getRealPath());

        return $info !== false && isset(self::READERS[$info[2]]);
    }

    /**
     * Sibling thumbnail path for a stored image.
     */
    public function thumbPath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            return $path;
        }

        return substr($path, 0, -(strlen($extension) + 1)).'-thumb.'.$extension;
    }

    /**
     * True when the path is one this service wrote (public/upload/...), as
     * opposed to a legacy storage-disk path.
     */
    private function isManaged(string $path): bool
    {
        return str_starts_with($path, config('erp.media.root').'/');
    }

    private function directory(string $module): string
    {
        $directory = implode('/', [
            config('erp.media.root'),
            Str::slug($module) ?: 'misc',
            now()->format('Y'),
            now()->format('m'),
        ]);

        $absolute = public_path($directory);

        if (! is_dir($absolute) && ! @mkdir($absolute, 0o755, true) && ! is_dir($absolute)) {
            throw new RuntimeException("Unable to create upload directory [{$absolute}].");
        }

        return $directory;
    }

    private function uniqueName(UploadedFile $file): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $base = $base !== '' ? Str::limit($base, 40, '') : 'file';

        return $base.'-'.Str::lower(Str::random(8));
    }

    private function read(UploadedFile $file): GdImage
    {
        $info = @getimagesize($file->getRealPath());

        if ($info === false || ! isset(self::READERS[$info[2]])) {
            throw new RuntimeException('The uploaded file is not an image this system can process.');
        }

        $image = @self::READERS[$info[2]]($file->getRealPath());

        if (! $image instanceof GdImage) {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        return $info[2] === IMAGETYPE_JPEG
            ? $this->autoOrient($image, $file->getRealPath())
            : $image;
    }

    /**
     * Photos taken on a phone carry their rotation in EXIF rather than in the
     * pixel data; bake it in so the stored WebP is upright.
     */
    private function autoOrient(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 0);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /**
     * Scale the long edge down to $maxWidth. Images already smaller are
     * returned untouched — upscaling only adds bytes.
     */
    private function resize(GdImage $image, int $maxWidth): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($maxWidth <= 0 || max($width, $height) <= $maxWidth) {
            return $image;
        }

        $ratio = $maxWidth / max($width, $height);
        $target = imagecreatetruecolor((int) round($width * $ratio), (int) round($height * $ratio));

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled(
            $target, $image,
            0, 0, 0, 0,
            imagesx($target), imagesy($target),
            $width, $height
        );

        return $target;
    }

    private function write(GdImage $image, string $absolutePath, int $quality): void
    {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if (! imagewebp($image, $absolutePath, $quality)) {
            throw new RuntimeException("Unable to write image to [{$absolutePath}].");
        }
    }
}
