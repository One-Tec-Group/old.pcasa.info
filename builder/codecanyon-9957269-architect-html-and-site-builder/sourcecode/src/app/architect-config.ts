import {environment} from '../environments/environment';

export const ARCHITECT_CONFIG = {
    assetsPrefix: 'client',
    environment: environment.production ? 'production' : 'dev',
    navbar: {
        defaultPosition: 'dashboard',
        dropdownItems: [
            {route: '/dashboard', name: 'Dashboard', icon: 'web-design-custom'},
        ]
    },
    auth: {
        color: 'primary',
        redirectUri: 'dashboard',
        adminRedirectUri: 'dashboard',
    },
    accountSettings: {
        hideNavbar: false,
    },
    customPages: {
        hideNavbar: false,
    },
    admin: {
        hideBilling: false,
        pages: [
            {name: 'templates', icon: 'web-design-custom', route: 'templates', permission: 'templates.view'},
            {name: 'projects', icon: 'dashboard', route: 'projects', permission: 'projects.view'},
        ],
        settingsPages: [
            {name: 'builder', route: 'builder'},
        ],
        analytics: {
          stats: [
              {name: 'users', icon: 'people'},
              {name: 'projects', icon: 'dashboard'},
              {name: 'templates', icon: 'web-design-custom'},
              {name: 'pages', icon: 'insert-drive-file'},
          ]
        },
        ads: [
            {slot: 'ads.dashboard_top', description: 'This will appear at the top of user dashboard.'},
            {slot: 'ads.dashboard_bottom', description: 'This will appear at the bottom of user dashboard.'},
        ],
        appearance: {
            defaultRoute: 'dashboard',
            navigationRoutes: [
                'dashboard',
                'dashboard/projects/new',
                'builder',
                'account/settings',
                'admin',
            ],
            menus: {
                availableRoutes:  [
                    'dashboard',
                    'dashboard/projects/new',
                ],
                positions: [
                    'dashboard',
                    'admin',
                    'custom-page-navbar',
                ]
            },
        }
    },
};
