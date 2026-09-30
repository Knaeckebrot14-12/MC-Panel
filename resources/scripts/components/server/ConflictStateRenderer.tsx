import React from 'react';
import { useTranslation } from 'react-i18next';
import { ServerContext } from '@/state/server';
import ScreenBlock from '@/components/elements/ScreenBlock';
import ServerInstallSvg from '@/assets/images/server_installing.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import ServerRestoreSvg from '@/assets/images/server_restore.svg';

export default () => {
    const { t } = useTranslation('server_console');
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isNodeUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isNodeUnderMaintenance || false
    );

    return status === 'installing' || status === 'install_failed' || status === 'reinstall_failed' ? (
        <ScreenBlock
            title={t('conflict_state.installing_title')}
            image={ServerInstallSvg}
            message={t('conflict_state.installing_message')}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={t('conflict_state.suspended_title')}
            image={ServerErrorSvg}
            message={t('conflict_state.suspended_message')}
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={t('conflict_state.maintenance_title')}
            image={ServerErrorSvg}
            message={t('conflict_state.maintenance_message')}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? t('conflict_state.transferring_title') : t('conflict_state.restoring_title')}
            image={ServerRestoreSvg}
            message={
                isTransferring ? t('conflict_state.transferring_message') : t('conflict_state.restoring_message')
            }
        />
    );
};
