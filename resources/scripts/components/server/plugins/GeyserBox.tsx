import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Can from '@/components/elements/Can';
import { Dialog } from '@/components/elements/dialog';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { getGeyser, installGeyser, uninstallGeyser, GeyserStatus } from '@/api/server/geyser';

/**
 * Bedrock players through Geyser + Floodgate, on the server's own port (UDP).
 */
export default ({ onChange }: { onChange?: () => void }) => {
    const { t } = useTranslation('server_plugins');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [status, setStatus] = useState<GeyserStatus | null>(null);
    const [busy, setBusy] = useState(false);
    const [confirmRemove, setConfirmRemove] = useState(false);

    useEffect(() => {
        getGeyser(uuid)
            .then(setStatus)
            .catch(() => setStatus(null));
    }, []);

    if (!status) return null;

    const run = (action: () => Promise<GeyserStatus & { viaversion_for?: string | null }>, message: string) => {
        clearFlashes('plugins');
        setBusy(true);
        action()
            .then((next) => {
                setStatus(next);
                const via = next.viaversion_for ? ' ' + t('geyser.via_note', { version: next.viaversion_for }) : '';
                addFlash({ key: 'plugins', type: 'success', message: message + via });
                onChange?.();
            })
            .catch((error) => addError({ key: 'plugins', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    return (
        <TitledGreyBox title={t('geyser.title')} css={tw`mb-6`}>
            <Dialog.Confirm
                open={confirmRemove}
                onClose={() => setConfirmRemove(false)}
                title={t('geyser.remove_title')}
                confirm={t('geyser.uninstall')}
                onConfirmed={() => {
                    setConfirmRemove(false);
                    run(() => uninstallGeyser(uuid), t('geyser.removed_ok'));
                }}
            >
                {t('geyser.remove_body')} {t('geyser.restart_note')}
            </Dialog.Confirm>

            <p css={tw`text-sm text-neutral-300`}>{t('geyser.description')}</p>

            {!status.supported ? (
                <p css={tw`text-sm text-yellow-400 mt-3`}>{t('geyser.unsupported')}</p>
            ) : (
                <>
                    {status.installed && (
                        <div css={tw`mt-3 text-sm space-y-1`}>
                            {status.failed ? (
                                <p css={tw`text-red-400`}>{t('geyser.failed', { version: status.minecraft || '?' })}</p>
                            ) : status.configured ? (
                                status.port && (
                                    <p css={tw`text-neutral-100`}>
                                        {t('geyser.join_hint', {
                                            address: status.address || '—',
                                            port: status.port,
                                        })}
                                    </p>
                                )
                            ) : (
                                <p css={tw`text-yellow-400`}>{t('geyser.not_configured')}</p>
                            )}
                            {status.port && (
                                <p css={tw`text-xs text-neutral-400`}>
                                    {t('geyser.firewall_hint', { port: status.port })}
                                </p>
                            )}
                        </div>
                    )}
                    <Can action={['file.create', 'file.update']}>
                        <div css={tw`flex flex-wrap items-center gap-2 mt-4`}>
                            {status.installed && (
                                <span css={tw`text-xs px-2 py-1 rounded bg-green-600 text-green-50`}>
                                    {t('geyser.installed')}
                                </span>
                            )}
                            <Button
                                type={'button'}
                                size={'xsmall'}
                                isLoading={busy}
                                disabled={busy}
                                onClick={() =>
                                    run(() => installGeyser(uuid), t('geyser.installed_ok'))
                                }
                            >
                                {status.installed ? t('geyser.update') : t('geyser.install')}
                            </Button>
                            {status.installed && (
                                <Can action={'file.delete'}>
                                    <Button
                                        type={'button'}
                                        size={'xsmall'}
                                        color={'red'}
                                        isSecondary
                                        disabled={busy}
                                        onClick={() => setConfirmRemove(true)}
                                    >
                                        {t('geyser.uninstall')}
                                    </Button>
                                </Can>
                            )}
                            <span css={tw`text-xs text-neutral-400`}>{t('geyser.restart_note')}</span>
                        </div>
                    </Can>
                </>
            )}
        </TitledGreyBox>
    );
};
