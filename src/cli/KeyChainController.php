<?php

namespace flipbox\saml\idp\cli;


use flipbox\keychain\cli\AbstractOpenSSL;
use flipbox\saml\idp\Saml;

class KeyChainController extends AbstractOpenSSL
{

    /**
     * @var bool $force
     * Force save the metadata. If one already exists, it'll be overwritten.
     */
    public $force;

    protected function getPlugin()
    {
        return Saml::getInstance();
    }

}