<?php

namespace flipbox\saml\idp\fields;

use flipbox\saml\core\fields\AbstractExternalIdentity;
use flipbox\saml\idp\traits\SamlPluginEnsured;

class ExternalIdentity extends AbstractExternalIdentity
{

    use SamlPluginEnsured;
}
