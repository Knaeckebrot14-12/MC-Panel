import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCheckCircle, faEnvelope } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import { useStoreActions, useStoreState } from 'easy-peasy';
import ContentContainer from '@/components/elements/ContentContainer';
import Button from '@/components/elements/Button';
import { httpErrorToHuman } from '@/api/http';
import { resendVerificationEmail } from '@/api/account/community';

export default () => {
    const { t } = useTranslation('verification');
    const user = useStoreState((state) => state.user.data);
    const required = useStoreState((state) => !!state.settings.data?.verifyEmail);
    const updateUserData = useStoreActions((actions) => actions.user.updateUserData);
    const [state, setState] = useState<'idle' | 'sending' | 'sent'>('idle');
    const [error, setError] = useState('');
    const [justVerified, setJustVerified] = useState<'yes' | 'invalid' | null>(null);

    // The link in the mail lands on "/?verified=1".
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const verified = params.get('verified');
        if (!verified) return;

        setJustVerified(verified === '1' ? 'yes' : 'invalid');
        if (verified === '1') updateUserData({ emailVerified: true });

        params.delete('verified');
        const query = params.toString();
        window.history.replaceState(null, '', window.location.pathname + (query ? `?${query}` : ''));
    }, []);

    if (justVerified === 'yes') {
        return (
            <ContentContainer css={tw`mt-4`}>
                <div
                    css={tw`rounded border border-green-500 bg-green-500 bg-opacity-10 p-4 flex items-center text-sm text-green-100`}
                >
                    <FontAwesomeIcon icon={faCheckCircle} css={tw`text-green-400 mr-3`} />
                    {t('banner.verified')}
                </div>
            </ContentContainer>
        );
    }

    if (!user || user.staff || user.emailVerified || !required) {
        return null;
    }

    const resend = () => {
        setState('sending');
        setError('');
        resendVerificationEmail()
            .then(() => setState('sent'))
            .catch((e) => {
                setError(httpErrorToHuman(e));
                setState('idle');
            });
    };

    return (
        <ContentContainer css={tw`mt-4`}>
            <div
                css={tw`rounded border border-yellow-500 bg-yellow-500 bg-opacity-10 p-4 sm:flex items-center gap-4 text-sm`}
            >
                <FontAwesomeIcon icon={faEnvelope} css={tw`text-yellow-400 mr-3 hidden sm:block`} />
                <div css={tw`flex-1 text-yellow-100`}>
                    <p css={tw`font-medium`}>
                        {justVerified === 'invalid' ? t('banner.invalid') : t('banner.title', { email: user.email })}
                    </p>
                    <p css={tw`text-yellow-200 mt-1`}>{t('banner.body')}</p>
                    {error && <p css={tw`text-red-300 mt-1`}>{error}</p>}
                </div>
                <Button
                    size={'small'}
                    color={'grey'}
                    css={tw`mt-3 sm:mt-0 flex-shrink-0`}
                    disabled={state !== 'idle'}
                    isLoading={state === 'sending'}
                    onClick={resend}
                >
                    {state === 'sent' ? t('banner.sent') : t('banner.resend')}
                </Button>
            </div>
        </ContentContainer>
    );
};
