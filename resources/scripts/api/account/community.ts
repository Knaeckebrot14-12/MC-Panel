import http from '@/api/http';

export const resendVerificationEmail = async (): Promise<void> => {
    await http.post('/api/client/account/verify-email');
};

export const unlinkDiscord = async (): Promise<void> => {
    await http.delete('/api/client/account/discord');
};
