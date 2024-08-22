<?php

namespace flipbox\saml\idp\events;

use craft\elements\User;
use SAML2\Response;
use yii\base\Event;

class ResponseEvent extends Event
{
    /**
     * @var Response
     */
    public $response;

    /**
     * @var User
     */
    public $user;
}
