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
     * Key Value store that maps the Response name (the array key) with
     * the user property.
     *
     * Simple mapping works by matching the Response name in the array with the user's
     * property, and setting what is found in the Response's value to the user element.
     *
     * Take the following example:
     * Here is my responseAttributeMap from the config/saml-sp.php
     *
     * @see ClaimTypes
     *
     * ```php
     * ...
     * 'responseAttributeMap' => [
     *      ClaimTypes::EMAIL_ADDRESS => 'email',
     *      'firstName' => 'firstName',
     * ],
     * ...
     * ```
     *
     * Here is a snippet from the Saml XML Response message:
     * ```xml
     * <Attribute Name="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress">
     *    <AttributeValue>damien@example.com</AttributeValue>
     * </Attribute>
     * <Attribute Name="firstName">
     *    <AttributeValue>Damien</AttributeValue>
     * </Attribute>
     * ```
     *
     * The above would result in the following mapping:
     * ```php
     * // @var $user \craft\elements\User
     * $user->firstName = 'Damien';
     * $user->email = 'damien@example.com';
     * ```
     *
     * With more complex user fields, you can set the array value to a callable. For
     * more on callables http://php.net/manual/en/language.types.callable.php.
     *
     * Here is my responseAttributeMap with a callable from the config/saml-sp.php
     * ```php
     * 'responseAttributeMap' => [
     *      ClaimTypes::EMAIL_ADDRESS => function(\craft\elements\User $user, array $attribute){
     *         // $attribute is key/name value (string/array) pair
     *
     *         // Could be an array
     *         $attributeValue = $attribute['Email'];
     *         if(is_array($attributeValue)){
     *             $attributeValue = $attributeValue[0];
     *         }
     *
     *         $user->email = $attribute['Email'];
     *      }
     * ],
     * ```
     * @var array
     */

    public $responseAttributeMap = [
        // SP Response Attribute Name => Craft Property
        'Email' => 'email',
        'FirstName' => 'firstName',
        'LastName' => 'lastName',
    ];
}
