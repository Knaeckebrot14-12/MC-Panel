import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import EditSubuserModal from '@/components/server/users/EditSubuserModal';
import { Button } from '@/components/elements/button/index';

export default () => {
    const { t } = useTranslation('server_users');
    const [visible, setVisible] = useState(false);

    return (
        <>
            <EditSubuserModal visible={visible} onModalDismissed={() => setVisible(false)} />
            <Button onClick={() => setVisible(true)}>{t('new_user_button')}</Button>
        </>
    );
};
