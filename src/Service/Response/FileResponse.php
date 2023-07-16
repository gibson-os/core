<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\RequestService;

class FileResponse extends ResourceResponse
{
    public function __construct(RequestService $requestService, string $filename)
    {
        parent::__construct(fopen($filename, 'rb'), $filename, (int) filesize($filename));

        try {
            try {
                $range = $requestService->getHeader('Range');
            } catch (RequestError) {
                $range = $requestService->getHeader('HTTP_RANGE');
            }

            $ranges = explode('-', substr($range, 6));
            $this->setRange((int) $ranges[0], $ranges[1] === null ? null : (int) $ranges[1]);
        } catch (RequestError) {
            // Range not exists
        }
    }

    public function getBody(): string
    {
        $body = parent::getBody();
        fclose($this->resource);

        return $body;
    }
}
