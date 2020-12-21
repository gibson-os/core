<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Meins
 * Date: 12.09.2018
 * Time: 22:53.
 */

namespace GibsonOS\Core\Service\Push;

use GibsonOS\Core\Model\User;

class C2dmService implements PushInterface
{
    /**
     * @param User[] $users
     */
    public function send(array $users): void
    {
        // TODO: Implement send() method.
    }
}
