<?php

namespace ByTIC\Controllers\Extra\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTest
 * @package ByTIC\Controllers\Extra\Tests
 */
abstract class AbstractTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $object;
}
