<?php

return [
    'sp-with-keys-and-mapping0' => [
        'metadata' => '<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://sp.localhost:9090/">
    <md:SPSSODescriptor protocolSupportEnumeration="" AuthnRequestsSigned="true">
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data>
                    <ds:X509Certificate>
                        MIIEEjCCAvqgAwIBAgIBADANBgkqhkiG9w0BAQsFADCBoTELMAkGA1UEBhMCVVMxETAPBgNVBAgMCENvbG9yYWRvMQ8wDQYDVQQHDAZEZW52ZXIxGDAWBgNVBAoMD0ZsaXBib3ggRGlnaXRhbDELMAkGA1UECwwCSVQxGzAZBgNVBAMMEmZsaXBib3hkaWdpdGFsLmNvbTEqMCgGCSqGSIb3DQEJARYba2V5Y2hhaW5AZmxpcGJveGRpZ2l0YWwuY29tMB4XDTE5MDQyOTIxMDUyNFoXDTIxMDQyODIxMDUyNFowgaExCzAJBgNVBAYTAlVTMREwDwYDVQQIDAhDb2xvcmFkbzEPMA0GA1UEBwwGRGVudmVyMRgwFgYDVQQKDA9GbGlwYm94IERpZ2l0YWwxCzAJBgNVBAsMAklUMRswGQYDVQQDDBJmbGlwYm94ZGlnaXRhbC5jb20xKjAoBgkqhkiG9w0BCQEWG2tleWNoYWluQGZsaXBib3hkaWdpdGFsLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMIPDFFOroGhhDuXXdNWGCH+fq4JG6M08Tbd13rxqoqrUEjPRc6xUj9fcdpdoXFHT1dXnAeyiCvXFUtWBzswDEAM5H6MciNKtEUvW03Dnc02+bRCkApH/Wf69YxGaOZdbLWexqaVTj4HbidZxYmHVglpMxGybR7r+AomQaKS9Ex7rMAaHBART8yiIcgKjL+y29qEglN301/ft+jhT4zfT+YAKAkeoRuTmzGLxeJe0oRzHLnC6F7k610A4trOEgKj+8CaqnoM+saUWmPzYA/yBrGOLlaZGOo/UqUF/bimd5SHPI6792SxFM1MsEJWs4w6jY4nl0PMnH9NtQLjbUayPoUCAwEAAaNTMFEwHQYDVR0OBBYEFBzLOHK9uHwDI552dE11cjNPlnCuMB8GA1UdIwQYMBaAFBzLOHK9uHwDI552dE11cjNPlnCuMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAAjp0QAPtu0xr72dCXVlmt7RJwuwSsl6A/kKg+2KvgPh7llqhkCDIRIXpp5sUQ+HmCSZhCL5CXWPmgwMI5TeE2vRH6UR3UOHimg9uPzZGlEjvs3FAIbm85ykPJFuc9ofhc7d4r+rCxQlzKsXtrSHgx2cIgx1gJk6Ijfkt5trMJKFo+fFEty3/JvqzC7xIHaT1MBO3tXNIi1xgsoZJA7AxocDmXk7VGJR5LvKhkYrcfJ/gGuYqr4E/La94iBUe8WCWB4Kd/UzM8ROKcPHUgU5rcDm+dg8wz2v1IWYpxz4+TWYL7LnaHkgky8HetvNsEUQFb90PH0yyvTc3Hkok1C5xNs=
                    </ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:KeyDescriptor use="encryption">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data>
                    <ds:X509Certificate>
                        MIIEEjCCAvqgAwIBAgIBADANBgkqhkiG9w0BAQsFADCBoTELMAkGA1UEBhMCVVMxETAPBgNVBAgMCENvbG9yYWRvMQ8wDQYDVQQHDAZEZW52ZXIxGDAWBgNVBAoMD0ZsaXBib3ggRGlnaXRhbDELMAkGA1UECwwCSVQxGzAZBgNVBAMMEmZsaXBib3hkaWdpdGFsLmNvbTEqMCgGCSqGSIb3DQEJARYba2V5Y2hhaW5AZmxpcGJveGRpZ2l0YWwuY29tMB4XDTE5MDQyOTIxMDUyNFoXDTIxMDQyODIxMDUyNFowgaExCzAJBgNVBAYTAlVTMREwDwYDVQQIDAhDb2xvcmFkbzEPMA0GA1UEBwwGRGVudmVyMRgwFgYDVQQKDA9GbGlwYm94IERpZ2l0YWwxCzAJBgNVBAsMAklUMRswGQYDVQQDDBJmbGlwYm94ZGlnaXRhbC5jb20xKjAoBgkqhkiG9w0BCQEWG2tleWNoYWluQGZsaXBib3hkaWdpdGFsLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMIPDFFOroGhhDuXXdNWGCH+fq4JG6M08Tbd13rxqoqrUEjPRc6xUj9fcdpdoXFHT1dXnAeyiCvXFUtWBzswDEAM5H6MciNKtEUvW03Dnc02+bRCkApH/Wf69YxGaOZdbLWexqaVTj4HbidZxYmHVglpMxGybR7r+AomQaKS9Ex7rMAaHBART8yiIcgKjL+y29qEglN301/ft+jhT4zfT+YAKAkeoRuTmzGLxeJe0oRzHLnC6F7k610A4trOEgKj+8CaqnoM+saUWmPzYA/yBrGOLlaZGOo/UqUF/bimd5SHPI6792SxFM1MsEJWs4w6jY4nl0PMnH9NtQLjbUayPoUCAwEAAaNTMFEwHQYDVR0OBBYEFBzLOHK9uHwDI552dE11cjNPlnCuMB8GA1UdIwQYMBaAFBzLOHK9uHwDI552dE11cjNPlnCuMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAAjp0QAPtu0xr72dCXVlmt7RJwuwSsl6A/kKg+2KvgPh7llqhkCDIRIXpp5sUQ+HmCSZhCL5CXWPmgwMI5TeE2vRH6UR3UOHimg9uPzZGlEjvs3FAIbm85ykPJFuc9ofhc7d4r+rCxQlzKsXtrSHgx2cIgx1gJk6Ijfkt5trMJKFo+fFEty3/JvqzC7xIHaT1MBO3tXNIi1xgsoZJA7AxocDmXk7VGJR5LvKhkYrcfJ/gGuYqr4E/La94iBUe8WCWB4Kd/UzM8ROKcPHUgU5rcDm+dg8wz2v1IWYpxz4+TWYL7LnaHkgky8HetvNsEUQFb90PH0yyvTc3Hkok1C5xNs=
                    </ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                Location="http://sp.localhost:9090/sso/logout/request"
                                ResponseLocation="http://sp.localhost:9090/sso/logout" index="1"/>
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                     Location="http://sp.localhost:9090/sso/login" index="1"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>
',
        'mapping' => '[{"attributeName":"att1","craftProperty":"email"}]',
    ],
    'sp-with-keys-and-mapping1' => [
        'metadata' => '<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://sp.localhost:9090/">
    <md:SPSSODescriptor protocolSupportEnumeration="" AuthnRequestsSigned="true">
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data>
                    <ds:X509Certificate>
                        MIIEEjCCAvqgAwIBAgIBADANBgkqhkiG9w0BAQsFADCBoTELMAkGA1UEBhMCVVMxETAPBgNVBAgMCENvbG9yYWRvMQ8wDQYDVQQHDAZEZW52ZXIxGDAWBgNVBAoMD0ZsaXBib3ggRGlnaXRhbDELMAkGA1UECwwCSVQxGzAZBgNVBAMMEmZsaXBib3hkaWdpdGFsLmNvbTEqMCgGCSqGSIb3DQEJARYba2V5Y2hhaW5AZmxpcGJveGRpZ2l0YWwuY29tMB4XDTE5MDQyOTIxMDUyNFoXDTIxMDQyODIxMDUyNFowgaExCzAJBgNVBAYTAlVTMREwDwYDVQQIDAhDb2xvcmFkbzEPMA0GA1UEBwwGRGVudmVyMRgwFgYDVQQKDA9GbGlwYm94IERpZ2l0YWwxCzAJBgNVBAsMAklUMRswGQYDVQQDDBJmbGlwYm94ZGlnaXRhbC5jb20xKjAoBgkqhkiG9w0BCQEWG2tleWNoYWluQGZsaXBib3hkaWdpdGFsLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMIPDFFOroGhhDuXXdNWGCH+fq4JG6M08Tbd13rxqoqrUEjPRc6xUj9fcdpdoXFHT1dXnAeyiCvXFUtWBzswDEAM5H6MciNKtEUvW03Dnc02+bRCkApH/Wf69YxGaOZdbLWexqaVTj4HbidZxYmHVglpMxGybR7r+AomQaKS9Ex7rMAaHBART8yiIcgKjL+y29qEglN301/ft+jhT4zfT+YAKAkeoRuTmzGLxeJe0oRzHLnC6F7k610A4trOEgKj+8CaqnoM+saUWmPzYA/yBrGOLlaZGOo/UqUF/bimd5SHPI6792SxFM1MsEJWs4w6jY4nl0PMnH9NtQLjbUayPoUCAwEAAaNTMFEwHQYDVR0OBBYEFBzLOHK9uHwDI552dE11cjNPlnCuMB8GA1UdIwQYMBaAFBzLOHK9uHwDI552dE11cjNPlnCuMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAAjp0QAPtu0xr72dCXVlmt7RJwuwSsl6A/kKg+2KvgPh7llqhkCDIRIXpp5sUQ+HmCSZhCL5CXWPmgwMI5TeE2vRH6UR3UOHimg9uPzZGlEjvs3FAIbm85ykPJFuc9ofhc7d4r+rCxQlzKsXtrSHgx2cIgx1gJk6Ijfkt5trMJKFo+fFEty3/JvqzC7xIHaT1MBO3tXNIi1xgsoZJA7AxocDmXk7VGJR5LvKhkYrcfJ/gGuYqr4E/La94iBUe8WCWB4Kd/UzM8ROKcPHUgU5rcDm+dg8wz2v1IWYpxz4+TWYL7LnaHkgky8HetvNsEUQFb90PH0yyvTc3Hkok1C5xNs=
                    </ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:KeyDescriptor use="encryption">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data>
                    <ds:X509Certificate>
                        MIIEEjCCAvqgAwIBAgIBADANBgkqhkiG9w0BAQsFADCBoTELMAkGA1UEBhMCVVMxETAPBgNVBAgMCENvbG9yYWRvMQ8wDQYDVQQHDAZEZW52ZXIxGDAWBgNVBAoMD0ZsaXBib3ggRGlnaXRhbDELMAkGA1UECwwCSVQxGzAZBgNVBAMMEmZsaXBib3hkaWdpdGFsLmNvbTEqMCgGCSqGSIb3DQEJARYba2V5Y2hhaW5AZmxpcGJveGRpZ2l0YWwuY29tMB4XDTE5MDQyOTIxMDUyNFoXDTIxMDQyODIxMDUyNFowgaExCzAJBgNVBAYTAlVTMREwDwYDVQQIDAhDb2xvcmFkbzEPMA0GA1UEBwwGRGVudmVyMRgwFgYDVQQKDA9GbGlwYm94IERpZ2l0YWwxCzAJBgNVBAsMAklUMRswGQYDVQQDDBJmbGlwYm94ZGlnaXRhbC5jb20xKjAoBgkqhkiG9w0BCQEWG2tleWNoYWluQGZsaXBib3hkaWdpdGFsLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMIPDFFOroGhhDuXXdNWGCH+fq4JG6M08Tbd13rxqoqrUEjPRc6xUj9fcdpdoXFHT1dXnAeyiCvXFUtWBzswDEAM5H6MciNKtEUvW03Dnc02+bRCkApH/Wf69YxGaOZdbLWexqaVTj4HbidZxYmHVglpMxGybR7r+AomQaKS9Ex7rMAaHBART8yiIcgKjL+y29qEglN301/ft+jhT4zfT+YAKAkeoRuTmzGLxeJe0oRzHLnC6F7k610A4trOEgKj+8CaqnoM+saUWmPzYA/yBrGOLlaZGOo/UqUF/bimd5SHPI6792SxFM1MsEJWs4w6jY4nl0PMnH9NtQLjbUayPoUCAwEAAaNTMFEwHQYDVR0OBBYEFBzLOHK9uHwDI552dE11cjNPlnCuMB8GA1UdIwQYMBaAFBzLOHK9uHwDI552dE11cjNPlnCuMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAAjp0QAPtu0xr72dCXVlmt7RJwuwSsl6A/kKg+2KvgPh7llqhkCDIRIXpp5sUQ+HmCSZhCL5CXWPmgwMI5TeE2vRH6UR3UOHimg9uPzZGlEjvs3FAIbm85ykPJFuc9ofhc7d4r+rCxQlzKsXtrSHgx2cIgx1gJk6Ijfkt5trMJKFo+fFEty3/JvqzC7xIHaT1MBO3tXNIi1xgsoZJA7AxocDmXk7VGJR5LvKhkYrcfJ/gGuYqr4E/La94iBUe8WCWB4Kd/UzM8ROKcPHUgU5rcDm+dg8wz2v1IWYpxz4+TWYL7LnaHkgky8HetvNsEUQFb90PH0yyvTc3Hkok1C5xNs=
                    </ds:X509Certificate>
                </ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                Location="http://sp.localhost:9090/sso/logout/request"
                                ResponseLocation="http://sp.localhost:9090/sso/logout" index="1"/>
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                     Location="http://sp.localhost:9090/sso/login" index="1"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>
',
        'mapping' => '[{"attributeName":"att3","craftProperty":"firstName"}]',
    ],
];
