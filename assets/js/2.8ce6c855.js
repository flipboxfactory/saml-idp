(window.webpackJsonp=window.webpackJsonp||[]).push([[2],{144:function(e,t,i){"use strict";i.r(t);var s=i(0),r=Object(s.a)({},function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"content"},[i("h1",[e._v("Initiating SSO")]),e._m(0),i("p",[e._v("Initiating SSO via the Service Provider is the most common. For example, the user tries to goto a page\nthat is password protected, then is redirected to the IdP to login. On successful login, the user is redirected back\nto the page on the Service Provider's site.")]),i("p",[e._v("This functionality is build-in to the plugin and will work as stated above.")]),e._m(1),e._m(2),i("p",[e._v("In layman's terms, the plugin will provider a URL for a specific SP that a user can goto to initiate the process. This\ncan be a button or link on any site.")]),e._m(3),e._m(4),e._m(5),e._m(6),i("ul",[i("li",[i("a",{attrs:{href:"http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("5.1.3 SP-Initiated SSO: POST/Artifact Bindings"),i("OutboundLink")],1),e._v(" (Direct link does not work, scroll to section 5.1.3)")]),i("li",[i("a",{attrs:{href:"http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-tech-overview-2.0.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("5.1.4 IdP-Initiated SSO: POST Binding"),i("OutboundLink")],1),e._v(" (Direct link does not work, scroll to section 5.1.4)")])])])},[function(){var e=this.$createElement,t=this._self._c||e;return t("h2",{attrs:{id:"service-provider-sp-initiated"}},[t("a",{staticClass:"header-anchor",attrs:{href:"#service-provider-sp-initiated","aria-hidden":"true"}},[this._v("#")]),this._v(" Service Provider (SP) Initiated")])},function(){var e=this.$createElement,t=this._self._c||e;return t("h2",{attrs:{id:"identity-provider-idp-initiated"}},[t("a",{staticClass:"header-anchor",attrs:{href:"#identity-provider-idp-initiated","aria-hidden":"true"}},[this._v("#")]),this._v(" Identity Provider (IdP) Initiated")])},function(){var e=this.$createElement,t=this._self._c||e;return t("p",[this._v("Initiating SSO via the Identity Provider is similar to the Service Provider but "),t("em",[this._v("the user must initiate the process")]),this._v(".\nThis can give a poor user experience in certain situations. IdP initiated SSO works by sending the user (using whatever\nmeans chosen) to an endpoint/URL on the IdP site. This skips the AuthnRequest message by taking the users to a SP specific\nURL on the IdP (it is SP aware), builds the Response message, and redirects the user to the Assertion Consumer Service.")])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{pre:!0},[t("p",[this._v("You can find the URL to initiate login under the 'Login/Logout Paths' on the 'Configure' tab, labeled 'Login Path'. The\nvalue will be a relative URL looking something like the following: "),t("code",[this._v("/sso/login/request/<SP-UID>")]),this._v(". Obviously, add the\nIdP/Craft host as needed.")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("h3",{attrs:{id:"relaystate"}},[t("a",{staticClass:"header-anchor",attrs:{href:"#relaystate","aria-hidden":"true"}},[this._v("#")]),this._v(" RelayState")])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{pre:!0},[t("p",[this._v("RelayState is still available via IdP initiated SSO. You may pass a normal string (not base 64'd, ie,\n"),t("code",[this._v("/my/path-to/account")]),this._v(") for easy of use or a base 64'd string. An example would be\n"),t("code",[this._v("https://<idp hostname>/sso/login/request/<SP-UID>?RelayState=/my/path-to/account")])])])},function(){var e=this.$createElement,t=this._self._c||e;return t("h2",{attrs:{id:"reference"}},[t("a",{staticClass:"header-anchor",attrs:{href:"#reference","aria-hidden":"true"}},[this._v("#")]),this._v(" Reference")])}],!1,null,null,null);t.default=r.exports}}]);