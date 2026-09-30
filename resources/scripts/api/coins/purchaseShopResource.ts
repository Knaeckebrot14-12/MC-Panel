import http from '@/api/http';

export type ShopResourceType = 'memory' | 'disk' | 'cpu' | 'backups' | 'slots';

export interface PurchaseResourceResponse {
    balance: number;
    limits: {
        memory: number;
        disk: number;
        cpu: number;
        backups: number;
        slots: number;
    };
}

export default (type: ShopResourceType, quantity: number): Promise<PurchaseResourceResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/coins/shop/resource', { type, quantity })
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
