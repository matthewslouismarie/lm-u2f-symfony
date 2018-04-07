<?php

namespace App\Tests;

use App\Service\AppConfigManager;
use App\Tests\TestCaseTemplate;
use InvalidArgumentException;
use LM\Common\Model\ArrayObject;
use LM\Common\Model\IntegerObject;
use LM\Common\Model\StringObject;

class GlobalConfigTest extends TestCaseTemplate
{
    public function testGlobalConfig()
    {
        $config = $this->getAppConfigManager();
        $config
            ->set('key0', new StringObject('value0'))
            ->set('key1', new IntegerObject(5))
            ->set('key2', new ArrayObject([new StringObject('hi'), new StringObject('yo')], StringObject::class))
        ;
        $this->assertEquals(
            'value0',
            $config->getSetting('key0', StringObject::class)->toString());
        $this->assertEquals(
            new IntegerObject(5),
            $config->getSetting('key1', IntegerObject::class));
        $this->assertEquals(
            new ArrayObject([new StringObject('hi'), new StringObject('yo')], StringObject::class),
            $config->getSetting('key2', ArrayObject::class));
        $this->expectException(InvalidArgumentException::class);
        $config->getSetting('key1', ArrayObject::class);
        
    }
}
