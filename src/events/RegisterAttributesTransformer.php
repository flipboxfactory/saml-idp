<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/16/18
 * Time: 8:09 PM
 */

namespace flipbox\saml\idp\events;


use craft\elements\User;
use flipbox\saml\idp\transformers\AbstractResponseAssertion;
use flipbox\saml\sp\transformers\AbstractResponseToUser;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class RegisterAttributesTransformer
 * @package flipbox\saml\idp\events
 */
class RegisterAttributesTransformer extends Event
{
    protected $transformers = [];

    /**
     * @param string $entityId
     * @param string $class
     * @return $this
     * @throws InvalidConfigException
     */
    public function setTransformer(string $entityId, string $class)
    {
        if (! class_exists($class) || ! (new $class(new User) instanceof AbstractResponseAssertion)) {
            throw new InvalidConfigException(
                sprintf(
                    "Transformer must be class of instanceof %s. %s was given and incorrect.",
                    AbstractResponseAssertion::class,
                    $class
                )
            );
        }
        $this->transformers[$entityId] = $class;
        return $this;
    }

    /**
     * @param $entityId
     * @return mixed|null
     */
    public function getTransformer($entityId)
    {
        return isset($this->transformers[$entityId]) ? $this->transformers[$entityId] : null;
    }
}