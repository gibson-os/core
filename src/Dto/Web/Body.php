<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Web;

use GibsonOS\Core\Exception\WebException;

class Body
{
    /**
     * @var resource|null
     */
    private $resource;

    private int $length = 0;

    /**
     * @return resource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param resource $resource
     */
    public function setResource($resource, int $length): Body
    {
        $this->resource = $resource;
        $this->length = $length;

        return $this;
    }

    /**
     * @throws WebException
     */
    public function getContent(): string
    {
        if ($this->resource === null || $this->length === 0) {
            throw new WebException('No body!');
        }

        if ($this->resource === null) {
            throw new WebException('No length!');
        }

        $body = fread($this->resource, $this->length);

        if ($body === false) {
            throw new WebException('No content!');
        }

        return $body;
    }

    /**
     * @throws WebException
     *
     * @return $this
     */
    public function setContent(string $content, int $length): Body
    {
        $this->resource = fopen('php://memory', 'r+');

        if (fwrite($this->resource, $content, $length) === false) {
            throw new WebException('Content write error!');
        }

        return $this;
    }
}
