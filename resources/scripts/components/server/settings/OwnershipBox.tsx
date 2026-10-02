import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Input from '@/components/elements/Input';
import Label from '@/components/elements/Label';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { getOwnershipOffer, offerOwnership, withdrawOwnershipOffer, PendingOwnership } from '@/api/server/ownership';

/**
 * Give the server to another user (only the owner sees this box).
 */
export default () => {
    const { t } = useTranslation('server_ownership');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [owner, setOwner] = useState(false);
    const [pending, setPending] = useState<PendingOwnership | null>(null);
    const [username, setUsername] = useState('');
    const [confirm, setConfirm] = useState(false);
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        getOwnershipOffer(uuid)
            .then((offer) => {
                setOwner(true);
                setPending(offer);
            })
            .catch(() => setOwner(false));
    }, []);

    if (!owner) return null;

    const send = () => {
        clearFlashes('settings');
        setConfirm(false);
        setBusy(true);
        offerOwnership(uuid, username.trim())
            .then((offer) => {
                setPending(offer);
                setUsername('');
                addFlash({ key: 'settings', type: 'success', message: t('offered', { user: offer?.to || '' }) });
            })
            .catch((error) => addError({ key: 'settings', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    const withdraw = () => {
        clearFlashes('settings');
        setBusy(true);
        withdrawOwnershipOffer(uuid)
            .then(() => {
                setPending(null);
                addFlash({ key: 'settings', type: 'success', message: t('cancelled') });
            })
            .catch((error) => addError({ key: 'settings', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    return (
        <TitledGreyBox title={t('title')} css={tw`mt-6 md:mt-10`}>
            <Dialog.Confirm
                open={confirm}
                onClose={() => setConfirm(false)}
                title={t('confirm_title')}
                confirm={t('send')}
                onConfirmed={send}
            >
                {t('confirm_body', { user: username.trim() })}
            </Dialog.Confirm>

            <p css={tw`text-sm text-neutral-300`}>{t('description')}</p>
            <p css={tw`text-xs text-neutral-400 mt-2`}>{t('coins_note')}</p>

            {pending ? (
                <div css={tw`mt-4 flex flex-wrap items-center justify-between gap-3`}>
                    <p css={tw`text-sm text-yellow-400`}>
                        {t('pending', { user: pending.to, date: new Date(pending.expires_at).toLocaleString() })}
                    </p>
                    <Button.Danger variant={Button.Variants.Secondary} disabled={busy} onClick={withdraw}>
                        {t('cancel')}
                    </Button.Danger>
                </div>
            ) : (
                <div css={tw`mt-4`}>
                    <Label htmlFor={'ownership-username'}>{t('username_label')}</Label>
                    <div css={tw`flex flex-wrap gap-2`}>
                        <div css={tw`flex-1`} style={{ minWidth: '12rem' }}>
                            <Input
                                id={'ownership-username'}
                                value={username}
                                maxLength={191}
                                placeholder={t('username_placeholder')}
                                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setUsername(e.currentTarget.value)}
                            />
                        </div>
                        <Button.Danger disabled={busy || username.trim().length < 1} onClick={() => setConfirm(true)}>
                            {t('send')}
                        </Button.Danger>
                    </div>
                </div>
            )}
        </TitledGreyBox>
    );
};
