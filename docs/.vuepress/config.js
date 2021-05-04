module.exports = {
    title: 'SAML Identity Provider - Craft CMS SSO',
    description: 'SAML Identity Provider plugin for Craft CMS',
    base: '/',
    themeConfig: {
        logo: '/icon.svg',
        docsRepo: 'flipboxfactory/saml-idp',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            json: 'JSON',
            // any other languages you want to include in code toggles...
        },
        nav: [
            {text: 'Details', link: 'https://www.flipboxdigital.com/craft-cms-plugins/saml-identity-provider'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/saml-idp/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/saml-idp'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        ['/', 'Introduction'],
                        ['/installation', 'Installation / Upgrading'],
                        ['/configure', 'Configure'],
                        ['/initiating-sso', 'Initiating SSO'],
                        ['/access-control', 'Access Control'],
                        ['/support', 'Support'],
                    ]
                },
            ]
        }
    },
    markdown: {
        anchor: { level: [2, 3, 4] },
        toc: { includeLevel: [3] },
        config(md) {
            md.use(require('vuepress-theme-flipbox/markup'))
        }
    }
}
