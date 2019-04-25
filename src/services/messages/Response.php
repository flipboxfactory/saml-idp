<?php

namespace flipbox\saml\idp\services\messages;


use craft\base\Component;
use flipbox\saml\core\services\messages\SamlResponseInterface;
use flipbox\saml\idp\records\ProviderRecord;
use flipbox\saml\idp\records\ProviderRecord as Provider;
use flipbox\saml\idp\Saml;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\Response as ResponseMessage;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Model\Protocol\StatusResponse;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;

class Response extends Component
{

    const CONSENT_IMPLICIT = 'urn:oasis:names:tc:SAML:2.0:consent:current-implicit';

    /**
     * @param AbstractRequest $samlMessage
     * @param array $config
     * @return StatusResponse
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function create(AbstractRequest $samlMessage): StatusResponse
    {
        /** @var AuthnRequest $authnRequest */
        $authnRequest = $samlMessage;

        /** @var Provider $idpProvider */
        $idpProvider = Saml::getInstance()->getProvider()->findOwn();

        /** @var Provider $spProvider */
        $spProvider = Saml::getInstance()->getProvider()->findByEntityId(
            $authnRequest->getIssuer()->getValue()
        )->one();

        $response = $this->createGeneral($authnRequest, $idpProvider);

        Saml::getInstance()->getResponseAssertion()->create(
            $authnRequest,
            $response,
            $spProvider,
            $idpProvider
        );

        /**
         * Sign Response
         */
        $response->setSignature(
            new SignatureWriter(
                (new X509Certificate())->loadPem(
                    $idpProvider->getKeychain()->one()->getDecryptedCertificate()
                ),
                KeyHelper::createPrivateKey(
                    $idpProvider->getKeychain()->one()->getDecryptedKey(),
                    ''
                )
            )
        );

        return $response;
    }

    /**
     * @param ResponseMessage $response
     * @param AuthnRequest $authnRequest
     * @param ProviderRecord $idpProvider
     * @throws \Exception
     */
    protected function createGeneral(AuthnRequest $authnRequest, ProviderRecord $idpProvider)
    {
        $response = new ResponseMessage();
        $response->setIssuer(
            new Issuer($idpProvider->entityId)
        )
            ->setID(Helper::generateID())
            ->setDestination(
                $authnRequest->getAssertionConsumerServiceURL()
            )->setConsent(static::CONSENT_IMPLICIT)
            ->setInResponseTo(
                $authnRequest->getID()
            )->setStatus(
                new Status(
                    new StatusCode(SamlConstants::STATUS_SUCCESS
                    )
                )
            )->setVersion(SamlConstants::VERSION_20)
            ->setIssueInstant(
                new \DateTime()
            )->setRelayState(
                $authnRequest->getRelayState()
            );

        return $response;
    }

}