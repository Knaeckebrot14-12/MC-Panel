import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import FlashMessageRender from '@/components/FlashMessageRender';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Select from '@/components/elements/Select';
import Spinner from '@/components/elements/Spinner';
import { Alert } from '@/components/elements/alert';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import {
    getSoftware,
    getSoftwareVersions,
    installSoftware,
    SoftwareOverview,
    SoftwareType,
    SoftwareVersion,
} from '@/api/server/software';

// Icons are plain coloured initials so no third-party logos are needed.
const COLORS: Record<SoftwareType, string> = {
    paper: '#e5e7eb',
    purpur: '#c084fc',
    folia: '#4ade80',
    fabric: '#dbd0b4',
    vanilla: '#86efac',
    velocity: '#60a5fa',
};

export default () => {
    const { t } = useTranslation('server_software');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [overview, setOverview] = useState<SoftwareOverview | null>(null);
    const [type, setType] = useState<SoftwareType>('paper');
    const [versions, setVersions] = useState<SoftwareVersion[] | null>(null);
    const [version, setVersion] = useState('');
    const [installing, setInstalling] = useState(false);
    const [confirm, setConfirm] = useState(false);
    const [backupFirst, setBackupFirst] = useState(false);

    useEffect(() => {
        getSoftware(uuid)
            .then((data) => {
                setOverview(data);
                // On by default, unless there is no free backup slot (a backup is never made by deleting another one).
                setBackupFirst(data.backup.allowed && !data.backup.full);
                if (data.current) setType(data.current.type);
            })
            .catch((error) => addError({ key: 'software', message: httpErrorToHuman(error) }));
    }, []);

    useEffect(() => {
        if (!overview?.supported) return;
        setVersions(null);
        setVersion('');
        clearFlashes('software');
        getSoftwareVersions(uuid, type)
            .then((list) => {
                setVersions(list);
                setVersion(list[0]?.id || '');
            })
            .catch((error) => addError({ key: 'software', message: httpErrorToHuman(error) }));
    }, [type, overview?.supported]);

    const install = () => {
        clearFlashes('software');
        setConfirm(false);
        setInstalling(true);
        installSoftware(uuid, type, version, backupFirst)
            .then((result) => {
                setOverview((o) => (o ? { ...o, current: result } : o));
                // The Startup tab shows the image from this state; keep it in step with the new Java image.
                setServerFromState((s) => ({ ...s, dockerImage: result.image }));
                addFlash({
                    key: 'software',
                    type: 'success',
                    message: t('installed', { name: t(`types.${type}.name`), version, java: result.java }),
                });
            })
            .catch((error) => addError({ key: 'software', message: httpErrorToHuman(error) }))
            .then(() => setInstalling(false));
    };

    const selected = versions?.find((v) => v.id === version);
    const current = overview?.current;
    const downgrade = !!current && current.type !== 'velocity' && type !== 'velocity' && compare(version, current.version) < 0;

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'software'} css={tw`mb-4`} />
            {!overview ? (
                <Spinner size={'large'} centered />
            ) : !overview.supported ? (
                <Alert type={'warning'}>{t('unsupported')}</Alert>
            ) : (
                <>
                    <TitledGreyBox title={t('current_title')} css={tw`mb-6`}>
                        {current ? (
                            <p css={tw`text-sm`}>
                                <strong>{t(`types.${current.type}.name`)}</strong> {current.version}
                                {current.build && current.build !== 'latest' ? ` (Build ${current.build})` : ''} ·
                                Java {current.java} · {new Date(current.installed_at).toLocaleString()}
                            </p>
                        ) : (
                            <p css={tw`text-sm text-neutral-300`}>{t('current_unknown')}</p>
                        )}
                    </TitledGreyBox>

                    <div css={tw`grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6`}>
                        {overview.types.map((option) => (
                            <button
                                key={option}
                                type={'button'}
                                onClick={() => setType(option)}
                                disabled={installing}
                                css={[
                                    tw`rounded border-2 bg-neutral-700 p-3 text-left transition-colors duration-150 hover:border-neutral-400`,
                                    option === type ? tw`border-primary-400` : tw`border-transparent`,
                                ]}
                            >
                                <span
                                    css={tw`inline-flex w-8 h-8 rounded items-center justify-center font-bold mb-2 border border-black border-opacity-10`}
                                    style={{ background: COLORS[option], color: '#111827' }}
                                >
                                    {t(`types.${option}.name`).charAt(0)}
                                </span>
                                <p css={tw`text-sm font-medium text-neutral-100`}>{t(`types.${option}.name`)}</p>
                                <p css={tw`text-xs text-neutral-400 leading-tight`}>{t(`types.${option}.description`)}</p>
                            </button>
                        ))}
                    </div>

                    <TitledGreyBox title={t('install_title', { name: t(`types.${type}.name`) })}>
                        {!versions ? (
                            <Spinner size={'small'} centered />
                        ) : versions.length === 0 ? (
                            <p css={tw`text-sm text-neutral-300`}>{t('no_versions')}</p>
                        ) : (
                            <>
                                <div css={tw`flex flex-wrap items-end gap-4`}>
                                    <div css={tw`flex-1`} style={{ minWidth: '12rem' }}>
                                        <label css={tw`block text-xs uppercase text-neutral-300 mb-1`}>{t('version_label')}</label>
                                        <Select value={version} onChange={(e) => setVersion(e.currentTarget.value)} disabled={installing}>
                                            {versions.map((v) => (
                                                <option key={v.id} value={v.id}>
                                                    {v.id}
                                                </option>
                                            ))}
                                        </Select>
                                    </div>
                                    <Button
                                        type={'button'}
                                        isLoading={installing}
                                        disabled={installing || !version}
                                        onClick={() => setConfirm(true)}
                                    >
                                        {t('install_button')}
                                    </Button>
                                </div>
                                {selected && <p css={tw`text-xs text-neutral-400 mt-2`}>{t('java_hint', { java: selected.java })}</p>}
                                {overview.backup.allowed && (
                                    <label css={tw`flex items-center mt-4 text-sm cursor-pointer`}>
                                        <input
                                            type={'checkbox'}
                                            checked={backupFirst}
                                            disabled={installing}
                                            onChange={(e) => setBackupFirst(e.currentTarget.checked)}
                                            css={tw`mr-2`}
                                        />
                                        {t('backup_first')}
                                    </label>
                                )}
                                {overview.backup.allowed && overview.backup.full && (
                                    <p css={tw`text-xs text-yellow-400 mt-1`}>{t('backup_full_note')}</p>
                                )}
                                {installing && <p css={tw`text-sm text-neutral-200 mt-4`}>{backupFirst ? t('installing_backup') : t('installing')}</p>}
                                {confirm && (
                                    <div css={tw`mt-4`}>
                                        <Alert type={downgrade ? 'danger' : 'warning'}>
                                            <div>
                                                <p>{t(downgrade ? 'confirm_downgrade' : 'confirm', { name: t(`types.${type}.name`), version })}</p>
                                                <div css={tw`mt-3 flex gap-2`}>
                                                    <Button type={'button'} size={'xsmall'} color={downgrade ? 'red' : 'primary'} onClick={install}>
                                                        {t('confirm_yes')}
                                                    </Button>
                                                    <Button type={'button'} size={'xsmall'} isSecondary onClick={() => setConfirm(false)}>
                                                        {t('confirm_no')}
                                                    </Button>
                                                </div>
                                            </div>
                                        </Alert>
                                    </div>
                                )}
                            </>
                        )}
                    </TitledGreyBox>
                    {!overview.backup.allowed && <p css={tw`text-xs text-neutral-400 mt-4`}>{t('backup_hint')}</p>}
                </>
            )}
        </ServerContentBlock>
    );
};

/** Compares dotted version numbers ("1.20.4" < "1.21"). */
function compare(a: string, b: string): number {
    const pa = a.split(/[.-]/).map((x) => parseInt(x, 10) || 0);
    const pb = b.split(/[.-]/).map((x) => parseInt(x, 10) || 0);
    for (let i = 0; i < Math.max(pa.length, pb.length); i++) {
        if ((pa[i] || 0) !== (pb[i] || 0)) return (pa[i] || 0) - (pb[i] || 0);
    }
    return 0;
}
