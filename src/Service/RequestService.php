<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Utility\JsonUtility;

class RequestService
{
    public const METHOD_GET = 'GET';

    public const METHOD_POST = 'POST';

    private array $requestValues;

    private string $moduleName;

    private string $taskName;

    private string $actionName;

    private string $queryString;

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function __construct()
    {
        $queryString = (string) preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI'] ?? '');
        $queryParams = array_map(
            fn ($value) => rawurldecode($value),
            explode('/', mb_substr($queryString, 1)),
        );

        $this->moduleName = array_shift($queryParams) ?: 'core';
        $this->taskName = array_shift($queryParams) ?: 'index';
        $this->actionName = array_shift($queryParams) ?: '';
        $this->queryString = implode('/', $queryParams);

        $params = [];

        while (count($queryParams) > 1) {
            $params[array_shift($queryParams) ?: ''] = array_shift($queryParams);
        }

        $files = [];

        /** @var array $file */
        foreach ($_FILES as $key => $file) {
            $keyFiles = [];

            foreach ($file as $property => $values) {
                if (!is_array($values)) {
                    $keyFiles = $file;

                    break;
                }

                foreach ($values as $index => $value) {
                    $keyFiles[$index][$property] = $value;
                }
            }

            $files[$key] = $keyFiles;
        }

        $this->requestValues = array_merge(
            $_GET,
            $_POST,
            $params,
            $this->getJsonBody(),
            $_COOKIE,
            $files,
        );
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getTaskName(): string
    {
        return $this->taskName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getBaseDir(): string
    {
        return preg_replace('|^(.*/).+?$|', '$1', $_SERVER['SCRIPT_NAME'] ?? '')
            ?? throw new RequestError('Base dir not set!')
        ;
    }

    /**
     * @throws RequestError
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        $headers = getallheaders();

        if (!is_array($headers)) {
            throw new RequestError('Headers not set!');
        }

        return $headers;
    }

    /**
     * @throws RequestError
     */
    public function getHeader(string $key): string
    {
        $headers = $this->getHeaders();
        /** @psalm-suppress InvalidScalarArgument */
        $headers = array_combine(array_map('mb_strtolower', array_keys($headers)), $headers) ?: [];
        $key = mb_strtolower($key);

        if (!isset($headers[$key])) {
            throw new RequestError(sprintf('Header %s not exists!', $key));
        }

        return $headers[$key];
    }

    public function getRequestValues(): array
    {
        return $this->requestValues;
    }

    /**
     * @throws RequestError
     */
    public function getRequestValue(string $key): mixed
    {
        if (!isset($this->requestValues[$key])) {
            throw new RequestError(sprintf('Request key "%s" not exists!', $key));
        }

        return $this->requestValues[$key];
    }

    public function isAjax(): bool
    {
        try {
            return $this->getHeader('X-REQUESTED-WITH') === 'XMLHttpRequest';
        } catch (RequestError) {
            return false;
        }
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? '';
    }

    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    private function getJsonBody(): array
    {
        if (mb_strtolower($_SERVER['CONTENT_TYPE'] ?? '') === 'application/json') {

            $body = file_get_contents('php://input');

            if ($body === '' || $body === false) {
                return [];
            }

            $parameters = JsonUtility::decode($body);

            if (!is_array($parameters)) {
                return [];
            }

            return $parameters;
        }

        return [];
    }
}
