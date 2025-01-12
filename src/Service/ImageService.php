<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GdImage;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use Throwable;

class ImageService
{
    public function __construct(private readonly FileService $fileService)
    {
    }

    public function getWidth(Image $image): int
    {
        return imagesx($image->getImage()) ?: 0;
    }

    public function getHeight(Image $image): int
    {
        return imagesy($image->getImage()) ?: 0;
    }

    /**
     * @throws CreateError
     */
    public function create(int $width, int $height): Image
    {
        $image = imagecreatetruecolor($width, $height);

        if (is_bool($image)) {
            throw new CreateError('Image could not be created!');
        }

        $image = new Image($image);
        $this->enableAlphaBlending($image);

        return $image;
    }

    public function fillTransparent(Image $image): void
    {
        $this->fill($image, $this->getTransparentColor($image));
        $this->enableAlpha($image);
    }

    public function getColor(Image $image, int $red, int $green, int $blue, int $alpha = 0): int
    {
        return (int) imagecolorallocatealpha($image->getImage(), $red, $green, $blue, $alpha);
    }

    public function getTransparentColor(Image $image): int
    {
        return imagecolortransparent($image->getImage());
    }

    public function setTransparentColor(Image $image, int $color): void
    {
        imagecolortransparent($image->getImage(), $color);
    }

    /**
     * Zerstört das Bild.
     */
    public function destroy(Image $image): bool
    {
        return imagedestroy($image->getImage());
    }

    /**
     * @throws FileNotFound
     * @throws LoadError
     */
    public function load(string $filename, ?string $type = null): Image
    {
        if ($type === null) {
            $type = $this->getImageTypeByFilename($filename);
        }

        if (
            $type !== 'string'
            && !$this->fileService->exists($filename)
        ) {
            throw new FileNotFound(sprintf('Bild %s existiert nicht!', $filename));
        }
        $image = match ($type) {
            'bmp' => imagecreatefromgd($filename),
            'jpg', 'jpeg' => imagecreatefromjpeg($filename),
            'gif' => imagecreatefromgif($filename),
            'png' => imagecreatefrompng($filename),
            'string' => imagecreatefromstring($filename),
        };

        if (!$image instanceof GdImage) {
            throw new LoadError(sprintf('Bild "%s" konnte nicht geladen werden!', $filename));
        }

        $Image = (new Image($image))
            ->setFilename($filename)
        ;

        if ($type === 'png') {
            $this->enableAlphaBlending($Image);
            $this->enableAlpha($Image);
        }

        return $Image;
    }

    public static function getImageTypeByFilename(string $filename): string
    {
        return preg_replace('/.*\//', '', mime_content_type($filename));
    }

    public static function getImageTypeByMimeType(string $mimeType): string
    {
        return strtolower(substr((string) strrchr($mimeType, '/'), 1));
    }

    public static function getMimeTypeByFilename(string $filename): string
    {
        return image_type_to_mime_type(
            constant('IMG_' . strtoupper(self::getImageTypeByFilename($filename))),
        );
    }

    public function output(Image $image, string $type = 'jpg'): bool
    {
        return match ($type) {
            'bmp' => imagewbmp($image->getImage(), null, 80),
            'jpg', 'jpeg' => imagejpeg($image->getImage(), null, 80),
            'gif' => imagegif($image->getImage()),
            'png' => imagepng($image->getImage()),
            default => false,
        };
    }

    public function show(Image $image, string $type = 'jpg'): bool
    {
        switch ($type) {
            case 'bmp':
                header('Content-type: image/x-ms-bmp');

                break;
            case 'jpg':
            case 'jpeg':
                header('Content-type: image/jpeg');

                break;
            case 'gif':
                header('Content-type: image/gif');

                break;
            case 'png':
                header('Content-type: image/png');

                break;
        }

        return $this->output($image, $type);
    }

    public function getString(Image $image, string $type = 'jpg'): string
    {
        ob_start();
        $this->output($image, $type);
        $string = (string) ob_get_contents();
        ob_end_clean();

        return $string;
    }

    /**
     * @throws DeleteError
     * @throws GetError
     */
    public function save(Image $image, ?string $type = null): bool
    {
        if ($type === null) {
            $type = $this->getImageTypeByFilename($image->getFilename());
        }

        try {
            $this->fileService->delete(
                $this->fileService->getDir($image->getFilename()),
                $this->fileService->getFilename($image->getFilename()),
            );
        } catch (FileNotFound) {
        }

        return match ($type) {
            'bmp' => imagewbmp($image->getImage(), $image->getFilename()),
            'jpg', 'jpeg' => imagejpeg($image->getImage(), $image->getFilename(), $image->getQuality()),
            'gif' => imagegif($image->getImage(), $image->getFilename()),
            'png' => imagepng($image->getImage(), $image->getFilename()),
            default => false,
        };
    }

    public function enableAlphaBlending(Image $image): bool
    {
        return imagealphablending($image->getImage(), true);
    }

    public function disableAlphaBlending(Image $image): bool
    {
        return imagealphablending($image->getImage(), false);
    }

    public function enableAlpha(Image $image): bool
    {
        return imagesavealpha($image->getImage(), true);
    }

    public function disableAlpha(Image $image): bool
    {
        return imagesavealpha($image->getImage(), false);
    }

    public function fill(Image $image, int $color, int $x = 0, int $y = 0): bool
    {
        return imagefill($image->getImage(), $x, $y, $color);
    }

    /**
     * @throws CreateError
     */
    public function cloneImage(Image $image): Image
    {
        $width = $this->getWidth($image);
        $height = $this->getHeight($image);

        $newImage = $this->create($width, $height);
        imagecopy($newImage->getImage(), $image->getImage(), 0, 0, 0, 0, $width, $height);

        $newImage->setFilename($image->getFilename());
        $newImage->setQuality($image->getQuality());

        return $newImage;
    }

    public function getExif(Image $image): array
    {
        try {
            return exif_read_data($image->getFilename()) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function getExifKey(Image $image, string $key): mixed
    {
        return $this->getExif($image)[$key] ?? null;
    }
}
