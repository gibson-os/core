<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Oauth;

use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;

class Token extends AbstractModel
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var DateTimeInterface
     */
    private $expireDate;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var User
     */
    private $user;

    public static function getTableName(): string
    {
        return 'oauth_token';
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): Token
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Token
    {
        $this->userId = $userId;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): Token
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): Token
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): Token
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpireDate(): DateTimeInterface
    {
        return $this->expireDate;
    }

    public function setExpireDate(DateTimeInterface $expireDate): Token
    {
        $this->expireDate = $expireDate;

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

    public function setClient(Client $client): Token
    {
        $this->client = $client;
        $this->setClientId($client->getId() ?? '');

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    public function setUser(User $user): Token
    {
        $this->user = $user;
        $this->setUserId($user->getId() ?? 0);

        return $this;
    }
}
