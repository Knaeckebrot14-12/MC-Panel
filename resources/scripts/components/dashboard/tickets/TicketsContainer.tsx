import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import Button from '@/components/elements/Button';
import GreyRowBox from '@/components/elements/GreyRowBox';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { getTickets, TicketList } from '@/api/tickets/tickets';
import TicketStatusBadge from '@/components/dashboard/tickets/TicketStatusBadge';

export default () => {
    const { t } = useTranslation('tickets');
    const [list, setList] = useState<TicketList | null>(null);
    const [page, setPage] = useState(1);
    const { clearFlashes, addFlash } = useFlash();

    useEffect(() => {
        clearFlashes('tickets:list');
        getTickets(page)
            .then(setList)
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'tickets:list', type: 'error', message: httpErrorToHuman(error) });
            });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [page]);

    return (
        <PageContentBlock title={t('list.title')} showFlashKey={'tickets:list'}>
            <div css={tw`flex items-center justify-between mb-8`}>
                <h1 css={tw`text-5xl`}>{t('list.title')}</h1>
                <Link to={'/tickets/new'}>
                    <Button>{t('list.new_ticket')}</Button>
                </Link>
            </div>
            {!list ? (
                <Spinner centered size={'large'} />
            ) : list.data.length === 0 ? (
                <p css={tw`text-sm text-neutral-400`}>{t('list.empty')}</p>
            ) : (
                <>
                    {list.data.map((ticket) => (
                        <Link key={ticket.id} to={`/tickets/${ticket.id}`} css={tw`block mb-2 no-underline`}>
                            <GreyRowBox>
                                <div css={tw`flex-1 min-w-0`}>
                                    <p css={tw`text-sm truncate`}>
                                        <span css={tw`text-neutral-400 mr-2`}>#{ticket.id}</span>
                                        {ticket.subject}
                                    </p>
                                    <p css={tw`text-xs text-neutral-400 mt-1`}>
                                        {t(`categories.${ticket.category}`)} ·{' '}
                                        {ticket.lastReplyAt ? new Date(ticket.lastReplyAt).toLocaleString() : '—'}
                                    </p>
                                </div>
                                <TicketStatusBadge status={ticket.status} />
                            </GreyRowBox>
                        </Link>
                    ))}
                    {list.meta.lastPage > 1 && (
                        <div css={tw`flex justify-between items-center mt-4`}>
                            <Button size={'small'} isSecondary disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                                {t('list.previous')}
                            </Button>
                            <p css={tw`text-xs text-neutral-400`}>
                                {t('list.page_of', { current: list.meta.currentPage, last: list.meta.lastPage })}
                            </p>
                            <Button
                                size={'small'}
                                isSecondary
                                disabled={page >= list.meta.lastPage}
                                onClick={() => setPage((p) => p + 1)}
                            >
                                {t('list.next')}
                            </Button>
                        </div>
                    )}
                </>
            )}
        </PageContentBlock>
    );
};
