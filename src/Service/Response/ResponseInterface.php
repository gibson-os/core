<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

interface ResponseInterface
{
    public function getCode(): int;

    public function getHeaders(): array;

    public function getBody(): string;

    public function getRequiredHeaders(): array;
}
