import http from '@/api/http';

export interface AfkTickResponse {
    credited: boolean;
    reason?: 'too_soon' | 'adblock_detected';
    reward?: number;
    balance: number;
    secondsUntilNextTick: number;
}

export default (adblockDetected: boolean): Promise<AfkTickResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/coins/afk', { adblockDetected })
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
