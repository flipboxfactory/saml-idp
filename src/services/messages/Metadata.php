<?php

namespace flipbox\saml\idp\services\messages;


use flipbox\saml\core\services\messages\AbstractMetadata;
use flipbox\saml\core\services\messages\MetadataServiceInterface;
use flipbox\saml\idp\traits\SamlPluginEnsured;

class Metadata extends AbstractMetadata implements MetadataServiceInterface
{
    use SamlPluginEnsured;

    /**
     * @return array
     */
    public function getSupportedBindings()
    {
        return $this->supportedBindings;
    }
}
