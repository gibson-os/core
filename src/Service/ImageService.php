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
     *
     * @param FileService $file
     */
    public function __construct(FileService $file)
    {
        $this->file = $file;
    }

    /**
     * @param Image $image
     *
     * @return int
     */
    public function getWidth(Image $image): int
    {
        return (int) imagesx($image->getResource());
    }

    /**
     * @param Image $image
     *
     * @return int
     */
    public function getHeight(Image $image): int
    {
        return (int) imagesy($image->getResource());
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @throws CreateError
     *
     * @return Image
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

    /**
     * @param Image $image
     */
    public function fillTransparent(Image $image): void
    {
        $this->fill($image, $this->getTransparentColor($image));
        $this->enableAlpha($image);
    }

    /**
     * @param Image $image
     * @param int   $red
     * @param int   $green
     * @param int   $blue
     * @param int   $alpha
     *
     * @return int
     */
    public function getColor(Image $image, int $red, int $green, int $blue, int $alpha = 0): int
    {
        return (int) imagecolorallocatealpha($image->getResource(), $red, $green, $blue, $alpha);
    }

    /**
     * @param Image $image
     *
     * @return int
     */
    public function getTransparentColor(Image $image): int
    {
        return (int) imagecolortransparent($image->getResource());
    }

    /**
     * @param Image $image
     * @param int   $color
     */
    public function setTransparentColor(Image $image, int $color): void
    {
        imagecolortransparent($image->getResource(), $color);
    }

    /**
     * Zerstört das Bild.
     *
     * @param Image $image
     *
     * @return bool
     */
    public function destroy(Image $image): bool
    {
        return imagedestroy($image->getResource());
    }

    /**
     * @param string      $filename
     * @param string|null $type
     *
     * @throws FileNotFound
     * @throws LoadError
     *
     * @return Image
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
            throw new LoadError(sprintf('Bild "%s" konnte nciht geladen werden!', $filename));
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

    /**
     * @param string $filename
     *
     * @return string
     */
    public static function getImageTypeByFilename(string $filename): string
    {
        return strtolower((string) substr((string) strrchr($filename, '.'), 1));
    }

    /**
     * @param string $mimeType
     *
     * @return string
     */
    public static function getImageTypeByMimeType(string $mimeType): string
    {
        return strtolower((string) substr((string) strrchr($mimeType, '/'), 1));
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public static function getMimeTypeByFilename(string $filename): string
    {
        return image_type_to_mime_type(
            constant('IMG_' . strtoupper(self::getImageTypeByFilename($filename)))
        );
    }

    /**
     * @param Image  $image
     * @param string $type
     *
     * @return bool
     */
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

    /**
     * @param Image  $image
     * @param string $type
     *
     * @return bool
     */
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

    /**
     * @param Image  $image
     * @param string $type
     *
     * @return string
     */
    public function getString(Image $image, string $type = 'jpg'): string
    {
        ob_start();
        $this->output($image, $type);
        $string = (string) ob_get_contents();
        ob_end_clean();

        return $string;
    }

    /**
     * @param Image       $image
     * @param string|null $type
     *
     * @throws DeleteError
     * @throws GetError
     *
     * @return bool
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

    /**
     * @param Image $image
     *
     * @return bool
     */
    public function enableAlphaBlending(Image $image): bool
    {
        return imagealphablending($image->getResource(), true);
    }

    /**
     * @param Image $image
     *
     * @return bool
     */
    public function disableAlphaBlending(Image $image): bool
    {
        return imagealphablending($image->getResource(), false);
    }

    /**
     * @param Image $image
     *
     * @return bool
     */
    public function enableAlpha(Image $image): bool
    {
        return imagesavealpha($image->getResource(), true);
    }

    /**
     * @param Image $image
     *
     * @return bool
     */
    public function disableAlpha(Image $image): bool
    {
        return imagesavealpha($image->getResource(), false);
    }

    /**
     * @param Image $image
     * @param int   $color
     * @param int   $x
     * @param int   $y
     *
     * @return bool
     */
    public function fill(Image $image, int $color, int $x = 0, int $y = 0): bool
    {
        return imagefill($image->getResource(), $x, $y, $color);
    }
}
