<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum\Mail;

enum Disposition: string
{
    case ATTACHMENT = 'attachment';
    case INLINE = 'inline';
}
