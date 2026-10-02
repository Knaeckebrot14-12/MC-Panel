import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faFingerprint } from '@fortawesome/free-solid-svg-icons';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { loginWithPasskey, passkeyErrorKey, passkeysSupported } from '@/api/account/passkeys';

/**
 * "Sign in with passkey" on the login page. Works without a username: the browser offers the
 * passkeys it has for this site and the server logs the user in once the signature checks out.
 */
export default ({ disabled }: { disabled?: boolean }) => {
    const { t } = useTranslation('passkeys');
    const { clearFlashes, addFlash, clearAndAddHttpError } = useFlash();
    const [busy, setBusy] = useState(false);

    if (!passkeysSupported()) {
        return null;
    }

    const onClick = () => {
        clearFlashes();
        setBusy(true);

        loginWithPasskey()
            .then((intended) => {
                // @ts-expect-error this is valid
                window.location = intended;
            })
            .catch((error) => {
                console.error(error);
                setBusy(false);

                const key = passkeyErrorKey(error);
                if (key) {
                    addFlash({
                        type: 'error',
                        title: t('title'),
                        message: t(key === 'already_registered' ? 'failed' : key),
                    });
                } else {
                    clearAndAddHttpError({ error });
                }
            });
    };

    return (
        <div css={tw`mt-2`}>
            <Button
                type={'button'}
                size={'xlarge'}
                isSecondary
                isLoading={busy}
                disabled={busy || disabled}
                onClick={onClick}
                css={tw`text-primary-500 border-primary-500 hover:text-primary-50 hover:bg-primary-500 hover:border-primary-600`}
            >
                <FontAwesomeIcon icon={faFingerprint} css={tw`mr-2`} />
                {t('login')}
            </Button>
        </div>
    );
};
