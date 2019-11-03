<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\Push;

use GibsonOS\Core\Service\Push\C2DmService;

class C2dm
{
    public static function create(): C2dmService
    {
        return new C2dmService();
    }
}
