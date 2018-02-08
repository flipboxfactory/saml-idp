<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:47 AM
 */

namespace flipbox\saml\idp\controllers;


use craft\web\Controller;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\idp\Saml;

class MetadataController extends Controller
{

    /**
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionIndex()
    {

        $this->requireAdmin();

        $metadata = Saml::getInstance()->getMetadata()->create();


        SerializeHelper::xmlContentType();
        return SerializeHelper::toXml($metadata);
    }

}