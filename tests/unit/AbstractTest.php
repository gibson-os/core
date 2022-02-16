<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest;

use Codeception\Test\Unit;
use GibsonOS\Core\Manager\ServiceManager;

class AbstractTest extends Unit
{
    /**
     * @var ServiceManager
     */
    protected $serviceManagerService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->serviceManagerService = new ServiceManager();
        putenv('TIMEZONE=Europe/Berlin');
        putenv('MYSQL_HOST=gos_mysql');
        putenv('MYSQL_DATABASE=gibson_os_test');
        putenv('MYSQL_USER=root');
        putenv('MYSQL_PASS=67yhnkMR');
    }
}
