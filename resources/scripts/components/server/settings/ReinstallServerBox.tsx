import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import reinstallServer from '@/api/server/reinstallServer';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';

export default () => {
    const { t } = useTranslation('server_settings');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const skipScripts = ServerContext.useStoreState((state) => state.server.data!.skipScripts);
    const [modalVisible, setModalVisible] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const reinstall = () => {
        clearFlashes('settings');
        reinstallServer(uuid)
            .then(() => {
                addFlash({
                    key: 'settings',
                    type: 'success',
                    message: t('reinstall.success_message'),
                });
            })
            .catch((error) => {
                console.error(error);

                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setModalVisible(false));
    };

    useEffect(() => {
        clearFlashes();
    }, []);

    if (skipScripts) {
        return (
            <TitledGreyBox title={t('reinstall.heading')}>
                <p css={tw`text-sm`}>{t('reinstall.disabled_notice')}</p>
            </TitledGreyBox>
        );
    }

    return (
        <TitledGreyBox title={t('reinstall.heading')} css={tw`relative`}>
            <Dialog.Confirm
                open={modalVisible}
                title={t('reinstall.confirm_title')}
                confirm={t('reinstall.confirm_button')}
                onClose={() => setModalVisible(false)}
                onConfirmed={reinstall}
            >
                {t('reinstall.confirm_body')}
            </Dialog.Confirm>
            <p css={tw`text-sm`}>
                {t('reinstall.body')}&nbsp;
                <strong css={tw`font-medium`}>{t('reinstall.body_warning')}</strong>
            </p>
            <div css={tw`mt-6 text-right`}>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => setModalVisible(true)}>
                    {t('reinstall.reinstall_button')}
                </Button.Danger>
            </div>
        </TitledGreyBox>
    );
};
