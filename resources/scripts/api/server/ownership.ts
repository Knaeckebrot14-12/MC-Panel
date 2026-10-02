import http from '@/api/http';

export interface PendingOwnership {
    id: number;
    to: string;
    expires_at: string;
}

export interface IncomingOwnership {
    id: number;
    server: string;
    from: string;
    memory: number;
    disk: number;
    cpu: number;
    coins: { monthly: number; paid_until: string } | null;
    expires_at: string;
}

// The owner's side (Server settings). Anyone else gets a 403 and sees nothing.
export const getOwnershipOffer = async (uuid: string): Promise<PendingOwnership | null> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/ownership`);

    return data.pending;
};

export const offerOwnership = async (uuid: string, username: string): Promise<PendingOwnership | null> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/ownership`, { username });

    return data.pending;
};

export const withdrawOwnershipOffer = (uuid: string): Promise<void> =>
    http.delete(`/api/client/servers/${uuid}/ownership`).then(() => undefined);

// The recipient's side (Dashboard).
export const getIncomingOwnership = async (): Promise<IncomingOwnership[]> => {
    const { data } = await http.get('/api/client/ownership-requests');

    return data.data || [];
};

export const acceptOwnership = async (id: number): Promise<string> => {
    const { data } = await http.post(`/api/client/ownership-requests/${id}/accept`);

    return data.server;
};

export const declineOwnership = (id: number): Promise<void> =>
    http.post(`/api/client/ownership-requests/${id}/decline`).then(() => undefined);
