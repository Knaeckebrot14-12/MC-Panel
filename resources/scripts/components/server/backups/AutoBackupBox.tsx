import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faHistory } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Select from '@/components/elements/Select';
import Can from '@/components/elements/Can';
import useFlash from '@/plugins/useFlash';
import { AutoBackupSettings, getAutoBackup, updateAutoBackup } from '@/api/server/backups/autoBackup';

export default () => {
    const { t, i18n } = useTranslation('server_backups');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearAndAddHttpError, clearFlashes, addFlash } = useFlash();
    const [settings, setSettings] = useState<AutoBackupSettings | null>(null);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        getAutoBackup(uuid)
            .then(setSettings)
            .catch(() => setSettings(null));
    }, []);

    if (!settings || settings.backupLimit <= 0) {
        return null;
    }

    const change = (hours: number) => {
        clearFlashes('backups');
        setSaving(true);
        updateAutoBackup(uuid, hours)
            .then((updated) => {
                setSettings(updated);
                addFlash({
                    key: 'backups',
                    type: 'success',
                    message: hours ? t('auto.saved_on') : t('auto.saved_off'),
                });
            })
            .catch((error) => clearAndAddHttpError({ key: 'backups', error }))
            .then(() => setSaving(false));
    };

    const format = (iso: string | null) =>
        iso ? new Date(iso).toLocaleString(i18n.language, { dateStyle: 'short', timeStyle: 'short' }) : '—';

    return (
        <TitledGreyBox
            title={
                <p css={tw`text-sm uppercase`}>
                    <FontAwesomeIcon icon={faHistory} css={tw`mr-2 text-neutral-300`} />
                    {t('auto.title')}
                </p>
            }
            css={tw`mb-6`}
        >
            <div css={tw`sm:flex items-start gap-4`}>
                <div css={tw`flex-1 text-sm text-neutral-300 mb-3 sm:mb-0`}>
                    <p>{t('auto.description', { limit: settings.backupLimit })}</p>
                    {settings.hours > 0 && (
                        <p css={tw`mt-2 text-xs text-neutral-400`}>
                            {t('auto.last', { time: format(settings.lastAt) })} ·{' '}
                            {t('auto.next', { time: format(settings.nextAt) })}
                        </p>
                    )}
                </div>
                <Can action={'backup.create'}>
                    <Select
                        css={tw`sm:w-56`}
                        value={settings.hours}
                        disabled={saving}
                        onChange={(e: React.ChangeEvent<HTMLSelectElement>) =>
                            change(parseInt(e.currentTarget.value, 10))
                        }
                        aria-label={t('auto.title')}
                    >
                        {settings.intervals.map((hours) => (
                            <option key={hours} value={hours}>
                                {t(`auto.intervals.${hours}`)}
                            </option>
                        ))}
                    </Select>
                </Can>
            </div>
        </TitledGreyBox>
    );
};
