<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Push;

use GibsonOS\Core\Model\User;

interface PushInterface
{
    /**
     * @param User[] $users
     */
    public function send($users);
}
