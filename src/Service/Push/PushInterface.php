<?php
namespace GibsonOS\Core\Service\Push;

use GibsonOS\Core\Model\User;

interface PushInterface
{
    /**
     * @param User[] $users
     * @return void
     */
    public function send($users);
}