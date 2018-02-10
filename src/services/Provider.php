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
use flipbox\ember\services\traits\AccessorById;
use flipbox\ember\services\traits\ModelDelete;
use flipbox\ember\services\traits\ModelSave;
use flipbox\saml\core\helpers\SerializeHelper;
use flipbox\saml\core\services\messages\ProviderServiceInterface;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\models\Provider as ProviderModel;
use LightSaml\Model\Assertion\Issuer;
use yii\db\ActiveRecord;


/**
 * Class Provider
 * @package flipbox\saml\idp\services
 */
class Provider extends Component implements ProviderServiceInterface
{

    use AccessorById, ModelSave, ModelDelete;

    /**
     * @inheritdoc
     */
    public static function objectClass(): string
    {
        return ProviderModel::class;
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return ProviderRecord::class;
    }

    /**
     * @inheritdoc
     */
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
        $metadata = SerializeHelper::toXml($model->getMetadata());
        return new ProviderRecord([
            'id'                => $model->getId(),
            'isNewRecord'       => $this->isNew($model),
            'entityId'          => $model->getEntityId(),
            'metadata'          => $metadata,
            'sha256'            => hash('sha256', $metadata),
            'sortOrder'         => $model->sortOrder,
            'enabled'           => (bool)$model->enabled,
            'signResponse'      => (bool)$model->signResponse,
            'encryptAssertions' => (bool)$model->encryptAssertions,
            'localKeyId'        => $model->localKeyId,
        ]);
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function isNew(Model $model): bool
    {
        return ! $model->id;
    }

    /**
     * @param string $entityId
     * @return ProviderRecord|null
     */
    public function findByEntityId($entityId)
    {
        return ProviderRecord::find()->andWhere([
            'entityId' => $entityId,
            'enabled'  => true,
        ])->orderBy('sortOrder')->one();
    }

    /**
     * @param Issuer $issuer
     * @return ProviderRecord|null
     */
    public function findByIssuer(Issuer $issuer)
    {
        return $this->findByEntityId($issuer->getValue());
    }
}