<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Exception\ResponseError;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\StatusCode;

class FileResponse implements ResponseInterface
{
    private RequestService $requestService;

    private string $filename;

    private string $type = 'application/octet-stream';

    private int $code = StatusCode::OK;

    private ?string $disposition = 'attachment';

    private int $partLength = 8192;

    private bool $hasRange = false;

    private int $startSize = 0;

    private int $endSize;

    private int $size;

    public function __construct(RequestService $requestService, string $filename)
    {
        $this->requestService = $requestService;
        $this->filename = $filename;
        $this->size = (int) filesize($filename);
        $this->endSize = $this->size;

        try {
            try {
                $range = $this->requestService->getHeader('Range');
            } catch (RequestError $e) {
                $range = $this->requestService->getHeader('HTTP_RANGE');
            }

            $ranges = explode('-', substr($range, 6));
            $this->hasRange = true;

            if (!$ranges[1]) {
                $ranges[1] = $this->size - 1;
            }

            $this->endSize = (int) $ranges[1];
            $this->startSize = (int) $ranges[0];

            $this->setCode(StatusCode::PARTIAL_CONTENT);
        } catch (RequestError $e) {
            // Range not exists
        }
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getHeaders(): array
    {
        $headers = [
            'Pragma' => 'public',
            'Expires' => 0,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
            'Content-Type' => $this->type,
            'Content-Length' => $this->hasRange ? $this->endSize - $this->startSize + 1 : $this->size,
            'Content-Transfer-Encoding' => 'binary',
        ];

        if (!empty($this->disposition)) {
            $headers['Content-Disposition'] =
                $this->disposition . '; ' .
                'filename*=UTF-8\'\'' . rawurlencode($this->filename) . ' ' .
                'filename="' . rawurlencode($this->filename) . '"'
            ;
        }

        if ($this->hasRange) {
            $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $this->startSize, $this->endSize, $this->size);
        }

        return $headers;
    }

    /**
     * @throws ResponseError
     */
    public function getBody(): string
    {
        ini_set('max_execution_time', '0');
        $file = fopen($this->filename, 'rb');

        if (!is_resource($file)) {
            throw new ResponseError(sprintf('Can not open file %s!', $this->filename));
        }

        fseek($file, $this->startSize);

        while (true) {
            $position = ftell($file);

            if ($position >= $this->endSize) {
                break;
            }

            echo fread($file, $this->partLength);
            ob_flush();
            flush();
        }

        fclose($file);
        ob_end_flush();

        return '';
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }

    public function setType(string $type): FileResponse
    {
        $this->type = $type;

        return $this;
    }

    public function setCode(int $code): FileResponse
    {
        $this->code = $code;

        return $this;
    }

    public function setDisposition(?string $disposition): FileResponse
    {
        $this->disposition = $disposition;

        return $this;
    }

    public function setPartLength(int $partLength): FileResponse
    {
        $this->partLength = $partLength;

        return $this;
    }
}
