<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;

interface ResponseInterface
{
    public function getCode(): HttpStatusCode;

    public function getHeaders(): array;

    public function getBody(): string;

    public function getRequiredHeaders(): array;
}
