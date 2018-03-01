<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/11/18
 * Time: 9:44 PM
 */

namespace flipbox\saml\idp\services\bindings;


use flipbox\saml\core\SamlPluginInterface;
use flipbox\saml\core\services\bindings\AbstractHttpPost;
use flipbox\saml\idp\Saml;

class HttpPost extends AbstractHttpPost
{

    const TEMPLATE_PATH = 'saml-idp/_components/post-binding-submit.twig';

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return static::TEMPLATE_PATH;
    }

    /**
     * @inheritdoc
     */
    protected function getSamlPlugin(): SamlPluginInterface
    {
        return Saml::getInstance();
    }
}