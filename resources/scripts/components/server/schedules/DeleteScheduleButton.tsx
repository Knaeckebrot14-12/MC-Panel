import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import deleteSchedule from '@/api/server/schedules/deleteSchedule';
import { ServerContext } from '@/state/server';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

interface Props {
    scheduleId: number;
    onDeleted: () => void;
}

export default ({ scheduleId, onDeleted }: Props) => {
    const { t } = useTranslation('server_schedules');
    const [visible, setVisible] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const onDelete = () => {
        setIsLoading(true);
        clearFlashes('schedules');
        deleteSchedule(uuid, scheduleId)
            .then(() => {
                setIsLoading(false);
                onDeleted();
            })
            .catch((error) => {
                console.error(error);

                addError({ key: 'schedules', message: httpErrorToHuman(error) });
                setIsLoading(false);
                setVisible(false);
            });
    };

    return (
        <>
            <Dialog.Confirm
                open={visible}
                onClose={() => setVisible(false)}
                title={t('delete_schedule.title')}
                confirm={t('delete_schedule.confirm')}
                onConfirmed={onDelete}
            >
                <SpinnerOverlay visible={isLoading} />
                {t('delete_schedule.body')}
            </Dialog.Confirm>
            <Button.Danger
                variant={Button.Variants.Secondary}
                className={'flex-1 sm:flex-none mr-4 border-transparent'}
                onClick={() => setVisible(true)}
            >
                {t('delete_button')}
            </Button.Danger>
        </>
    );
};
