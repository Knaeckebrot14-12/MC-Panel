import React from 'react';
import { useTranslation } from 'react-i18next';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import SubdomainBox from '@/components/server/network/SubdomainBox';

/**
 * Own tab for the server's subdomain (Minecraft servers only, when Admin -> Settings -> Subdomains is set up).
 */
export default () => {
    const { t } = useTranslation('server_subdomain');

    return (
        <ServerContentBlock showFlashKey={'server:subdomain'} title={t('title')}>
            <SubdomainBox />
        </ServerContentBlock>
    );
};
