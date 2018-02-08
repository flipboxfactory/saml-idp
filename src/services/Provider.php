<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 10:44 PM
 */

namespace flipbox\saml\idp\services;


use craft\base\Component;
use flipbox\ember\models\Model;
use flipbox\ember\services\traits\AccessorByIdOrString;
use flipbox\ember\services\traits\ModelDelete;
use flipbox\ember\services\traits\ModelSave;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\models\Provider as ProviderModel;
use LightSaml\Model\Assertion\Issuer;
use yii\db\ActiveRecord;


class Provider extends Component
{

    use AccessorByIdOrString, ModelSave, ModelDelete;

    const HANDLE = 'entityId';


    public static function objectClass(): string
    {
        return ProviderModel::class;
    }

    public static function recordClass(): string
    {
        return ProviderRecord::class;
    }

    public function stringProperty(): string
    {
        return static::HANDLE;
    }

    public function getRecordByModel(Model $model): ActiveRecord
    {
        /** @var $model ProviderModel */
        return ProviderRecord::findOne([
           'entityId' => $model->getEntityId(),
        ]);

    }

    public function modelToRecord(Model $model, bool $mirrorScenario = true): ActiveRecord
    {
//        if (!$record = $this->findRecordByObject($model)) {
//            $record = $this->createRecord();
//        }
//
//        if ($mirrorScenario === true) {
//            $record->setScenario($model->getScenario());
//        }
//
//        // Populate the record attributes
//        $this->transferToRecord($model, $record);
//        return $record;

        /** @var $model ProviderModel */
        return new ProviderRecord([
            'id' => $model->getId(),
            'isNewRecord' => $this->isNew($model),
            'entityId' => $model->getEntityId(),
            'metadata' => SerializeHelper::toXml($model->getMetadata()),
            'enabled'  => (bool)$model->enabled,
            'signResponse'  => (bool)$model->signResponse,
            'encryptAssertions'  => (bool)$model->encryptAssertions,
            'key'  => (bool)$model->getKey(),
        ]);
    }

    public function isNew(Model $model): bool
    {
        return ! $model->id;
    }

    /**
     * @param Issuer $issuer
     * @return \flipbox\saml\idp\models\Provider
     */
    public function findByIssuer(Issuer $issuer)
    {
        return $this->findByString($issuer->getValue());
    }
}