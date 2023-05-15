<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum;

enum SmtpEncryption: string
{
    case STARTTLS = 'tls';
    case SMTPS = 'ssl';
}
