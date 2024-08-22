<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 */

namespace flipbox\saml\idp\controllers\cp\view;

use flipbox\saml\idp\Saml;

class LoginController extends GeneralController
{
    public const TEMPLATE_INDEX = DIRECTORY_SEPARATOR . '_cp';

    public $allowAnonymous = [
        'index',
    ];

    public function actionIndex()
    {
        $variables = Saml::getInstance()->getEditProvider()->getBaseVariables();

        $variables['providers'] = Saml::getInstance()->getProvider()->findByIdp();
        return $this->renderTemplate(
            'saml-idp/_cp/login',
            $variables
        );
    }
}
