<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Service\TwigService;
use Override;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TwigResponse implements ResponseInterface
{
    private array $variables = [];

    public function __construct(
        private readonly TwigService $twigService,
        private readonly string $template,
        private readonly HttpStatusCode $code = HttpStatusCode::OK,
        private readonly array $headers = [],
    ) {
    }

    #[Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[Override]
    public function getBody(): string
    {
        return $this->twigService->getTwig()->render($this->template, $this->variables);
    }

    #[Override]
    public function getRequiredHeaders(): array
    {
        return [];
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }

    public function setVariable(string $name, array $value): TwigResponse
    {
        $this->variables[$name] = $value;

        return $this;
    }

    public function setVariables(array $variables): TwigResponse
    {
        $this->variables = $variables;

        return $this;
    }
}
