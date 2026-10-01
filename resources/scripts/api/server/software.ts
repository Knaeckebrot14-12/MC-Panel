import http from '@/api/http';

export type SoftwareType = 'paper' | 'purpur' | 'folia' | 'fabric' | 'vanilla' | 'velocity';

export interface InstalledSoftware {
    type: SoftwareType;
    version: string;
    build: string | null;
    java: number;
    installed_at: string;
}

export interface SoftwareOverview {
    supported: boolean;
    current: InstalledSoftware | null;
    types: SoftwareType[];
    backup: { allowed: boolean; full: boolean };
}

export interface SoftwareVersion {
    id: string;
    java: number;
}

export const getSoftware = async (uuid: string): Promise<SoftwareOverview> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/software`);

    return data;
};

export const getSoftwareVersions = async (uuid: string, type: SoftwareType): Promise<SoftwareVersion[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/software/${type}`);

    return data.versions || [];
};

export const installSoftware = async (
    uuid: string,
    type: SoftwareType,
    version: string,
    backup = false
): Promise<InstalledSoftware & { image: string }> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/software`, { type, version, backup }, { timeout: 900000 });

    return data;
};
