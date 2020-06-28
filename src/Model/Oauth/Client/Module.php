<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Oauth\Client;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Module as ModuleModel;
use GibsonOS\Core\Model\Oauth\Client;

class Module extends AbstractModel
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var int
     */
    private $moduleId;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ModuleModel
     */
    private $module;

    public static function getTableName(): string
    {
        return 'oauth_client_module';
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): Module
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): Module
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getClient(): Client
    {
        $this->loadForeignRecord($this->client, $this->getClientId());

        return $this->client;
    }

    public function setClient(Client $client): Module
    {
        $this->client = $client;
        $this->setClientId($client->getId() ?? '');

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getModule(): ModuleModel
    {
        $this->loadForeignRecord($this->module, $this->getModuleId());

        return $this->module;
    }

    public function setModule(ModuleModel $module): Module
    {
        $this->module = $module;
        $this->setModuleId($module->getId() ?? 0);

        return $this;
    }
}
