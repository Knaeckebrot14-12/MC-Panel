import http from '@/api/http';

export interface Subdomain {
    name: string;
    domain: string;
    fqdn: string;
}

export interface SubdomainOverview {
    enabled: boolean;
    domains: string[];
    current: Subdomain | null;
}

export const getSubdomain = async (uuid: string): Promise<SubdomainOverview> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/subdomain`);

    return data;
};

export const setSubdomain = async (uuid: string, name: string, domain: string): Promise<Subdomain> => {
    const { data } = await http.put(`/api/client/servers/${uuid}/subdomain`, { name, domain });

    return data;
};

export const deleteSubdomain = (uuid: string): Promise<void> =>
    http.delete(`/api/client/servers/${uuid}/subdomain`).then(() => undefined);
