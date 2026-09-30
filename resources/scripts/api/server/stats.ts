import http from '@/api/http';

export interface StatPoint {
    t: string;
    cpu: number;
    memory: number;
    players: number | null;
}

export interface ServerStats {
    range: '24h' | '7d';
    memoryLimit: number | null;
    points: StatPoint[];
}

export default async (uuid: string, range: '24h' | '7d'): Promise<ServerStats> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/stats`, { params: { range } });

    return { range: data.range, memoryLimit: data.memory_limit, points: data.points };
};
