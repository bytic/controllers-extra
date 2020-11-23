<?php

namespace ByTIC\Controllers\Behaviors;

use ByTIC\Common\Application\Models\Users\Traits\AbstractUsersTrait as Users;
use ByTIC\Common\Application\Models\Users\Traits\AbstractUserTrait as User;
use Nip\Records\Locator\ModelLocator;

/**
 * Trait HasUser
 * @package ByTIC\Controllers\Behaviors
 */
trait HasUser
{
    protected $user = null;

    /**
     * @return User
     */
    protected function _getUser()
    {
        if ($this->user === null) {
            $this->initUser();
        }

        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    protected function initUser()
    {
        $this->setUser($this->generateUser());
    }

    /**
     * @return \Nip\Records\Record|User
     */
    protected function generateUser()
    {
        return $this->getUserManager()->getCurrent();
    }

    /**
     * @return \Nip\Records\RecordManager|Users
     */
    protected function getUserManager()
    {
        return ModelLocator::get('users');
    }

    protected function _checkUser()
    {
        if (!$this->_getUser()->authenticated()) {
            $this->redirect($this->getNonAuthRedirectURL());
        }
    }

    protected function getNonAuthRedirectURL()
    {
        return $this->URL()->base();
    }
}