import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Server } from '@/api/server/getServer';
import getServers from '@/api/getServers';
import ServerRow from '@/components/dashboard/ServerRow';
import AnnouncementsBanner from '@/components/dashboard/AnnouncementsBanner';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import { usePersistedState } from '@/plugins/usePersistedState';
import Switch from '@/components/elements/Switch';
import tw from 'twin.macro';
import useSWR from 'swr';
import { PaginatedResult } from '@/api/http';
import Pagination from '@/components/elements/Pagination';
import { Link, useLocation } from 'react-router-dom';
import Button from '@/components/elements/Button';
import getSelfServiceServerOptions, { SelfServiceServerOptions } from '@/api/getSelfServiceServerOptions';

const PoolBadge = ({
    label,
    used,
    limit,
    unit = '',
}: {
    label: string;
    used: number;
    limit: number;
    unit?: string;
}) => {
    const exhausted = limit > 0 && used >= limit;

    return (
        <div
            css={[
                tw`flex items-center rounded-full px-3 py-1 text-xs font-medium bg-neutral-700 border`,
                exhausted ? tw`border-red-500 text-red-300` : tw`border-neutral-600 text-neutral-200`,
            ]}
        >
            <span css={tw`uppercase text-neutral-400 mr-1.5`}>{label}</span>
            <span>
                {used}/{limit}
                {unit}
            </span>
        </div>
    );
};

export default () => {
    const { t } = useTranslation('dashboard');
    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [showOnlyAdmin, setShowOnlyAdmin] = usePersistedState(`${uuid}:show_all_servers`, false);
    const [pool, setPool] = useState<SelfServiceServerOptions | null>(null);

    const { data: servers, error } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', showOnlyAdmin && rootAdmin, page],
        () => getServers({ page, type: showOnlyAdmin && rootAdmin ? 'admin' : undefined })
    );

    useEffect(() => {
        getSelfServiceServerOptions()
            .then(setPool)
            .catch((error) => console.error(error));
    }, []);

    useEffect(() => {
        setPage(1);
    }, [showOnlyAdmin]);

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        // Don't use react-router to handle changing this part of the URL, otherwise it
        // triggers a needless re-render. We just want to track this in the URL incase the
        // user refreshes the page.
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    return (
        <PageContentBlock title={'Dashboard'} showFlashKey={'dashboard'}>
            <AnnouncementsBanner />
            <div css={tw`mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between`}>
                <Link to={'/create-server'} css={tw`block w-full sm:inline-block sm:w-auto`}>
                    <Button size={'large'} css={tw`w-full sm:w-auto`}>
                        {t('create_a_server')}
                    </Button>
                </Link>
                {rootAdmin && (
                    <div css={tw`flex items-center justify-end`}>
                        <p css={tw`uppercase text-xs text-neutral-400 mr-2`}>
                            {showOnlyAdmin ? t('showing_others_servers') : t('showing_your_servers')}
                        </p>
                        <Switch
                            name={'show_all_servers'}
                            defaultChecked={showOnlyAdmin}
                            onChange={() => setShowOnlyAdmin((s) => !s)}
                        />
                    </div>
                )}
            </div>
            {pool && (
                <div css={tw`mb-6 flex flex-wrap gap-2`}>
                    <PoolBadge label={t('pool.slots')} used={pool.used.slots} limit={pool.limits.slots} />
                    <PoolBadge label={t('pool.ram')} used={pool.used.memory} limit={pool.limits.memory} unit={' MiB'} />
                    <PoolBadge label={t('pool.disk')} used={pool.used.disk} limit={pool.limits.disk} unit={' MiB'} />
                    <PoolBadge label={t('pool.cpu')} used={pool.used.cpu} limit={pool.limits.cpu} unit={'%'} />
                    <PoolBadge label={t('pool.backups')} used={pool.used.backups} limit={pool.limits.backups} />
                </div>
            )}
            {!servers ? (
                <Spinner centered size={'large'} />
            ) : (
                <Pagination data={servers} onPageSelect={setPage}>
                    {({ items }) =>
                        items.length > 0 ? (
                            items.map((server, index) => (
                                <ServerRow key={server.uuid} server={server} css={index > 0 ? tw`mt-2` : undefined} />
                            ))
                        ) : (
                            <div css={tw`text-center`}>
                                <p css={tw`text-sm text-neutral-400`}>
                                    {showOnlyAdmin ? t('no_other_servers') : t('no_own_servers')}
                                </p>
                            </div>
                        )
                    }
                </Pagination>
            )}
        </PageContentBlock>
    );
};
