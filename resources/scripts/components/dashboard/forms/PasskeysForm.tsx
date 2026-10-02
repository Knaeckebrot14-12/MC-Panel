import React, { useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faFingerprint, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import asDialog from '@/hoc/asDialog';
import { Dialog, DialogWrapperContext } from '@/components/elements/dialog';
import { Button } from '@/components/elements/button/index';
import { Input } from '@/components/elements/inputs';
import ContentBox from '@/components/elements/ContentBox';
import GreyRowBox from '@/components/elements/GreyRowBox';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash, { useFlashKey } from '@/plugins/useFlash';
import {
    createPasskey,
    deletePasskey,
    getPasskeys,
    Passkey,
    passkeyErrorKey,
    passkeysSupported,
} from '@/api/account/passkeys';

const FLASH_KEY = 'account:passkeys';
const MAX_PASSKEYS = 10;

const RemovePasskeyDialog = asDialog()(({ passkey, onRemoved }: { passkey: Passkey; onRemoved: () => void }) => {
    const { t } = useTranslation('passkeys');
    const { close, setProps } = useContext(DialogWrapperContext);
    const { clearAndAddHttpError } = useFlashKey(`${FLASH_KEY}:remove`);
    const [password, setPassword] = useState('');
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        setProps((state) => ({
            ...state,
            preventExternalClose: submitting,
            title: t('remove_title'),
            description: t('remove_description', { name: passkey.name }),
        }));
    }, [submitting, passkey.name, t]);

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        e.stopPropagation();
        if (submitting || !password) return;

        setSubmitting(true);
        clearAndAddHttpError();
        deletePasskey(passkey.id, password)
            .then(() => {
                onRemoved();
                close();
            })
            .catch((error) => {
                clearAndAddHttpError(error);
                setSubmitting(false);
            });
    };

    return (
        <form id={`remove-passkey-${passkey.id}`} className={'mt-6'} onSubmit={submit}>
            <FlashMessageRender byKey={`${FLASH_KEY}:remove`} className={'-mt-2 mb-6'} />
            <label className={'block pb-1'} htmlFor={`remove-passkey-password-${passkey.id}`}>
                {t('password_label')}
            </label>
            <Input.Text
                id={`remove-passkey-password-${passkey.id}`}
                type={'password'}
                autoComplete={'current-password'}
                variant={Input.Text.Variants.Loose}
                value={password}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.currentTarget.value)}
            />
            <Dialog.Footer>
                <Button.Text onClick={close}>{t('cancel')}</Button.Text>
                <Button.Danger type={'submit'} form={`remove-passkey-${passkey.id}`} disabled={submitting || !password}>
                    {t('remove')}
                </Button.Danger>
            </Dialog.Footer>
        </form>
    );
});

export default ({ className }: { className?: string }) => {
    const { t, i18n } = useTranslation('passkeys');
    const { addFlash } = useFlash();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey(FLASH_KEY);
    const [passkeys, setPasskeys] = useState<Passkey[] | null>(null);
    const [name, setName] = useState('');
    const [password, setPassword] = useState('');
    const [busy, setBusy] = useState(false);
    const [removing, setRemoving] = useState<Passkey | null>(null);
    const supported = passkeysSupported();

    const load = () => getPasskeys().then(setPasskeys).catch(clearAndAddHttpError);

    useEffect(() => {
        load();
    }, []);

    const formatDate = (date: Date | null) => (date ? date.toLocaleString(i18n.language) : '');

    const add = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (busy || !name.trim() || !password) return;

        clearFlashes();
        setBusy(true);
        createPasskey(name.trim(), password)
            .then((passkey) => {
                setPasskeys((current) => (current || []).concat(passkey));
                setName('');
                setPassword('');
                addFlash({ key: FLASH_KEY, type: 'success', message: t('added_success') });
            })
            .catch((error) => {
                console.error(error);

                const key = passkeyErrorKey(error);
                if (key) {
                    addFlash({ key: FLASH_KEY, type: 'error', message: t(key) });
                } else {
                    clearAndAddHttpError(error);
                }
            })
            .then(() => setBusy(false));
    };

    const limitReached = (passkeys?.length || 0) >= MAX_PASSKEYS;

    return (
        <ContentBox className={className} title={t('title')}>
            <FlashMessageRender byKey={FLASH_KEY} css={tw`mb-4`} />
            <p css={tw`text-sm text-neutral-300`}>{t('intro')}</p>

            <div css={tw`relative mt-4`}>
                <SpinnerOverlay visible={!passkeys && !busy} />
                {!passkeys ? (
                    <p css={tw`text-center text-sm text-neutral-400 py-2`}>{t('loading')}</p>
                ) : passkeys.length === 0 ? (
                    <p css={tw`text-center text-sm text-neutral-400 py-2`}>{t('empty')}</p>
                ) : (
                    passkeys.map((passkey, index) => (
                        <GreyRowBox
                            key={passkey.id}
                            css={[tw`bg-neutral-600 flex space-x-4 items-center`, index > 0 && tw`mt-2`]}
                        >
                            <FontAwesomeIcon icon={faFingerprint} css={tw`text-neutral-300`} />
                            <div css={tw`flex-1 min-w-0`}>
                                <p css={tw`text-sm break-words font-medium`}>{passkey.name}</p>
                                <p css={tw`text-xs mt-1 text-neutral-300`}>
                                    {t('added', { date: formatDate(passkey.createdAt) })}
                                    {' · '}
                                    {passkey.lastUsedAt
                                        ? t('last_used', { date: formatDate(passkey.lastUsedAt) })
                                        : t('never_used')}
                                </p>
                            </div>
                            <button
                                type={'button'}
                                css={tw`ml-4 p-2 text-sm`}
                                title={t('remove')}
                                aria-label={t('remove')}
                                onClick={() => setRemoving(passkey)}
                            >
                                <FontAwesomeIcon
                                    icon={faTrashAlt}
                                    css={tw`text-neutral-400 hover:text-red-400 transition-colors duration-150`}
                                />
                            </button>
                        </GreyRowBox>
                    ))
                )}
            </div>

            {removing && (
                <RemovePasskeyDialog
                    open
                    passkey={removing}
                    onClose={() => setRemoving(null)}
                    onRemoved={() => setPasskeys((current) => (current || []).filter((p) => p.id !== removing.id))}
                />
            )}

            <div css={tw`mt-6 pt-4 border-t border-neutral-600`}>
                <h3 css={tw`text-neutral-200 text-lg mb-3`}>{t('add_title')}</h3>
                {!supported ? (
                    <p css={tw`text-sm text-neutral-400`}>{t('unsupported')}</p>
                ) : limitReached ? (
                    <p css={tw`text-sm text-neutral-400`}>{t('limit', { max: MAX_PASSKEYS })}</p>
                ) : (
                    <form onSubmit={add} css={tw`m-0`}>
                        <label css={tw`block text-xs uppercase text-neutral-200 mb-1`} htmlFor={'passkey_name'}>
                            {t('name_label')}
                        </label>
                        <Input.Text
                            id={'passkey_name'}
                            type={'text'}
                            maxLength={64}
                            placeholder={t('name_placeholder')}
                            value={name}
                            disabled={busy}
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setName(e.currentTarget.value)}
                        />
                        <p css={tw`text-xs text-neutral-400 mt-1`}>{t('name_hint')}</p>
                        <label
                            css={tw`block text-xs uppercase text-neutral-200 mb-1 mt-4`}
                            htmlFor={'passkey_password'}
                        >
                            {t('password_label')}
                        </label>
                        <Input.Text
                            id={'passkey_password'}
                            type={'password'}
                            autoComplete={'current-password'}
                            value={password}
                            disabled={busy}
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.currentTarget.value)}
                        />
                        <p css={tw`text-xs text-neutral-400 mt-1`}>{t('password_hint')}</p>
                        <div css={tw`mt-6`}>
                            <Button type={'submit'} disabled={busy || !name.trim() || !password}>
                                {t('add')}
                            </Button>
                        </div>
                    </form>
                )}
            </div>
        </ContentBox>
    );
};
