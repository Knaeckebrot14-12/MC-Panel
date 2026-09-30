import http from '@/api/http';

export default (): Promise<{ url: string; reward: number }> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/coins/linkvertise')
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
