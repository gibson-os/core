<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;

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
     * @var resource Bild
     */
    private $resource;

    /**
     * Gibt das Bild zurück.
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param resource|bool $resource
     *
     * @throws SetError
     */
    public function setResource($resource): void
    {
        if (!is_resource($resource)) {
            throw new SetError('Bild ist keine Ressource!');
        }

        $this->resource = $resource;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return (int) imagesx($this->getResource());
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return (int) imagesy($this->getResource());
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @throws SetError
     */
    public function create(int $width, int $height): void
    {
        $this->setResource(imagecreatetruecolor($width, $height));
        $this->alphaBlending(true);
    }

    public function fillTransparent(): void
    {
        $this->fill($this->getTransparentColor());
        $this->saveAlpha(true);
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     *
     * @return int
     */
    public function getColor(int $red, int $green, int $blue, int $alpha = 0): int
    {
        return (int) imagecolorallocatealpha($this->getResource(), $red, $green, $blue, $alpha);
    }

    /**
     * @return int
     */
    public function getTransparentColor(): int
    {
        return (int) imagecolortransparent($this->getResource());
    }

    /**
     * @param int $color
     */
    public function setTransparentColor(int $color): void
    {
        imagecolortransparent($this->getResource(), $color);
    }

    /**
     * Zerstört das Bild.
     *
     * @return bool
     */
    public function destroy(): bool
    {
        return imagedestroy($this->getResource());
    }

    /**
     * @param string      $filename
     * @param string|null $type
     *
     * @throws FileNotFound
     * @throws SetError
     */
    public function load(string $filename, string $type = null): void
    {
        if ($type === null) {
            $type = $this->getImageTypeByFilename($filename);
        }

        if (
            $type != 'string' &&
            !file_exists($filename)
        ) {
            throw new FileNotFound(sprintf('Bild %s existiert nicht!', $filename));
        }

        switch ($type) {
            case 'bmp':
                // @todo BMPs gehen nicht!
                $this->setResource(imagecreatefromgd($filename));

                break;
            case 'jpg':
            case 'jpeg':
                $this->setResource(imagecreatefromjpeg($filename));

                break;
            case 'gif':
                $this->setResource(imagecreatefromgif($filename));

                break;
            case 'png':
                $this->setResource(imagecreatefrompng($filename));
                $this->alphaBlending(true);
                $this->saveAlpha(true);

                break;
            case 'string':
                $this->setResource(imagecreatefromstring($filename));

                break;
        }
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
     * @param string $type
     *
     * @return bool
     */
    public function output(string $type = 'jpg'): bool
    {
        switch ($type) {
            case 'bmp':
                return imagewbmp($this->getResource(), null, 80);
            case 'jpg':
            case 'jpeg':
                return imagejpeg($this->getResource(), null, 80);
            case 'gif':
                return imagegif($this->getResource());
            case 'png':
                return imagepng($this->getResource());
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function show(string $type = 'jpg'): bool
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

        return $this->output($type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getString(string $type = 'jpg'): string
    {
        ob_start();
        $this->output($type);
        $string = (string) ob_get_contents();
        ob_end_clean();

        return $string;
    }

    /**
     * @param string      $filename
     * @param string|null $type
     *
     * @throws DeleteError
     * @throws GetError
     *
     * @return bool
     */
    public function save(string $filename, string $type = null): bool
    {
        if ($type === null) {
            $type = $this->getImageTypeByFilename($filename);
        }

        try {
            $this->file->delete($this->file->getDir($filename), $this->file->getFilename($filename));
        } catch (FileNotFound $exception) {
        }

        switch ($type) {
            case 'bmp':
                return imagewbmp($this->getResource(), $filename);
            case 'jpg':
            case 'jpeg':
                return imagejpeg($this->getResource(), $filename, 80);
            case 'gif':
                return imagegif($this->getResource(), $filename);
            case 'png':
                return imagepng($this->getResource(), $filename);
        }

        return false;
    }

    /**
     * @param bool $blendMode
     *
     * @return bool
     */
    public function alphaBlending(bool $blendMode): bool
    {
        return imagealphablending($this->getResource(), $blendMode);
    }

    /**
     * @param bool $saveFlag
     *
     * @return bool
     */
    public function saveAlpha(bool $saveFlag): bool
    {
        return imagesavealpha($this->getResource(), $saveFlag);
    }

    /**
     * @param int $color
     * @param int $x
     * @param int $y
     *
     * @return bool
     */
    public function fill(int $color, int $x = 0, int $y = 0): bool
    {
        return imagefill($this->getResource(), $x, $y, $color);
    }

    /**
     * @throws SetError
     */
    public function __clone()
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        $trans = $this->getTransparentColor();
        $oldImage = $this->getResource();

        if (imageistruecolor($this->getResource())) {
            $this->setResource(imagecreatetruecolor($w, $h));
            $this->alphaBlending(false);
            $this->saveAlpha(true);
        } else {
            $this->setResource(imagecreate($w, $h));

            if ($trans >= 0) {
                $rgb = imagecolorsforindex($this->getResource(), $trans);

                $this->saveAlpha(true);
                $transIndex = $this->getColor($rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha']);
                $this->fill($transIndex, 0, 0);
            }
        }

        imagecopy($this->getResource(), $oldImage, 0, 0, 0, 0, $w, $h);
    }
}
