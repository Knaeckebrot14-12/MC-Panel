import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';
import tw from 'twin.macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faThumbsDown, faThumbsUp } from '@fortawesome/free-solid-svg-icons';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { closeTicket, getTicket, rateTicket, reopenTicket, replyToTicket, TicketDetail } from '@/api/tickets/tickets';
import TicketStatusBadge from '@/components/dashboard/tickets/TicketStatusBadge';

export default () => {
    const { t } = useTranslation('tickets');
    const { id } = useParams<{ id: string }>();
    const [ticket, setTicket] = useState<TicketDetail | null>(null);
    const [message, setMessage] = useState('');
    const [busy, setBusy] = useState(false);
    const { clearFlashes, addFlash } = useFlash();

    const handleError = (error: any) => {
        console.error(error);
        addFlash({ key: 'tickets:view', type: 'error', message: httpErrorToHuman(error) });
    };

    useEffect(() => {
        clearFlashes('tickets:view');
        getTicket(Number(id)).then(setTicket).catch(handleError);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [id]);

    const run = (action: () => Promise<TicketDetail>, after?: () => void) => {
        setBusy(true);
        clearFlashes('tickets:view');
        action()
            .then((data) => {
                setTicket(data);
                after && after();
            })
            .catch(handleError)
            .then(() => setBusy(false));
    };

    if (!ticket) {
        return (
            <PageContentBlock title={t('view.title')} showFlashKey={'tickets:view'}>
                <Spinner centered size={'large'} />
            </PageContentBlock>
        );
    }

    const closed = ticket.status === 'closed';

    return (
        <PageContentBlock title={`#${ticket.id} ${ticket.subject}`} showFlashKey={'tickets:view'}>
            <Link to={'/tickets'} css={tw`text-sm text-neutral-400 hover:text-neutral-200`}>
                ← {t('view.back')}
            </Link>
            <div css={tw`flex items-center justify-between mt-2 mb-2`}>
                <h1 css={tw`text-3xl`}>
                    #{ticket.id} {ticket.subject}
                </h1>
                <TicketStatusBadge status={ticket.status} />
            </div>
            <p css={tw`text-xs text-neutral-400 mb-8`}>
                {t(`categories.${ticket.category}`)} · {t(`priorities.${ticket.priority}`)}
                {ticket.serverName && <> · {t('view.server', { name: ticket.serverName })}</>}
            </p>

            {ticket.messages.map((m) => (
                <div
                    key={m.id}
                    css={[
                        tw`rounded p-4 mb-4 border-l-4`,
                        m.isStaff ? tw`bg-neutral-700 border-blue-500` : tw`bg-neutral-800 border-neutral-500`,
                    ]}
                >
                    <div css={tw`flex justify-between text-xs text-neutral-400 mb-2`}>
                        <span>
                            {m.author}
                            {m.isStaff && <span css={tw`ml-2 text-blue-400 uppercase`}>{t('view.staff')}</span>}
                        </span>
                        <span>{new Date(m.createdAt).toLocaleString()}</span>
                    </div>
                    <p css={tw`text-sm whitespace-pre-wrap break-words`}>{m.body}</p>
                </div>
            ))}

            {closed ? (
                <>
                    <div css={tw`mt-6 flex items-center justify-between`}>
                        <p css={tw`text-sm text-neutral-400`}>{t('view.closed_notice')}</p>
                        <Button
                            isSecondary
                            isLoading={busy}
                            disabled={busy}
                            onClick={() => run(() => reopenTicket(ticket.id))}
                        >
                            {t('view.reopen')}
                        </Button>
                    </div>
                    <div css={tw`mt-6 bg-neutral-700 rounded p-4 flex items-center justify-between`}>
                        <p css={tw`text-sm`}>{ticket.rating ? t('view.rated_thanks') : t('view.rate_question')}</p>
                        <div css={tw`flex`}>
                            <Button
                                size={'small'}
                                color={ticket.rating === 1 ? 'green' : 'grey'}
                                isSecondary={ticket.rating !== 1}
                                disabled={busy}
                                onClick={() => run(() => rateTicket(ticket.id, 'up'))}
                                css={tw`mr-2`}
                            >
                                <FontAwesomeIcon icon={faThumbsUp} css={tw`mr-2`} />
                                {t('view.rate_up')}
                            </Button>
                            <Button
                                size={'small'}
                                color={ticket.rating === -1 ? 'red' : 'grey'}
                                isSecondary={ticket.rating !== -1}
                                disabled={busy}
                                onClick={() => run(() => rateTicket(ticket.id, 'down'))}
                            >
                                <FontAwesomeIcon icon={faThumbsDown} css={tw`mr-2`} />
                                {t('view.rate_down')}
                            </Button>
                        </div>
                    </div>
                </>
            ) : (
                <div css={tw`mt-6`}>
                    <label htmlFor={'reply'} css={tw`text-xs uppercase text-neutral-200 block mb-1`}>
                        {t('view.reply')}
                    </label>
                    <textarea
                        id={'reply'}
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        rows={5}
                        maxLength={5000}
                        css={tw`w-full bg-neutral-600 border border-neutral-500 rounded p-3 text-sm text-neutral-100`}
                    />
                    <div css={tw`flex justify-between mt-3`}>
                        <Button
                            isSecondary
                            color={'red'}
                            isLoading={busy}
                            disabled={busy}
                            onClick={() => run(() => closeTicket(ticket.id))}
                        >
                            {t('view.close')}
                        </Button>
                        <Button
                            isLoading={busy}
                            disabled={busy || message.trim().length < 2}
                            onClick={() =>
                                run(
                                    () => replyToTicket(ticket.id, message),
                                    () => setMessage('')
                                )
                            }
                        >
                            {t('view.send')}
                        </Button>
                    </div>
                </div>
            )}
        </PageContentBlock>
    );
};
