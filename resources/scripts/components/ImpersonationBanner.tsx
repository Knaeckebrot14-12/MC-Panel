import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faUserSecret } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import http from '@/api/http';

/**
 * Shown the whole time a staff member is signed in as a user (support view), with the way back.
 */
export default ({ staff, user }: { staff: string; user: string }) => {
    const { t } = useTranslation('impersonation');
    const [busy, setBusy] = useState(false);

    const leave = () => {
        setBusy(true);
        http.post('/impersonate/leave')
            .then(({ data }) => window.location.assign(data.redirect || '/'))
            .catch(() => window.location.assign('/'));
    };

    return (
        <div
            css={tw`w-full bg-red-600 text-white text-sm px-4 py-2 flex flex-wrap items-center justify-center gap-x-4 gap-y-1`}
            role={'alert'}
        >
            <span>
                <FontAwesomeIcon icon={faUserSecret} css={tw`mr-2`} />
                {t('banner', { user, staff })}
            </span>
            <button
                type={'button'}
                onClick={leave}
                disabled={busy}
                css={tw`underline font-medium hover:text-red-100 disabled:opacity-50`}
            >
                {t('leave')}
            </button>
        </div>
    );
};
