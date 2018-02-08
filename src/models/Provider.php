<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 9:30 PM
 */

namespace flipbox\saml\idp\models;


use craft\base\Model;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\models\ModelWithId;
use flipbox\ember\traits\StateAttribute;
use flipbox\saml\core\models\ProviderInterface;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Metadata\EntityDescriptor;

class Provider extends ModelWithId implements ProviderInterface
{

    use StateAttribute;

    /**
     * @var bool
     */
    public $encryptAssertions = false;

    /**
     * @var bool
     */
    public $signResponse = true;

    /**
     * @var $entityId string
     */
    protected $entityId;

    /**
     * @var $metadata EntityDescriptor
     */
    protected $metadata;

    public function attributes()
    {
        return array_merge(
            [
                'entityId',
                'metadata',
            ],
            parent::attributes()
        );
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        if (! $this->entityId) {
            if ($this->getMetadata()) {
                $this->entityId = $this->getMetadata()->getEntityID();
            }
        }

        return $this->entityId;
    }

    /**
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @param $metadata
     * @return $this
     * @throws \Exception
     */
    public function setMetadata($metadata)
    {
        if (is_string($metadata)) {
            $metadata = EntityDescriptor::fromXML($metadata, new DeserializationContext());
            $this->setEntityId(
                $metadata->getEntityID()
            );
        }
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return EntityDescriptor|null
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}