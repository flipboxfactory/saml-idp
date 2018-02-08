<?php
/**
 * Created by PhpStorm.
 * User: dsmrt
 * Date: 1/12/18
 * Time: 11:59 PM
 */

namespace flipbox\saml\idp\cli;


use craft\helpers\Console;
use flipbox\keychain\keypair\KeyPairInterface;
use flipbox\keychain\keypair\OpenSSL;
use flipbox\saml\idp\models\Provider;
use flipbox\saml\idp\Saml;
use yii\console\Controller;
use yii\console\ExitCode;
use flipbox\keychain\keypair\traits\OpenSSL as OpenSSLTrait;

class Metadata extends Controller
{

    use OpenSSLTrait;
    /**
     * @var bool $force
     * Force save the metadata. If one already exists, it'll be overwritten.
     */
    public $force;

    public function options($actionID)
    {
        return array_merge(
            [
                'force',
            ],
            parent::options($actionID)
        );
    }

    public function optionAliases()
    {
        return array_merge(
            [
                'f' => 'force',
            ],
            parent::optionAliases()
        );
    }

    /**
     * @return KeyPairInterface
     */
    protected function configKeyPair()
    {
        $this->stdout(
            PHP_EOL . PHP_EOL . '****************************************************' .
            $this->startNoticeText
            .'****************************************************' . PHP_EOL . PHP_EOL
            , Console::FG_GREEN);

        $config = [];
        foreach ($this->attributes as $attribute => $options) {
            $config[$attribute] = $this->prompt(
                $this->labels[$attribute],
                $options
            );
        }

        return new OpenSSL($config);
    }

    public function actionCreate()
    {

        //create key pair
        $keyPair = $this->configKeyPair();

        $keyPairRecord = $keyPair->create();

        //create metadata
        var_dump($keyPairRecord);
        exit;
        return ExitCode::OK;
    }

    public function actionSave($file = null, $default = true, $enabled = true)
    {

        if (! $file) {
            $this->stderr("No file passed.");
            return ExitCode::NOINPUT;
        }
        $newProvider = new Provider([
            'metadata' => file_get_contents($file),
            'enabled'  => $enabled,
        ]);

        /** @var Provider $provider */
        if ($provider = Saml::getInstance()->getProvider()->findByString($newProvider->getEntityId())) {
            $provider->setMetadata($newProvider->getMetadata());
        } else {
            $provider = new Provider([
                'metadata' => file_get_contents($file),
                'enabled'  => $enabled,
            ]);

        }

        if ($provider->id && ! $this->force) {

            if (! $this->confirm(sprintf(
                    "Are you sure you want to overwrite %s?",
                    $provider->getEntityId()
                )
            )
            ) {
                $this->stdout('Exiting.' . PHP_EOL);
                return ExitCode::OK;
            }

        }

        if (Saml::getInstance()->getProvider()->save($provider)) {

            $this->stdout(sprintf(
                    'Save for %s metadata was successful.',
                    $provider->getEntityId()
                ) . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        return ExitCode::UNSPECIFIED_ERROR;
    }

    public function actionDelete($entityId)
    {
        if (! Saml::getInstance()->getProvider()->delete(new Provider([
            'entityId' => $entityId,
        ]))) {
            $this->stderr("Couldn't delete provider {$entityId}", Console::FG_RED);
        }


        $this->stdout("Successfully deleted provider {$entityId}" . PHP_EOL, Console::FG_GREEN);
    }
}