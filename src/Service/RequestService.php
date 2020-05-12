<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\RequestError;

class RequestService
{
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

    public function __construct()
    {
        $queryString = (string) preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $queryParams = explode('/', $queryString);

        $this->moduleName = (string) (array_shift($queryParams) ?: 'core');
        $this->taskName = (string) (array_shift($queryParams) ?: 'index');
        $this->actionName = (string) (array_shift($queryParams) ?: 'index');

        $params = [];

        while (count($queryParams) > 1) {
            $params[(string) array_shift($queryParams)] = (string) array_shift($queryParams);
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

        if (!isset($headers[$key])) {
            throw new RequestError(sprintf('Header %s not exists!', $key));
        }

        return $_SERVER[$key];
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

    /**
     * @throws RequestError
     */
    public function isAjax(): bool
    {
        return $this->getHeader('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }
}
