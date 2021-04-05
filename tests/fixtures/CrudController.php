<?php

namespace ByTIC\Controllers\Extra\Tests\Fixtures;

use ByTIC\Controllers\Behaviors\CrudModels;
use Nip\Controllers\Controller;

/**
 * Class CrudController
 * @package ByTIC\Controllers\Extra\Tests\Fixtures
 */
class CrudController extends Controller
{
    use CrudModels;

    public function setAfterUrlFlashTest($url, $flash)
    {
        $this->setAfterFlashName($url, $flash);
    }
}
