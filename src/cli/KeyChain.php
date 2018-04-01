<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 11:59 PM
 */

namespace flipbox\saml\idp\cli;


use flipbox\keychain\keypair\traits\OpenSSLCliUtil;
use flipbox\saml\idp\Saml;
use yii\console\Controller;
use flipbox\keychain\keypair\traits\OpenSSL as OpenSSLTrait;
use flipbox\keychain\keypair\traits\OpenSSL as OpenSSLCliTrait;

class KeyChain extends Controller
{

    use OpenSSLTrait, OpenSSLCliTrait, OpenSSLCliUtil;
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