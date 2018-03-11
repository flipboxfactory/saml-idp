<?php
namespace flipbox\saml\idp\transformers;

use craft\elements\User;
use flipbox\saml\idp\Saml;
use LightSaml\Model\Assertion\Assertion;

/**
 * Class ResponseAssertion
 * @package flipbox\saml\idp\transformers
 */
class ResponseAssertion extends AbstractResponseAssertion
{

    /**
     * @inheritdoc
     */
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
}