import http from '@/api/http';

export const redeemVoucher = (code: string): Promise<{ coins: number; balance: number }> =>
    http.post('/api/client/coins/voucher', { code }).then(({ data }) => data);

export const claimDailyReward = (): Promise<{ reward: number; streak: number; balance: number }> =>
    http.post('/api/client/coins/daily').then(({ data }) => data);
