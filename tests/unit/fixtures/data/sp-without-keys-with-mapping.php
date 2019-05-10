<?php

return [
    'sp-without-keys-with-mapping0' => [
        'providerType' => 'sp',
        'metadata' => '<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://sp.localhost:9090/">
    <md:SPSSODescriptor protocolSupportEnumeration="" AuthnRequestsSigned="true">
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="http://sp.localhost:9090/sso/logout/request" ResponseLocation="http://sp.localhost:9090/sso/logout" index="1"/>
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="http://sp.localhost:9090/sso/login" index="1"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>
',
        'mapping' => '[{"craftProperty":"firstName","attributeName":"FirstName"},{"craftProperty":"lastName","attributeName":"LastName"},{"craftProperty":"email","attributeName":"Email"},{"craftProperty":"uid","attributeName":"UID"},{"craftProperty":"id","attributeName":"Craft ID"},{"craftProperty":"username","attributeName":"Username"}]',
    ],
    'sp-without-keys-with-mapping1' => [
        'providerType' => 'sp',
        'metadata' => '<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://sp.localhost:9090/">
    <md:SPSSODescriptor protocolSupportEnumeration="" AuthnRequestsSigned="true">
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="http://sp.localhost:9090/sso/logout/request" ResponseLocation="http://sp.localhost:9090/sso/logout" index="1"/>
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="http://sp.localhost:9090/sso/login" index="1"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>
',
        'mapping' => '[{"craftProperty":"firstName","attributeName":"FirstName"},{"craftProperty":"lastName","attributeName":"LastName"},{"craftProperty":"email","attributeName":"Email"},{"craftProperty":"uid","attributeName":"UID"},{"craftProperty":"id","attributeName":"Craft ID"},{"craftProperty":"username","attributeName":"Username"}]',
    ],
];
