import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBan, faCrown, faDoorOpen, faSyncAlt, faTimes, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import FlashMessageRender from '@/components/FlashMessageRender';
import GreyRowBox from '@/components/elements/GreyRowBox';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Spinner from '@/components/elements/Spinner';
import { Alert } from '@/components/elements/alert';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { getPlayers, PlayerAction, PlayerEntry, PlayerOverview, runPlayerAction } from '@/api/server/players';

type Tab = 'whitelist' | 'ops' | 'bannedPlayers' | 'bannedIps';

const TABS: { key: Tab; add: PlayerAction; remove: PlayerAction; ip?: boolean; reason?: boolean }[] = [
    { key: 'whitelist', add: 'whitelist_add', remove: 'whitelist_remove' },
    { key: 'ops', add: 'op', remove: 'deop' },
    { key: 'bannedPlayers', add: 'ban', remove: 'pardon', reason: true },
    { key: 'bannedIps', add: 'ban_ip', remove: 'pardon_ip', ip: true, reason: true },
];

const Avatar = ({ name }: { name?: string }) => (
    <img
        src={`https://mc-heads.net/avatar/${encodeURIComponent(name || 'MHF_Steve')}/32`}
        alt={''}
        css={tw`w-8 h-8 rounded flex-shrink-0 bg-neutral-600`}
        loading={'lazy'}
    />
);

export default () => {
    const { t } = useTranslation('server_players');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [data, setData] = useState<PlayerOverview | null>(null);
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [tab, setTab] = useState<Tab>('whitelist');
    const [target, setTarget] = useState('');
    const [reason, setReason] = useState('');

    const load = () => {
        setLoading(true);
        getPlayers(uuid)
            .then(setData)
            .catch((error) => addError({ key: 'players', message: httpErrorToHuman(error) }))
            .then(() => setLoading(false));
    };

    useEffect(() => {
        load();
    }, []);

    const act = (action: PlayerAction, name?: string, why?: string) => {
        clearFlashes('players');
        setBusy(true);
        runPlayerAction(uuid, action, name, why)
            .then((via) => {
                addFlash({
                    key: 'players',
                    type: 'success',
                    message:
                        t(`done.${action}`, { name: name || '' }) + (via === 'file' ? ' ' + t('applied_on_start') : ''),
                });
                setTarget('');
                setReason('');
                // A running server writes its list files a moment after the command.
                setTimeout(load, via === 'command' ? 1500 : 0);
            })
            .catch((error) => addError({ key: 'players', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    if (loading && !data) {
        return (
            <ServerContentBlock title={t('title')}>
                <Spinner size={'large'} centered />
            </ServerContentBlock>
        );
    }

    const current = TABS.find((entry) => entry.key === tab)!;
    const entries: PlayerEntry[] = data ? data.lists[tab] : [];

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'players'} css={tw`mb-4`} />
            {data && !data.running && (
                <Alert type={'info'} className={'mb-4'}>
                    {t('offline_notice')}
                </Alert>
            )}

            <div css={tw`grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6`}>
                <TitledGreyBox
                    title={
                        <div css={tw`flex items-center justify-between`}>
                            <p css={tw`text-sm uppercase`}>
                                {t('online_title')}
                                {data?.online && (
                                    <span css={tw`ml-2 text-neutral-300 normal-case`}>
                                        {data.online.online} / {data.online.max}
                                    </span>
                                )}
                            </p>
                            <button
                                type={'button'}
                                onClick={load}
                                css={tw`text-neutral-300 hover:text-neutral-100`}
                                aria-label={t('refresh')}
                            >
                                <FontAwesomeIcon icon={faSyncAlt} spin={loading} />
                            </button>
                        </div>
                    }
                    css={tw`lg:col-span-2`}
                >
                    {!data?.running ? (
                        <p css={tw`text-sm text-neutral-400`}>{t('server_offline')}</p>
                    ) : !data.online ? (
                        <p css={tw`text-sm text-neutral-400`}>{t('ping_failed')}</p>
                    ) : data.online.sample.length === 0 ? (
                        <p css={tw`text-sm text-neutral-400`}>
                            {data.online.online > 0
                                ? t('sample_hidden', { count: data.online.online })
                                : t('nobody_online')}
                        </p>
                    ) : (
                        <div css={tw`space-y-2`}>
                            {data.online.sample.map((player) => (
                                <div key={player.id} css={tw`flex items-center`}>
                                    <Avatar name={player.name} />
                                    <span css={tw`ml-3 flex-1 truncate`}>{player.name}</span>
                                    <Button
                                        size={'xsmall'}
                                        color={'grey'}
                                        disabled={busy}
                                        onClick={() => act('op', player.name)}
                                    >
                                        <FontAwesomeIcon icon={faCrown} css={tw`mr-1`} />
                                        {t('actions.op')}
                                    </Button>
                                    <Button
                                        size={'xsmall'}
                                        color={'grey'}
                                        css={tw`ml-2`}
                                        disabled={busy}
                                        onClick={() => act('kick', player.name)}
                                    >
                                        <FontAwesomeIcon icon={faDoorOpen} css={tw`mr-1`} />
                                        {t('actions.kick')}
                                    </Button>
                                    <Button
                                        size={'xsmall'}
                                        color={'red'}
                                        css={tw`ml-2`}
                                        disabled={busy}
                                        onClick={() => act('ban', player.name)}
                                    >
                                        <FontAwesomeIcon icon={faBan} css={tw`mr-1`} />
                                        {t('actions.ban')}
                                    </Button>
                                </div>
                            ))}
                            {data.online.online > data.online.sample.length && (
                                <p css={tw`text-xs text-neutral-400`}>
                                    {t('more_online', { count: data.online.online - data.online.sample.length })}
                                </p>
                            )}
                        </div>
                    )}
                </TitledGreyBox>

                <TitledGreyBox title={t('whitelist_title')}>
                    <p css={tw`text-sm text-neutral-300 mb-3`}>
                        {data?.whitelistEnabled ? t('whitelist_on') : t('whitelist_off')}
                    </p>
                    <Button
                        size={'small'}
                        color={data?.whitelistEnabled ? 'grey' : 'primary'}
                        disabled={busy}
                        onClick={() => act(data?.whitelistEnabled ? 'whitelist_off' : 'whitelist_on')}
                    >
                        {data?.whitelistEnabled ? t('whitelist_disable') : t('whitelist_enable')}
                    </Button>
                    {data && !data.onlineMode && <p css={tw`text-xs text-yellow-400 mt-3`}>{t('offline_mode')}</p>}
                </TitledGreyBox>
            </div>

            <div css={tw`flex flex-wrap border-b border-neutral-600 mb-4`}>
                {TABS.map((entry) => (
                    <button
                        key={entry.key}
                        type={'button'}
                        onClick={() => {
                            setTab(entry.key);
                            setTarget('');
                            setReason('');
                        }}
                        css={[
                            tw`px-4 py-2 text-sm border-b-2 -mb-px transition-colors duration-150`,
                            tab === entry.key
                                ? tw`border-primary-400 text-neutral-50`
                                : tw`border-transparent text-neutral-400 hover:text-neutral-200`,
                        ]}
                    >
                        {t(`tabs.${entry.key}`)}
                        <span css={tw`ml-2 text-xs text-neutral-500`}>{data ? data.lists[entry.key].length : 0}</span>
                    </button>
                ))}
            </div>

            <form
                css={tw`flex flex-col sm:flex-row gap-2 mb-4`}
                onSubmit={(e: React.FormEvent<HTMLFormElement>) => {
                    e.preventDefault();
                    if (target.trim()) act(current.add, target.trim(), reason.trim() || undefined);
                }}
            >
                <Input
                    value={target}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setTarget(e.currentTarget.value)}
                    placeholder={current.ip ? t('ip_placeholder') : t('name_placeholder')}
                    maxLength={current.ip ? 45 : 17}
                />
                {current.reason && (
                    <Input
                        value={reason}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => setReason(e.currentTarget.value)}
                        placeholder={t('reason_placeholder')}
                        maxLength={100}
                    />
                )}
                <Button type={'submit'} css={tw`flex-shrink-0`} disabled={busy || !target.trim()} isLoading={busy}>
                    <FontAwesomeIcon icon={current.key.startsWith('banned') ? faBan : faUserPlus} css={tw`mr-2`} />
                    {t(`add.${current.key}`)}
                </Button>
            </form>

            {entries.length === 0 ? (
                <p css={tw`text-center text-sm text-neutral-400 py-6`}>{t(`empty.${current.key}`)}</p>
            ) : (
                entries.map((entry, index) => {
                    const label = current.ip ? entry.ip : entry.name;

                    return (
                        <GreyRowBox key={`${label}-${index}`} css={index > 0 ? tw`mt-2` : undefined}>
                            {!current.ip && <Avatar name={entry.name} />}
                            <div css={tw`ml-3 flex-1 min-w-0`}>
                                <p css={tw`truncate`}>{label}</p>
                                {(entry.reason || entry.created) && current.reason && (
                                    <p css={tw`text-xs text-neutral-400 truncate`}>
                                        {entry.reason}
                                        {entry.created && ` · ${entry.created.substring(0, 16)}`}
                                    </p>
                                )}
                            </div>
                            <Button
                                size={'xsmall'}
                                color={'grey'}
                                disabled={busy}
                                onClick={() => act(current.remove, label)}
                                aria-label={t(`remove.${current.key}`)}
                            >
                                <FontAwesomeIcon icon={faTimes} css={tw`mr-1`} />
                                {t(`remove.${current.key}`)}
                            </Button>
                        </GreyRowBox>
                    );
                })
            )}
        </ServerContentBlock>
    );
};
