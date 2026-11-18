<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/9/18
 * Time: 9:37 AM
 */

namespace flipbox\saml\idp\models;

use flipbox\saml\core\helpers\ClaimTypes;
use flipbox\saml\core\models\AbstractSettings;
use flipbox\saml\core\models\SettingsInterface;

class Settings extends AbstractSettings implements SettingsInterface
{
    /**
     * @var bool
     */
    public $wantsAuthnRequestsSigned = false;

    /**
     * SubjectConfirmationData and Conditions datetimes
     * https://stackoverflow.com/a/29546696/1590910
     */

    /**
     * Start datetime to consume login response
     * @see \DateTime::__construct
     *
     * @var string
     */
    public $messageNotBefore = 'now';

    /**
     * Expiration datetime to consume login response
     * @see \DateTime::__construct
     *
     * @var string
     */
    public $messageNotOnOrAfter = '+10 MINUTES';

    /**
     * The attributes released to service providers that don't have their own mapping. Mappings saved
     * on a provider's Configure tab take precedence, even when they're empty.
     *
     * Each key is the attribute name in the Assertion, and each value the Craft user property it's taken
     * from. For example, in config/saml-idp.php:
     *
     * ```php
     * 'responseAttributeMap' => [
     *      ClaimTypes::EMAIL_ADDRESS => 'email',
     *      'FirstName' => 'firstName',
     * ],
     * ```
     *
     * releases:
     *
     * ```xml
     * <saml:Attribute Name="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress">
     *    <saml:AttributeValue>jane@example.com</saml:AttributeValue>
     * </saml:Attribute>
     * <saml:Attribute Name="FirstName">
     *    <saml:AttributeValue>Jane</saml:AttributeValue>
     * </saml:Attribute>
     * ```
     *
     * A value can also be a mapping row, as the control panel saves them, to use a template:
     * `['attributeName' => 'Department', 'templateOverride' => '{department.one().title}']`.
     *
     * @see ClaimTypes
     * @var array
     */
    public $responseAttributeMap = [
        // SP Response Attribute Name => Craft Property
        'Email' => 'email',
        'FirstName' => 'firstName',
        'LastName' => 'lastName',
    ];
}
