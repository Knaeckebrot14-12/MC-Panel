import http from '@/api/http';

export interface CoinTransaction {
    amount: number;
    type: string;
    description: string | null;
    createdAt: string;
}

export interface CoinTransactionsResponse {
    data: CoinTransaction[];
    meta: {
        currentPage: number;
        lastPage: number;
        total: number;
    };
}

export default (page = 1): Promise<CoinTransactionsResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/coins/transactions', { params: { page } })
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
