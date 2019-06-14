# Initiating SSO

## Service Provider (SP) Initiated
Initiating SSO via the Service Provider is the most common. For example, the user tries to goto a page
that is password protected, then is redirected to the IdP to login. On successful login, the user is redirected back 
to the page on the Service Provider's site.

This functionality is build-in to the plugin and will work as stated above. 

## Identity Provider (IdP) Initiated
Initiating SSO via the Identity Provider is similar to the Service Provider but _the user must initiate the process_. 
This can give a poor user experience in certain situations. IdP initiated SSO works by sending the user (using whatever 
means chosen) to an endpoint/URL on the IdP site. This skips the AuthnRequest message by taking the users to a SP specific
URL on the IdP (it is SP aware), builds the Response message, and redirects the user to the Assertion Consumer Service. 

In layman's terms, the plugin will provider a URL for a specific SP that a user can goto to initiate the process. This 
can be a button or link on any site. 

You can find the URL to initiate login under the 'Login/Logout Paths' on the 'Configure' tab, labeled 'Login Path'. The 
value will be a relative URL looking something like the following: `/sso/login/request/<SP-UID>`. Obviously, add the 
IdP/Craft host as needed.

### RelayState
RelayState is still available via IdP initiated SSO. You may pass a normal string (not base 64'd, ie, 
`/my/path-to/account`) for easy of use or a base 64'd string. An example would be 
`https://<idp hostname>/sso/login/request/<SP-UID>?RelayState=/my/path-to/account`

## Reference
- [5.1.3 SP-Initiated SSO: POST/Artifact Bindings](http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html) (Direct link does not work, scroll to section 5.1.3)
- [5.1.4 IdP-Initiated SSO: POST Binding](http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html) (Direct link does not work, scroll to section 5.1.4)
