<?php
namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Utility\File;

class Image extends AbstractService
{
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
     * Setzt das Bild.
     *
     * @param resource $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Gibt die Breite des Bildes zurück.
     *
     * @return int Breite
     */
    public function getWidth()
    {
        return imagesx($this->getResource());
    }

    /**
     * Gibt die Höhe des Bildes zurück.
     *
     * @return int Höhe
     */
    public function getHeight()
    {
        return imagesy($this->getResource());
    }

    /**
     * Erstellt ein neues Bild.
     *
     * @param int $width Breite
     * @param int $height Höhe
     * @param bool $trueColor
     * @param bool $fillTransparent
     */
    public function create($width, $height, $trueColor = true, $fillTransparent = true)
    {
        if ($trueColor) {
            $this->setResource(imagecreatetruecolor($width, $height));
        } else {
            $this->setResource(imagecreate($width, $height));
        }

        $this->alphaBlending(true);

        if ($fillTransparent) {
            $this->fill($this->getTransparentColor());
            $this->saveAlpha(true);
        }
    }

    /**
     * Erstellt eine Farbe aus einem RGB Code.
     *
     * @param int $red Rot
     * @param int $green Grün
     * @param int $blue Blau
     * @param int $alpha
     * @return int
     */
    public function getColor($red, $green, $blue, $alpha = 0)
    {
        return imagecolorallocatealpha($this->getResource(), $red, $green, $blue, $alpha);
    }

    /**
     * Erstellt die Transparente Farbe.
     *
     * @return int
     */
    public function getTransparentColor()
    {
        return imagecolortransparent($this->getResource());
    }

    /**
     * @param int $color
     */
    public function setTransparentColor($color)
    {
        imagecolortransparent($this->getResource(), $color);
    }

    /**
     * Zerstört das Bild.
     *
     * @return bool
     */
    public function destroy()
    {
        return imagedestroy($this->getResource());
    }

    /**
     * @param string $filename Dateiname
     * @param string|null $type Dateityp
     * @throws FileNotFound
     */
    public function load($filename, $type = null)
    {
        if (is_null($type)) {
            $type = $this->getImageTypeByFilename($filename);
        }

        if(
            $type != 'string' &&
            !file_exists($filename)
        ) {
            throw new FileNotFound('Bild ' . $filename . ' existiert nicht!');
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
     * Gibt Dateityp zurück
     *
     * Holt sich den Dateityp über den Dateinamen.
     *
     * @param string $filename Dateiname
     * @return string Dateityp
     */
    static function getImageTypeByFilename($filename)
    {
        return strtolower(substr(strrchr($filename, '.'), 1));
    }

    /**
     * Gibt Dateityp zurück
     *
     * Holt sich den Dateityp über den Mime Type.
     *
     * @param string $mimeType
     * @return string Dateityp
     */
    static function getImageTypeByMimeType($mimeType)
    {
        return strtolower(substr(strrchr($mimeType, '/'), 1));
    }

    /**
     * Gibt Mime Type zurück
     *
     * Holt sich den Mime Type über den Dateinamen.
     *
     * @param string $filename Dateiname
     * @return string Mime Type
     */
    static function getMimeTypeByFilename($filename)
    {
        return image_type_to_mime_type(
            constant('IMG_' . strtoupper(self::getImageTypeByFilename($filename)))
        );
    }

    /**
     * Erzeugt die Ausgabe des aktuellen Bildes.
     *
     * @param string $type
     * @return bool
     */
    public function output($type = 'jpg')
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
     * Erzeugt eine HTTP Ausgabe des aktuellen Bildes.
     *
     * @param string $type
     * @return bool
     */
    public function show($type = 'jpg')
    {
        switch ($type) {
            case 'bmp':
                header("Content-type: image/x-ms-bmp");
                break;
            case 'jpg':
            case 'jpeg':
                header("Content-type: image/jpeg");
                break;
            case 'gif':
                header("Content-type: image/gif");
                break;
            case 'png':
                header("Content-type: image/png");
                break;
        }

        return $this->output($type);
    }

    /**
     * Gibt das Bild als String zurück
     *
     * @param string $type Dateityp
     * @return string
     */
    public function getString($type = 'jpg')
    {
        ob_start();
        $this->output($type);
        $string = ob_get_contents();
        ob_end_clean();

        return $string;
    }

    /**
     * @param string $filename Dateiname
     * @param string|null $type Dateityp
     * @return bool
     * @throws DeleteError
     */
    public function save($filename, $type = null)
    {
        if (is_null($type)) {
            $type = $this->getImageTypeByFilename($filename);
        }

        try {
            File::delete(File::getDir($filename), File::getFilename($filename));
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
     * De-/Aktiviert die Transparenz.
     *
     * @param bool $blendMode
     * @return bool
     */
    public function alphaBlending($blendMode)
    {
        return imagealphablending($this->getResource(), $blendMode);
    }

    /**
     * De-/Aktiviert das die Transparenz mitgespeichert wird (PNG).
     *
     * @param bool $saveFlag
     * @return bool
     */
    public function saveAlpha($saveFlag)
    {
        return imagesavealpha($this->getResource(), $saveFlag);
    }

    /**
     * Füllt Bild in einer Farbe.
     *
     * @param int $color
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function fill($color, $x = 0, $y = 0)
    {
        return imagefill($this->getResource(), $x, $y, $color);
    }

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