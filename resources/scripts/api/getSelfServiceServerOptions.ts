import http from '@/api/http';

export interface ResourcePool {
    memory: number;
    disk: number;
    cpu: number;
    backups: number;
    slots: number;
}

export interface SelfServiceNode {
    id: number;
    name: string;
    servers: number;
    maximumServers: number | null;
}

export interface SelfServiceEgg {
    id: number;
    name: string;
}

export interface SelfServiceNest {
    id: number;
    name: string;
    eggs: SelfServiceEgg[];
}

export interface SelfServiceServer {
    identifier: string;
    name: string;
    memory: number;
    disk: number;
    cpu: number;
    backupLimit: number;
    paidWithCoinsUntil: string | null;
}

export interface SelfServiceServerOptions {
    limits: ResourcePool;
    used: ResourcePool;
    suspended: boolean;
    cooldownSecondsRemaining: number;
    servers: SelfServiceServer[];
    nodes: SelfServiceNode[];
    nests: SelfServiceNest[];
}

export default (): Promise<SelfServiceServerOptions> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/self-service/servers')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
