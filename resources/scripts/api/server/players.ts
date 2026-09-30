import http from '@/api/http';

export interface PlayerEntry {
    uuid?: string;
    name?: string;
    ip?: string;
    level?: number;
    reason?: string;
    created?: string;
    source?: string;
}

export interface OnlineStatus {
    online: number;
    max: number;
    sample: { name: string; id: string }[];
    version: string | null;
}

export interface PlayerOverview {
    running: boolean;
    online: OnlineStatus | null;
    whitelistEnabled: boolean;
    onlineMode: boolean;
    lists: {
        whitelist: PlayerEntry[];
        ops: PlayerEntry[];
        bannedPlayers: PlayerEntry[];
        bannedIps: PlayerEntry[];
    };
}

export type PlayerAction =
    | 'whitelist_add'
    | 'whitelist_remove'
    | 'op'
    | 'deop'
    | 'ban'
    | 'pardon'
    | 'ban_ip'
    | 'pardon_ip'
    | 'kick'
    | 'whitelist_on'
    | 'whitelist_off';

export const getPlayers = async (uuid: string): Promise<PlayerOverview> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/players`);

    return {
        running: data.running,
        online: data.online,
        whitelistEnabled: data.whitelist_enabled,
        onlineMode: data.online_mode,
        lists: {
            whitelist: data.lists.whitelist || [],
            ops: data.lists.ops || [],
            bannedPlayers: data.lists.banned_players || [],
            bannedIps: data.lists.banned_ips || [],
        },
    };
};

export const runPlayerAction = async (
    uuid: string,
    action: PlayerAction,
    target?: string,
    reason?: string
): Promise<'command' | 'file'> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/players`, { action, target, reason });

    return data.via;
};
