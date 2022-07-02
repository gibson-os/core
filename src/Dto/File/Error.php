<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\File;

enum Error: int
{
    case UPLOAD_ERR_OK = 0;
    case UPLOAD_ERR_INI_SIZE = 1;
    case UPLOAD_ERR_FORM_SIZE = 2;
    case UPLOAD_ERR_PARTIAL = 3;
    case UPLOAD_ERR_NO_FILE = 4;
    case UPLOAD_ERR_NO_TMP_DIR = 6;
    case UPLOAD_ERR_CANT_WRITE = 7;
    case UPLOAD_ERR_EXTENSION = 8;
}
