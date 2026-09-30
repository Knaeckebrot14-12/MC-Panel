import http from '@/api/http';

export default (login: string, recaptchaData?: string): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/password', { login, 'g-recaptcha-response': recaptchaData })
            .then((response) => resolve(response.data.status || ''))
            .catch(reject);
    });
};
