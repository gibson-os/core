<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\UnitTest\AbstractTest;

abstract class AbstractAutoCompleteTest extends AbstractTest
{
    /**
     * @return class-string
     */
    abstract protected function getAutoCompleteClassName(): string;

    public function testGetModel(): void
    {
        $autoComplete = $this->serviceManager->get($this->getAutoCompleteClassName(), AutoCompleteInterface::class);
        $model = $autoComplete->getModel();
        $modelParts = explode('.', $model);

        $this->assertEquals('GibsonOS', $modelParts[0]);
        $this->assertEquals('module', $modelParts[1]);
        unset($modelParts[0], $modelParts[1], $modelParts[2]);

        $modelPath = realpath(
            __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'js' . DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, $modelParts) . '.js'
        );

        $this->assertNotFalse($modelPath);

        $this->assertNotFalse(mb_strpos(file_get_contents($modelPath), "Ext.define('" . $model . "'"));
    }
}
