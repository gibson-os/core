<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Image as ImageDto;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;

class Image extends AbstractService
{
    /**
     * @var File
     */
    private $file;

    /**
     * Image constructor.
     *
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @param ImageDto $image
     *
     * @return int
     */
    public function getWidth(ImageDto $image): int
    {
        return (int) imagesx($image->getResource());
    }

    /**
     * @param ImageDto $image
     *
     * @return int
     */
    public function getHeight(ImageDto $image): int
    {
        return (int) imagesy($image->getResource());
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @throws CreateError
     *
     * @return ImageDto
     */
    public function create(int $width, int $height): ImageDto
    {
        $image = imagecreatetruecolor($width, $height);

        if (is_bool($image)) {
            throw new CreateError('Bild jonnte nicht erstellt werden!');
        }

        $image = new ImageDto($image);
        $this->enableAlphaBlending($image);

        return $image;
    }

    /**
     * @param ImageDto $image
     */
    public function fillTransparent(ImageDto $image): void
    {
        $this->fill($image, $this->getTransparentColor($image));
        $this->enableAlpha($image);
    }

    /**
     * @param ImageDto $image
     * @param int      $red
     * @param int      $green
     * @param int      $blue
     * @param int      $alpha
     *
     * @return int
     */
    public function getColor(ImageDto $image, int $red, int $green, int $blue, int $alpha = 0): int
    {
        return (int) imagecolorallocatealpha($image->getResource(), $red, $green, $blue, $alpha);
    }

    /**
     * @param ImageDto $image
     *
     * @return int
     */
    public function getTransparentColor(ImageDto $image): int
    {
        return (int) imagecolortransparent($image->getResource());
    }

    /**
     * @param ImageDto $image
     * @param int      $color
     */
    public function setTransparentColor(ImageDto $image, int $color): void
    {
        imagecolortransparent($image->getResource(), $color);
    }

    /**
     * Zerstört das Bild.
     *
     * @param ImageDto $image
     *
     * @return bool
     */
    public function destroy(ImageDto $image): bool
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
     * @return ImageDto
     */
    public function load(string $filename, string $type = null): ImageDto
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

        $imageDto = (new ImageDto($image))
            ->setFilename($filename)
        ;

        if ($type === 'png') {
            $this->enableAlphaBlending($imageDto);
            $this->enableAlpha($imageDto);
        }

        return $imageDto;
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
     * @param ImageDto $image
     * @param string   $type
     *
     * @return bool
     */
    public function output(ImageDto $image, string $type = 'jpg'): bool
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
     * @param ImageDto $image
     * @param string   $type
     *
     * @return bool
     */
    public function show(ImageDto $image, string $type = 'jpg'): bool
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
     * @param ImageDto $image
     * @param string   $type
     *
     * @return string
     */
    public function getString(ImageDto $image, string $type = 'jpg'): string
    {
        ob_start();
        $this->output($image, $type);
        $string = (string) ob_get_contents();
        ob_end_clean();

        return $string;
    }

    /**
     * @param ImageDto    $image
     * @param string|null $type
     *
     * @throws DeleteError
     * @throws GetError
     *
     * @return bool
     */
    public function save(ImageDto $image, string $type = null): bool
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
     * @param ImageDto $image
     *
     * @return bool
     */
    public function enableAlphaBlending(ImageDto $image): bool
    {
        return imagealphablending($image->getResource(), true);
    }

    /**
     * @param ImageDto $image
     *
     * @return bool
     */
    public function disableAlphaBlending(ImageDto $image): bool
    {
        return imagealphablending($image->getResource(), false);
    }

    /**
     * @param ImageDto $image
     *
     * @return bool
     */
    public function enableAlpha(ImageDto $image): bool
    {
        return imagesavealpha($image->getResource(), true);
    }

    /**
     * @param ImageDto $image
     *
     * @return bool
     */
    public function disableAlpha(ImageDto $image): bool
    {
        return imagesavealpha($image->getResource(), false);
    }

    /**
     * @param ImageDto $image
     * @param int      $color
     * @param int      $x
     * @param int      $y
     *
     * @return bool
     */
    public function fill(ImageDto $image, int $color, int $x = 0, int $y = 0): bool
    {
        return imagefill($image->getResource(), $x, $y, $color);
    }
}
