<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 2/21/18
 * Time: 9:22 PM
 */

namespace flipbox\saml\idp\transformers;

use craft\elements\User;
use Flipbox\Transform\Scope;
use Flipbox\Transform\Transformers\AbstractTransformer;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Attribute;

/**
 * Class AbstractResponseAssertion
 * @package flipbox\saml\idp\transformers
 */
abstract class AbstractResponseAssertion extends AbstractTransformer
{

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user, array $config = [])
    {
        parent::__construct($config);
        $this->user = $user;
    }

    /**
     * @param Assertion $assertion
     * @param User $user
     * @return mixed
     */
    abstract public function transform(Assertion $assertion, User $user);

    /**
     * @inheritdoc
     */
    public function __invoke($data, Scope $scope, string $identifier = null)
    {
        return $this->transform(
            $data,
            $this->user
        );
    }

    /**
     * @param Assertion $assertion
     * @param $name
     * @param $value
     */
    protected function addAttribute(Assertion $assertion, $name, $value)
    {

        $assertion->getFirstAttributeStatement()->addAttribute(
            new Attribute($name, $value)
        );
    }

}