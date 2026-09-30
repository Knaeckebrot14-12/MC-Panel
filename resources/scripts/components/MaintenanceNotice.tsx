import React from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faSignOutAlt, faTools } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import http from '@/api/http';
import { SiteSettings } from '@/state/settings';

type Maintenance = NonNullable<SiteSettings['maintenance']>;

/**
 * Strip shown on every page while maintenance is announced (and, for the team, while the panel is locked).
 */
export const MaintenanceBanner = ({ maintenance, staffView }: { maintenance: Maintenance; staffView?: boolean }) => {
    const { t } = useTranslation('maintenance');

    return (
        <div
            css={[
                tw`w-full px-4 py-2 text-sm text-center flex items-center justify-center`,
                maintenance.mode === 'lock' ? tw`bg-red-600 text-red-50` : tw`bg-yellow-600 text-yellow-50`,
            ]}
        >
            <FontAwesomeIcon icon={faTools} css={tw`mr-2 flex-shrink-0`} />
            <span css={tw`whitespace-pre-line`}>
                {staffView ? t('staff_notice') + ' ' : ''}
                {maintenance.message || t('default_message')}
            </span>
        </div>
    );
};

/**
 * Replaces the whole panel for regular users while it is locked for maintenance.
 */
export const MaintenanceScreen = ({ maintenance, statusPage }: { maintenance: Maintenance; statusPage: boolean }) => {
    const { t } = useTranslation('maintenance');

    const logout = () => {
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    return (
        <div css={tw`min-h-screen flex items-center justify-center p-6`}>
            <div
                css={tw`max-w-lg w-full bg-neutral-800 border border-neutral-700 rounded-lg p-8 text-center shadow-lg`}
            >
                <FontAwesomeIcon icon={faTools} css={tw`text-4xl text-yellow-400 mb-4`} />
                <h1 css={tw`text-2xl font-header text-neutral-50 mb-3`}>{t('title')}</h1>
                <p css={tw`text-neutral-300 whitespace-pre-line`}>{maintenance.message || t('default_message')}</p>
                <p css={tw`text-sm text-neutral-400 mt-4`}>{t('servers_running')}</p>
                <div css={tw`mt-6 flex items-center justify-center gap-4 text-sm`}>
                    {statusPage && (
                        <a href={'/status'} css={tw`text-primary-300 hover:text-primary-200`}>
                            {t('status_link')}
                        </a>
                    )}
                    <button type={'button'} onClick={logout} css={tw`text-neutral-400 hover:text-neutral-200`}>
                        <FontAwesomeIcon icon={faSignOutAlt} css={tw`mr-1`} />
                        {t('logout')}
                    </button>
                </div>
            </div>
        </div>
    );
};
