<?php
/**
 * @var $faker \Faker\Generator
 * @var $index integer
 */

return [
    'label' => $faker->word,
    'providerType' => \flipbox\saml\core\models\SettingsInterface::SP,
    'metadata' => file_get_contents(dirname(__DIR__, 3) . '/_data/xml/sp-metadata-with-keys.xml'),
    'mapping' => json_encode([
        [
            'craftProperty' => 'firstName',
            'attributeName' => 'FirstName',
        ],
        [
            'craftProperty' => 'lastName',
            'attributeName' => 'LastName',
        ],
        [
            'craftProperty' => 'email',
            'attributeName' => 'Email',
        ],
        [
            'craftProperty' => 'uid',
            'attributeName' => 'UID',
        ],
        [
            'craftProperty' => 'id',
            'attributeName' => 'Craft ID',
        ],
        [
            'craftProperty' => 'username',
            'attributeName' => 'Username',
        ],
    ]),

];
