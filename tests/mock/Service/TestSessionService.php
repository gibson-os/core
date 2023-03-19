<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Service;

use GibsonOS\Core\Service\SessionService;

class TestSessionService extends SessionService
{
    public function __construct()
    {
    }

    public function set(string $key, $value): SessionService
    {
        $this->data[$key] = $value;
        $_SESSION[$key] = $value;

        return $this;
    }

    public function unset(string $key): SessionService
    {
        if (!isset($this->data[$key])) {
            throw new \OutOfBoundsException(sprintf('Session key $%s not exists!', $key));
        }

        unset($this->data[$key], $_SESSION[$key]);

        return $this;
    }
}
