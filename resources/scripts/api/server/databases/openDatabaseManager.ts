import http from '@/api/http';

/**
 * Returns a one-time URL (valid for a minute) that opens phpMyAdmin signed in as this database's user.
 */
export default (uuid: string, database: string): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/databases/${database}/manager`)
            .then((response) => resolve(response.data.url))
            .catch(reject);
    });
};
