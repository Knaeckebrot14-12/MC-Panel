import http from '@/api/http';

export interface GeyserStatus {
    supported: boolean;
    installed: boolean;
    configured: boolean;
    port: number | null;
    address: string | null;
}

export const getGeyser = async (uuid: string): Promise<GeyserStatus> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/geyser`);

    return data;
};

// Downloads two plugins and may restart the server, so it gets more time than a normal request.
export const installGeyser = async (uuid: string): Promise<GeyserStatus & { viaversion_for: string | null }> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/geyser`, {}, { timeout: 300000 });

    return data;
};

export const uninstallGeyser = async (uuid: string): Promise<GeyserStatus> => {
    const { data } = await http.delete(`/api/client/servers/${uuid}/geyser`);

    return data;
};
