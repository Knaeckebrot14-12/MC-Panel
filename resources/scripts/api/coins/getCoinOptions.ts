import http from '@/api/http';

export interface ShopPlan {
    id: number;
    name: string;
    description: string | null;
    memory: number;
    disk: number;
    cpu: number;
    backups: number;
    monthlyPrice: number;
}

export interface CoinOptions {
    balance: number;
    linkvertise: {
        available: boolean;
        reward: number;
    };
    daily: {
        enabled: boolean;
        available: boolean;
        reward: number;
        streak: number;
        nextStreak: number;
    };
    referral: {
        enabled: boolean;
        code: string | null;
        referrerReward: number;
        referredBonus: number;
        invited: number;
        active: number;
    };
    afk: {
        rewardPerMinute: number;
        adSlotHtml: string | null;
    };
    shop: {
        memoryUnitMib: number;
        memoryPrice: number;
        diskUnitMib: number;
        diskPrice: number;
        cpuUnitPercent: number;
        cpuPrice: number;
        backupPrice: number;
        slotPrice: number;
        server: {
            monthlyPrice: number;
            memory: number;
            disk: number;
            cpu: number;
            backups: number;
            plans: ShopPlan[];
        };
    };
}

export default (): Promise<CoinOptions> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/coins')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
