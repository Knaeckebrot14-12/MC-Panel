import React, { lazy } from 'react';
import ServerConsole from '@/components/server/console/ServerConsoleContainer';
import DatabasesContainer from '@/components/server/databases/DatabasesContainer';
import ScheduleContainer from '@/components/server/schedules/ScheduleContainer';
import UsersContainer from '@/components/server/users/UsersContainer';
import BackupContainer from '@/components/server/backups/BackupContainer';
import NetworkContainer from '@/components/server/network/NetworkContainer';
import StartupContainer from '@/components/server/startup/StartupContainer';
import FileManagerContainer from '@/components/server/files/FileManagerContainer';
import PluginsContainer from '@/components/server/plugins/PluginsContainer';
import PlayersContainer from '@/components/server/players/PlayersContainer';
import PropertiesContainer from '@/components/server/properties/PropertiesContainer';
import SettingsContainer from '@/components/server/settings/SettingsContainer';
import AccountOverviewContainer from '@/components/dashboard/AccountOverviewContainer';
import EarnCoinsContainer from '@/components/dashboard/coins/EarnCoinsContainer';
import AfkContainer from '@/components/dashboard/coins/AfkContainer';
import ShopContainer from '@/components/dashboard/coins/ShopContainer';
import HistoryContainer from '@/components/dashboard/coins/HistoryContainer';
import TicketsContainer from '@/components/dashboard/tickets/TicketsContainer';
import NewTicketContainer from '@/components/dashboard/tickets/NewTicketContainer';
import TicketViewContainer from '@/components/dashboard/tickets/TicketViewContainer';
import AccountApiContainer from '@/components/dashboard/AccountApiContainer';
import AccountSSHContainer from '@/components/dashboard/ssh/AccountSSHContainer';
import ActivityLogContainer from '@/components/dashboard/activity/ActivityLogContainer';
import ServerActivityLogContainer from '@/components/server/ServerActivityLogContainer';
import { Server } from '@/api/server/getServer';

// Each of the router files is already code split out appropriately — so
// all of the items above will only be loaded in when that router is loaded.
//
// These specific lazy loaded routes are to avoid loading in heavy screens
// for the server dashboard when they're only needed for specific instances.
const FileEditContainer = lazy(() => import('@/components/server/files/FileEditContainer'));
const ScheduleEditContainer = lazy(() => import('@/components/server/schedules/ScheduleEditContainer'));

interface RouteDefinition {
    path: string;
    // If undefined is passed this route is still rendered into the router itself
    // but no navigation link is displayed in the sub-navigation menu.
    name: string | undefined;
    component: React.ComponentType;
    exact?: boolean;
}

interface ServerRouteDefinition extends RouteDefinition {
    permission: string | string[] | null;
    // Optional check run against the loaded server; when present and it
    // returns false the route is hidden from navigation and not rendered,
    // for routes that only make sense for certain egg types.
    condition?: (server: Server) => boolean;
}

// True for eggs that expose a Minecraft version variable, which is how the
// stock Minecraft eggs (and any custom egg modeled after them) identify
// themselves — there's no dedicated "is this Minecraft" flag on the server
// resource itself.
export const isMinecraftServer = (server: Server): boolean =>
    server.variables.some((variable) => variable.envVariable === 'MINECRAFT_VERSION');

interface Routes {
    // All of the routes available under "/account"
    account: RouteDefinition[];
    // All of the routes available under "/coins"
    coins: RouteDefinition[];
    // All of the routes available under "/tickets"
    tickets: RouteDefinition[];
    // All of the routes available under "/server/:id"
    server: ServerRouteDefinition[];
}

// `name` values below are i18next keys (namespace `navigation`, under `tabs.`),
// resolved with `t()` wherever a route's tab label is actually rendered —
// this file itself isn't a component and can't use translation hooks.
export default {
    tickets: [
        {
            path: '/',
            name: 'tabs.tickets',
            component: TicketsContainer,
            exact: true,
        },
        {
            path: '/new',
            name: 'tabs.tickets_new',
            component: NewTicketContainer,
            exact: true,
        },
        {
            path: '/:id([0-9]+)',
            name: undefined,
            component: TicketViewContainer,
            exact: true,
        },
    ],
    coins: [
        {
            path: '/earn',
            name: 'tabs.coins_earn',
            component: EarnCoinsContainer,
            exact: true,
        },
        {
            path: '/afk',
            name: 'tabs.coins_afk',
            component: AfkContainer,
            exact: true,
        },
        {
            path: '/shop',
            name: 'tabs.coins_shop',
            component: ShopContainer,
            exact: true,
        },
        {
            path: '/history',
            name: 'tabs.coins_history',
            component: HistoryContainer,
            exact: true,
        },
    ],
    account: [
        {
            path: '/',
            name: 'tabs.account',
            component: AccountOverviewContainer,
            exact: true,
        },
        {
            path: '/api',
            name: 'tabs.api_credentials',
            component: AccountApiContainer,
        },
        {
            path: '/ssh',
            name: 'tabs.ssh_keys',
            component: AccountSSHContainer,
        },
        {
            path: '/activity',
            name: 'tabs.activity',
            component: ActivityLogContainer,
        },
    ],
    server: [
        {
            path: '/',
            permission: null,
            name: 'tabs.console',
            component: ServerConsole,
            exact: true,
        },
        {
            path: '/files',
            permission: 'file.*',
            name: 'tabs.files',
            component: FileManagerContainer,
        },
        {
            path: '/files/:action(edit|new)',
            permission: 'file.*',
            name: undefined,
            component: FileEditContainer,
        },
        {
            path: '/plugins',
            permission: 'file.*',
            name: 'tabs.plugins',
            component: PluginsContainer,
            condition: isMinecraftServer,
        },
        {
            path: '/players',
            permission: 'file.*',
            name: 'tabs.players',
            component: PlayersContainer,
            condition: isMinecraftServer,
        },
        {
            path: '/properties',
            permission: 'file.*',
            name: 'tabs.properties',
            component: PropertiesContainer,
            condition: isMinecraftServer,
        },
        {
            path: '/databases',
            permission: 'database.*',
            name: 'tabs.databases',
            component: DatabasesContainer,
        },
        {
            path: '/schedules',
            permission: 'schedule.*',
            name: 'tabs.schedules',
            component: ScheduleContainer,
        },
        {
            path: '/schedules/:id',
            permission: 'schedule.*',
            name: undefined,
            component: ScheduleEditContainer,
        },
        {
            path: '/users',
            permission: 'user.*',
            name: 'tabs.users',
            component: UsersContainer,
        },
        {
            path: '/backups',
            permission: 'backup.*',
            name: 'tabs.backups',
            component: BackupContainer,
        },
        {
            path: '/network',
            permission: 'allocation.*',
            name: 'tabs.network',
            component: NetworkContainer,
        },
        {
            path: '/startup',
            permission: 'startup.*',
            name: 'tabs.startup',
            component: StartupContainer,
        },
        {
            path: '/settings',
            permission: ['settings.*', 'file.sftp'],
            name: 'tabs.settings',
            component: SettingsContainer,
        },
        {
            path: '/activity',
            permission: 'activity.*',
            name: 'tabs.activity',
            component: ServerActivityLogContainer,
        },
    ],
} as Routes;
