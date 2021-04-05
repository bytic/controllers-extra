<?php

namespace ByTIC\Controllers\Extra\Tests\Models;

use ByTIC\Controllers\Extra\Tests\AbstractTest;
use ByTIC\Controllers\Extra\Tests\Fixtures\CrudController;

/**
 * Class HasAfterActionsTest
 * @package ByTIC\Controllers\Extra\Tests\Models
 */
class HasAfterActionsTest extends AbstractTest
{
    public function test_setAfterUrlFlash()
    {
        $controller = new CrudController();
        $controller->setAfterUrlFlashTest('redirect_url', 'flash');

//        self::assertSame('redirect_url', $controller->getR);
    }
}