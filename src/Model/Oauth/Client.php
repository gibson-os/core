<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Oauth;

use GibsonOS\Core\Model\AbstractModel;

class Client extends AbstractModel
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $name;

    public static function getTableName(): string
    {
        return 'oauth_client';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): Client
    {
        $this->id = $id;

        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): Client
    {
        $this->secret = $secret;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Client
    {
        $this->name = $name;

        return $this;
    }
}
