<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\ResponseError;

class ResourceResponse implements ResponseInterface
{
    private string $type = 'application/octet-stream';

    private HttpStatusCode $code = HttpStatusCode::OK;

    private ?string $disposition = 'attachment';

    private int $partLength = 8192;

    private bool $hasRange = false;

    private int $startSize = 0;

    private int $endSize;

    public function __construct(
        protected $resource,
        private readonly string $filename,
        private readonly int $size,
    ) {
        $this->endSize = $this->size;

    }

    public function setRange(int $startSize, ?int $endSize): ResourceResponse
    {
        $this->hasRange = true;

        if ($endSize === null) {
            $endSize = $this->size - 1;
        }

        $this->endSize = $endSize;
        $this->startSize = $startSize;

        $this->setCode(HttpStatusCode::PARTIAL_CONTENT);

        return $this;
    }

    public function getCode(): HttpStatusCode
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
            $filename = mb_substr($this->filename, (mb_strrpos($this->filename, DIRECTORY_SEPARATOR) ?: -1) + 1);
            $headers['Content-Disposition'] =
                $this->disposition . '; ' .
                'filename*=UTF-8\'\'' . rawurlencode($filename) . ' ' .
                'filename="' . rawurlencode($filename) . '"'
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

        if (!is_resource($this->resource)) {
            throw new ResponseError(sprintf('Can not open file %s!', $this->filename));
        }

        fseek($this->resource, $this->startSize);

        while (true) {
            $position = ftell($this->resource);

            if ($position >= $this->endSize) {
                break;
            }

            echo fread($this->resource, $this->partLength);
            ob_flush();
            flush();
        }

        ob_end_flush();

        return '';
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }

    public function setType(string $type): ResourceResponse
    {
        $this->type = $type;

        return $this;
    }

    public function setCode(HttpStatusCode $code): ResourceResponse
    {
        $this->code = $code;

        return $this;
    }

    public function setDisposition(?string $disposition): ResourceResponse
    {
        $this->disposition = $disposition;

        return $this;
    }

    public function setPartLength(int $partLength): ResourceResponse
    {
        $this->partLength = $partLength;

        return $this;
    }
}
