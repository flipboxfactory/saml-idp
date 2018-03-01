<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 2/21/18
 * Time: 9:22 PM
 */

namespace flipbox\saml\idp\transformers;

use craft\elements\User;
use flipbox\saml\idp\Saml;
use Flipbox\Transform\Scope;
use Flipbox\Transform\Transformers\AbstractTransformer;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Assertion\AttributeStatement;

class ResponseAssertion extends AbstractTransformer
{

    protected $user;

    public function __construct(User $user, array $config = [])
    {
        parent::__construct($config);
        $this->user = $user;
    }

    public function transform(Assertion $assertion, User $user)
    {

        foreach (Saml::getInstance()->getSettings()->responseAttributeMap as $craftName => $attributeName) {
            $this->addAttribute(
                $assertion,
                $attributeName,
                $user->{$craftName}
            );
        }

        return $assertion;
    }

    public function __invoke($data, Scope $scope, string $identifier = null)
    {
        return $this->transform(
            $data,
            $this->user
        );
    }

    protected function addAttribute(Assertion $assertion, $name, $value)
    {

        $assertion->getFirstAttributeStatement()->addAttribute(
            new Attribute($name, $value)
        );
    }

}