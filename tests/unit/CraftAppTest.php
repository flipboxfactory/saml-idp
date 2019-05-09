<?php

namespace tests\unit;

use Codeception\Test\Unit;
use craft\services\Elements;
use craft\services\Entries;
use craft\services\Users;

class CraftAppTest extends Unit
{
    /**
     * Test the component is set correctly
     */
    public function testCraftComponents()
    {

        $this->assertInstanceOf(
            Users::class,
            \Craft::$app->getUsers()
        );

        $this->assertInstanceOf(
            Entries::class,
            \Craft::$app->getEntries()
        );

        $this->assertInstanceOf(
            Elements::class,
            \Craft::$app->getElements()
        );
    }
}
