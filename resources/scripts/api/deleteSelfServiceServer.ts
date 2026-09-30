import http from '@/api/http';

export default (identifier: string): Promise<{ refund: number }> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/self-service/servers/${identifier}`)
            .then(({ data }) => resolve(data))
            .catch(reject);
    });
};
