<?php
namespace Yiisoft\Data\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\BaseDataProvider;

/**
 * @group data
 */
class BaseDataProviderTest extends TestCase
{
    public function testGenerateId()
    {
        $reflectionClass = new \ReflectionClass(BaseDataProvider::class);
        $reflectionProperty = $reflectionClass->getProperty('counter');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);

        $this->assertNull((new ConcreteDataProvider())->id);
        $this->assertNotNull((new ConcreteDataProvider())->id);
    }
}
