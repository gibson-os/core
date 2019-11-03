<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File;

use GibsonOS\Core\Exception\GetError;

class TypeService
{
    public const TYPE_JPG = 'jpg';

    public const TYPE_JPEG = 'jpeg';

    public const TYPE_GIF = 'gif';

    public const TYPE_PNG = 'png';

    public const TYPE_BMP = 'bmp';

    public const TYPE_AVI = 'avi';

    public const TYPE_ASF = 'asf';

    public const TYPE_MKV = 'mkv';

    public const TYPE_MPG = 'mpg';

    public const TYPE_MPEG = 'mpeg';

    public const TYPE_OGG = 'ogg';

    public const TYPE_FLA = 'fla';

    public const TYPE_SWF = 'swf';

    public const TYPE_FLV = 'flv';

    public const TYPE_F4V = 'f4v';

    public const TYPE_F4P = 'f4p';

    public const TYPE_MP4 = 'mp4';

    public const TYPE_MOV = 'mov';

    public const TYPE_3GP = '3gp';

    public const TYPE_WMV = 'wmv';

    public const TYPE_RM = 'rm';

    public const TYPE_WEBM = 'webm';

    public const TYPE_WAV = 'wav';

    public const TYPE_MP3 = 'mp3';

    public const TYPE_M4A = 'm4a';

    public const TYPE_F4A = 'f4a';

    public const TYPE_F4B = 'f4b';

    public const TYPE_AIFF = 'aiff';

    public const TYPE_PDF = 'pdf';

    public const TYPE_ODT = 'odt';

    public const TYPE_DOC = 'doc';

    public const TYPE_DOCX = 'docx';

    public const TYPE_ODS = 'ods';

    public const TYPE_XLS = 'xls';

    public const TYPE_OPD = 'opd';

    public const TYPE_PPT = 'ppt';

    public const TYPE_PPTX = 'pptx';

    public const TYPE_ODG = 'odg';

    public const TYPE_RAR = 'rar';

    public const TYPE_ZIP = 'zip';

    public const TYPE_EXE = 'exe';

    public const TYPE_BIN = 'bin';

    public const TYPE_ISO = 'iso';

    public const TYPE_TXT = 'txt';

    public const TYPE_JS = 'js';

    public const TYPE_PHP = 'php';

    public const TYPE_HTML = 'html';

    public const TYPE_HTM = 'htm';

    public const TYPE_CATEGORY_IMAGE = 1;

    public const TYPE_CATEGORY_VIDEO = 2;

    public const TYPE_CATEGORY_PDF = 3;

    public const TYPE_CATEGORY_AUDIO = 4;

    public const TYPE_CATEGORY_OFFICE = 5;

    public const TYPE_CATEGORY_ARCHIVE = 6;

    public const TYPE_CATEGORY_BINARY = 7;

    public const TYPE_CATEGORY_TEXT = 8;

    private const TYPE_CATEGORIES = [
        self::TYPE_JPG => self::TYPE_CATEGORY_IMAGE,
        self::TYPE_JPEG => self::TYPE_CATEGORY_IMAGE,
        self::TYPE_GIF => self::TYPE_CATEGORY_IMAGE,
        self::TYPE_PNG => self::TYPE_CATEGORY_IMAGE,
        self::TYPE_BMP => self::TYPE_CATEGORY_IMAGE,
        self::TYPE_ASF => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_AVI => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_MKV => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_MPG => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_MPEG => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_OGG => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_FLA => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_SWF => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_FLV => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_F4V => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_F4P => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_MP4 => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_MOV => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_3GP => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_WMV => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_RM => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_WEBM => self::TYPE_CATEGORY_VIDEO,
        self::TYPE_WAV => self::TYPE_CATEGORY_AUDIO,
        self::TYPE_MP3 => self::TYPE_CATEGORY_AUDIO,
        self::TYPE_M4A => self::TYPE_CATEGORY_AUDIO,
        self::TYPE_F4A => self::TYPE_CATEGORY_AUDIO,
        self::TYPE_F4B => self::TYPE_CATEGORY_AUDIO,
        self::TYPE_AIFF => self::TYPE_CATEGORY_AUDIO,
        self::TYPE_PDF => self::TYPE_CATEGORY_PDF,
        self::TYPE_ODT => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_DOC => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_DOCX => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_ODS => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_XLS => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_OPD => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_PPT => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_PPTX => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_ODG => self::TYPE_CATEGORY_OFFICE,
        self::TYPE_RAR => self::TYPE_CATEGORY_ARCHIVE,
        self::TYPE_ZIP => self::TYPE_CATEGORY_ARCHIVE,
        self::TYPE_EXE => self::TYPE_CATEGORY_BINARY,
        self::TYPE_BIN => self::TYPE_CATEGORY_BINARY,
        self::TYPE_ISO => self::TYPE_CATEGORY_BINARY,
        self::TYPE_TXT => self::TYPE_CATEGORY_TEXT,
        self::TYPE_JS => self::TYPE_CATEGORY_TEXT,
        self::TYPE_PHP => self::TYPE_CATEGORY_TEXT,
        self::TYPE_HTML => self::TYPE_CATEGORY_TEXT,
        self::TYPE_HTM => self::TYPE_CATEGORY_TEXT,
    ];

    private const THUMB_TYPES = [
        self::TYPE_CATEGORY_IMAGE,
        self::TYPE_CATEGORY_VIDEO,
        self::TYPE_CATEGORY_PDF,
        self::TYPE_CATEGORY_OFFICE,
    ];

    /**
     * @param string $path
     *
     * @return string|null
     */
    public function getFileType(string $path): ?string
    {
        if (mb_strrpos($path, '.') === false) {
            return null;
        }

        return mb_substr($path, mb_strrpos($path, '.') + 1);
    }

    /**
     * @param string $filename
     *
     * @throws GetError
     *
     * @return string
     */
    public function getContentType(string $filename): string
    {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!is_resource($fileInfo)) {
            throw new GetError(sprintf('Content Type für %s konnte nicht ermittelt werden!', $filename));
        }

        return (string) finfo_file($fileInfo, $filename);
    }

    /**
     * @param string $path
     *
     * @return int|null
     */
    public function getCategory(string $path): ?int
    {
        $type = $this->getFileType(strtolower($path));

        if (
            $type === null ||
            !array_key_exists($type, self::TYPE_CATEGORIES)
        ) {
            return null;
        }

        return self::TYPE_CATEGORIES[$type];
    }

    /**
     * @param string $path
     *
     * @return int|null
     *
     * @deprecated
     */
    public function getThumbType(string $path): ?int
    {
        $category = $this->getCategory($path);

        if (
            $category === null ||
            !in_array($category, self::THUMB_TYPES)
        ) {
            return null;
        }

        return self::THUMB_TYPES[$category];
    }
}
