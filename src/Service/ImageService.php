<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;

class ImageService extends AbstractService
{
    /**
     * @var FileService
     */
    private $file;

    /**
     * Image constructor.
     */
    public function __construct(FileService $file)
    {
        $this->file = $file;
    }

    public function getWidth(Image $image): int
    {
        return (int) imagesx($image->getResource());
    }

    public function getHeight(Image $image): int
    {
        return (int) imagesy($image->getResource());
    }

    /**
     * @throws CreateError
     */
    public function create(int $width, int $height): Image
    {
        $image = imagecreatetruecolor($width, $height);

        if (is_bool($image)) {
            throw new CreateError('Bild jonnte nicht erstellt werden!');
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
        return (int) imagecolorallocatealpha($image->getResource(), $red, $green, $blue, $alpha);
    }

    public function getTransparentColor(Image $image): int
    {
        return (int) imagecolortransparent($image->getResource());
    }

    public function setTransparentColor(Image $image, int $color): void
    {
        imagecolortransparent($image->getResource(), $color);
    }

    /**
     * Zerstört das Bild.
     */
    public function destroy(Image $image): bool
    {
        return imagedestroy($image->getResource());
    }

    /**
     * @throws FileNotFound
     * @throws LoadError
     */
    public function load(string $filename, string $type = null): Image
    {
        if ($type === null) {
            $type = $this->getImageTypeByFilename($filename);
        }

        if (
            $type != 'string' &&
            !$this->file->exists($filename)
        ) {
            throw new FileNotFound(sprintf('Bild %s existiert nicht!', $filename));
        }

        $image = false;

        switch ($type) {
            case 'bmp':
                // @todo BMPs gehen nicht!
                $image = imagecreatefromgd($filename);

                break;
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filename);

                break;
            case 'gif':
                $image = imagecreatefromgif($filename);

                break;
            case 'png':
                $image = imagecreatefrompng($filename);

                break;
            case 'string':
                $image = imagecreatefromstring($filename);

                break;
        }

        if (!is_resource($image)) {
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
        return strtolower((string) substr((string) strrchr($filename, '.'), 1));
    }

    public static function getImageTypeByMimeType(string $mimeType): string
    {
        return strtolower((string) substr((string) strrchr($mimeType, '/'), 1));
    }

    public static function getMimeTypeByFilename(string $filename): string
    {
        return image_type_to_mime_type(
            constant('IMG_' . strtoupper(self::getImageTypeByFilename($filename)))
        );
    }

    public function output(Image $image, string $type = 'jpg'): bool
    {
        switch ($type) {
            case 'bmp':
                return imagewbmp($image->getResource(), null, 80);
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image->getResource(), null, 80);
            case 'gif':
                return imagegif($image->getResource());
            case 'png':
                return imagepng($image->getResource());
        }

        return false;
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
    public function save(Image $image, string $type = null): bool
    {
        if ($type === null) {
            $type = $this->getImageTypeByFilename($image->getFilename());
        }

        try {
            $this->file->delete(
                $this->file->getDir($image->getFilename()),
                $this->file->getFilename($image->getFilename())
            );
        } catch (FileNotFound $exception) {
        }

        switch ($type) {
            case 'bmp':
                return imagewbmp($image->getResource(), $image->getFilename());
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image->getResource(), $image->getFilename(), $image->getQuality());
            case 'gif':
                return imagegif($image->getResource(), $image->getFilename());
            case 'png':
                return imagepng($image->getResource(), $image->getFilename());
        }

        return false;
    }

    public function enableAlphaBlending(Image $image): bool
    {
        return imagealphablending($image->getResource(), true);
    }

    public function disableAlphaBlending(Image $image): bool
    {
        return imagealphablending($image->getResource(), false);
    }

    public function enableAlpha(Image $image): bool
    {
        return imagesavealpha($image->getResource(), true);
    }

    public function disableAlpha(Image $image): bool
    {
        return imagesavealpha($image->getResource(), false);
    }

    public function fill(Image $image, int $color, int $x = 0, int $y = 0): bool
    {
        return imagefill($image->getResource(), $x, $y, $color);
    }

    /**
     * @throws CreateError
     */
    public function cloneImage(Image $image): Image
    {
        $width = $this->getWidth($image);
        $height = $this->getHeight($image);

        $newImage = $this->create($width, $height);
        imagecopy($newImage->getResource(), $image->getResource(), 0, 0, 0, 0, $width, $height);

        $newImage->setFilename($image->getFilename());
        $newImage->setQuality($image->getQuality());

        return $newImage;
    }
}
