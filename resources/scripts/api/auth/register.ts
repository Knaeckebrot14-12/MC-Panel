import http from '@/api/http';

export interface RegisterResponse {
    complete: boolean;
    intended?: string;
}

export interface RegisterData {
    nameFirst: string;
    nameLast: string;
    username: string;
    email: string;
    password: string;
    passwordConfirmation: string;
    recaptchaData?: string | null;
    referralCode?: string | null;
}

export default ({
    nameFirst,
    nameLast,
    username,
    email,
    password,
    passwordConfirmation,
    recaptchaData,
    referralCode,
}: RegisterData): Promise<RegisterResponse> => {
    return new Promise((resolve, reject) => {
        http.get('/sanctum/csrf-cookie')
            .then(() =>
                http.post('/auth/register', {
                    name_first: nameFirst,
                    name_last: nameLast,
                    username,
                    email,
                    password,
                    password_confirmation: passwordConfirmation,
                    'g-recaptcha-response': recaptchaData,
                    referral_code: referralCode || undefined,
                })
            )
            .then((response) => {
                if (!(response.data instanceof Object)) {
                    return reject(new Error('An error occurred while processing the registration request.'));
                }

                return resolve({
                    complete: response.data.data.complete,
                    intended: response.data.data.intended || undefined,
                });
            })
            .catch(reject);
    });
};
