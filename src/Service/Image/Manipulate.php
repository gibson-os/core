<?php
namespace GibsonOS\Core\Service\Image;

use GibsonOS\Core\Factory\Image\Draw as DrawFactory;
use GibsonOS\Core\Factory\Image\Manipulate as ManipulateFactory;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\Image;

class Manipulate extends AbstractService
{
    /**
     * @var Image
     */
    private $image;

    /**
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Verkleinert oder Vergrößter ein Bild und behäld dabei die Proportionen.
     *
     * @param int $width Breite
     * @param int $height Höhe
     * @return bool
     */
    public function resize(int $width, int $height): bool
    {
        // Wenn das bild breiter als hoch ist
        if ($this->getImage()->getWidth() > $this->getImage()->getHeight()) {
            $newHeight = ($this->getImage()->getHeight() / $this->getImage()->getWidth()) * $width;
            $newWidth = $width;

            /* Höhe passt nicht in die übergebenen maße
               Beispiel: Bild: w=400, h=200
                         Neues Format: w=200, h=50
                         Ergibt oben: new w=200, new h=100
                         Passt nicht in die übergebene Maße
                         deswegen: new w=100; new h=50
            */
            if ($newHeight > $height) {
                $newWidth = ($width / $newHeight) * $height;
                $newHeight = $height;
            }
        } else {
            // Wenn das Bild höher als breit ist
            $newWidth = ($this->getImage()->getWidth() / $this->getImage()->getHeight()) * $height;
            $newHeight = $height;

            if ($newWidth > $width) {
                $newHeight = ($height / $newWidth) * $width;
                $newWidth = $width;
            }
        }

        $width = $newWidth;
        $height = $newHeight;

        $Image = new Image();
        $Image->create($width, $height);
        $image = $Image->getResource();

        if (
            !imagecopyresampled(
                $image,
                $this->getImage()->getResource(),
                0,
                0,
                0,
                0,
                $width,
                $height,
                $this->getImage()->getWidth(),
                $this->getImage()->getHeight()
            )
        ) {
            return false;
        }

        $this->getImage()->destroy();
        $this->getImage()->setResource($image);

        return true;
    }

    /**
     * Setzt das Bild zentriert auf die angegebene Breite und Höhe.
     * Gegenstück zu cropResized.
     *
     * @param int $width Breite
     * @param int $height Höhe
     * @return bool
     */
    public function resizeCentered($width, $height)
    {
        if (!$this->resize($width, $height)) {
            return false;
        }

        if ($this->getImage()->getWidth() < $width) {
            $this->horizontalCentered($width, $height);
        } else if ($this->getImage()->getHeight() < $height) {
            $this->verticalCentered($width, $height);
        }

        return true;
    }

    /**
     * Setzt das Bild vertikal zentriert auf die angegebene Breite und Höhe
     *
     * @param int $width Breite
     * @param int $height Höhe
     */
    public function verticalCentered($width, $height)
    {
        $manipulate = ManipulateFactory::create($width, $height);
        $manipulate->copy(
            $this->getImage(),
            0,
            ($height - $this->getImage()->getHeight()) / 2
        );
        $this->getImage()->setResource($manipulate->getImage()->getResource());
    }

    /**
     * Setzt das Bild horizontal zentriert auf die angegebene Breite und Höhe.
     *
     * @param int $width Breite
     * @param int $height Höhe
     */
    public function horizontalCentered($width, $height)
    {
        $manipulate = ManipulateFactory::create($width, $height);
        $manipulate->copy(
            $this->getImage(),
            ($width - $this->getImage()->getWidth()) / 2
        );
        $this->getImage()->setResource($manipulate->getImage()->getResource());
    }

    /**
     * Schneidet das Bild zu.
     *
     * @param int $width Breite
     * @param int $height Höhe
     * @param int|bool $startX
     * @param int|bool $startY
     * @param int|bool $color
     * @return bool
     */
    public function crop($width, $height, $startX = false, $startY = false, $color = false)
    {
        if ($startX === false) {
            $startX = ($this->getImage()->getWidth() - $width) / 2;
        }
        if ($startY === false) {
            $startY = ($this->getImage()->getHeight() - $height) / 2;
        }

        $draw = DrawFactory::create($width, $height);

        if ($color) {
            $draw->filledRectangle($color);
        }

        $image = $draw->getImage()->getResource();
        $return = imagecopyresampled(
            $image,
            $this->getImage()->getResource(),
            0,
            0,
            $startX,
            $startY,
            $width,
            $height,
            $width,
            $height
        );

        $this->getImage()->destroy();
        $this->getImage()->setResource($image);

        return $return;
    }

    /**
     * Bild vergrößern oder verkleinern und alles was übersteht abschneiden.
     * Gegenstück zu resizeCentered.
     *
     * @param int $width Breite
     * @param int $height Höhe
     * @param int|bool $startX
     * @param int|bool $startY
     * @return bool
     */
    public function cropResized($width, $height, $startX = false, $startY = false)
    {
        if ($this->getImage()->getWidth() > $this->getImage()->getHeight()) {
            $newWidth = ($this->getImage()->getWidth() / $this->getImage()->getHeight()) * $height;
            $newHeight = $height;

            if ($newWidth < $width) {
                $newHeight = ($height / $newWidth) * $width;
                $newWidth = $width;
            }
        } else {
            $newHeight = ($this->getImage()->getHeight() / $this->getImage()->getWidth()) * $width;
            $newWidth = $width;

            if ($newHeight < $height) {
                $newWidth = ($width / $newHeight) * $height;
                $newHeight = $height;
            }
        }

        if ($this->resize($newWidth, $newHeight)) {
            if ($this->crop($width, $height, $startX, $startY)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kopiert ein Bild in das aktuelle Bild. Kann dabei auch nur einen Tiel kopieren.
     *
     * @param Image $image Bild
     * @param int $destX Horizontale Position in die das Bild kopiert wird
     * @param int $destY Vertikale Position in die das Bild kopiert wird
     * @param int $srcX Horizontale Startposition des zu kopierenden Bildes
     * @param int $srcY Vertikale Startosition des zu kopierenden Bildes
     * @param int $srcWidth Breite des zu kopierenden Bildes
     * @param int $srcHeight Höhe des zu kopierenden Bildes
     * @param int $dstWidth Breite des Bildes in das kopiert wird
     * @param int $dstHeight Höhe des Bildes in das kopiert wird
     * @return bool
     */
    public function copy(
        $image,
        $destX = 0,
        $destY = 0,
        $srcX = 0,
        $srcY = 0,
        $srcWidth = -1,
        $srcHeight = -1,
        $dstWidth = -1,
        $dstHeight = -1
    ) {
        if ($srcWidth == -1) {
            $srcWidth = $image->getWidth();
        }

        if ($srcHeight == -1) {
            $srcHeight = $image->getHeight();
        }

        if ($dstWidth == -1) {
            $dstWidth = $image->getWidth();
        }

        if ($dstHeight == -1) {
            $dstHeight = $image->getHeight();
        }

        return imagecopyresampled(
            $this->getImage()->getResource(),
            $image->getResource(),
            $destX,
            $destY,
            $srcX,
            $srcY,
            $dstWidth,
            $dstHeight,
            $srcWidth,
            $srcHeight
        );
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    public function __clone()
    {
        $this->image = clone $this->image;
    }
}