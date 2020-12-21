<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\RequestError;

class RequestService
{
    public const METHOD_GET = 'GET';

    public const METHOD_POST = 'POST';

    private $requestValues = [];

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $taskName;

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var string
     */
    private $queryString;

    public function __construct()
    {
        $queryString = (string) preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $queryParams = explode('/', mb_substr($queryString, 1));

        $this->moduleName = array_shift($queryParams) ?: 'core';
        $this->taskName = array_shift($queryParams) ?: 'index';
        $this->actionName = array_shift($queryParams) ?: 'index';
        $this->queryString = implode('/', $queryParams);

        $params = [];

        while (count($queryParams) > 1) {
            $params[(string) array_shift($queryParams)] = array_shift($queryParams);
        }

        $this->requestValues = array_merge(
            $_GET,
            $_POST,
            $params,
            $_COOKIE
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

    public function getBaseDir(): string
    {
        return preg_replace('|^(.*/).+?$|', '$1', $_SERVER['SCRIPT_NAME']);
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
     *
     * @return mixed
     */
    public function getRequestValue(string $key)
    {
        if (!isset($this->requestValues[$key])) {
            throw new RequestError(sprintf('Request key %d not exists!', $key));
        }

        return $this->requestValues[$key];
    }

    public function isAjax(): bool
    {
        try {
            return $this->getHeader('X-REQUESTED-WITH') === 'XMLHttpRequest';
        } catch (RequestError $e) {
            return false;
        }
    }

    public function getMethod(): string
    {
        return (string) $_SERVER['REQUEST_METHOD'];
    }

    public function getQueryString(): string
    {
        return $this->queryString;
    }
}
