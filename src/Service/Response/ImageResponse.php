<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Service\Image\ManipulateService;
use Override;

class ImageResponse implements ResponseInterface
{
    private HttpStatusCode $code = HttpStatusCode::OK;

    private int $size;

    private string $body;

    private string $contentType;

    /**
     * @throws LoadError
     * @throws FileNotFound
     * @throws CreateError
     */
    public function __construct(
        private readonly ManipulateService $manipulateService,
        private readonly string $filename,
        private readonly string $name,
        private readonly ?int $width = null,
        private readonly ?int $height = null,
    ) {
        $contentType = mime_content_type($this->filename);

        if ($contentType === false) {
            throw new LoadError('Image content type not found');
        }

        $this->contentType = $contentType;
        $image = $this->manipulateService->load($this->filename);
        $this->manipulateService->resize(
            $image,
            $this->width ?? $this->manipulateService->getWidth($image),
            $this->height ?? $this->manipulateService->getHeight($image),
        );

        $this->body = $this->manipulateService->getString($image);
        $this->size = (int) filesize($filename);
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }

    #[Override]
    public function getHeaders(): array
    {
        $name = rawurlencode($this->name);

        return [
            'Pragma' => 'public',
            'Expires' => 0,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
            'Content-Type' => $this->contentType,
            'Content-Length' => $this->size,
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => sprintf('inline; filename*=UTF-8\'\'%s filename="%s"', $name, $name),
        ];
    }

    #[Override]
    public function getBody(): string
    {
        return $this->body;
    }

    #[Override]
    public function getRequiredHeaders(): array
    {
        return [];
    }

    public function setCode(HttpStatusCode $code): ImageResponse
    {
        $this->code = $code;

        return $this;
    }
}
