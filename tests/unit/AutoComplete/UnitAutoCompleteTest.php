<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete;

use Codeception\Test\Unit;
use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

abstract class UnitAutoCompleteTest extends Unit
{
    use ModelManagerTrait;

    protected AutoCompleteInterface $autoComplete;

    abstract protected function getAutoComplete(): AutoCompleteInterface;

    abstract public function testGetByNamePart(): void;

    abstract public function testGetById(): void;

    abstract protected function getValueField(): string;

    abstract protected function getDisplayField(): string;

    protected function _before()
    {
        $this->loadModelManager();
        $this->autoComplete = $this->getAutoComplete();
    }

    public function testGetModel(): void
    {
        $model = $this->autoComplete->getModel();
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

        $this->assertNotFalse($modelPath, sprintf('JS model "%s" not found', $model));

        $this->assertNotFalse(mb_strpos(file_get_contents($modelPath), "Ext.define('" . $model . "'"));
    }

    public function testGetValueField(): void
    {
        $this->assertEquals($this->getValueField(), $this->autoComplete->getValueField());
    }

    public function testGetDisplayField(): void
    {
        $this->assertEquals($this->getDisplayField(), $this->autoComplete->getDisplayField());
    }
}
