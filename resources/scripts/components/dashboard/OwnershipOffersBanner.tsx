import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useHistory } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faGift } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { acceptOwnership, declineOwnership, getIncomingOwnership, IncomingOwnership } from '@/api/server/ownership';

/**
 * Servers other users want to give to the signed-in user.
 */
export default () => {
    const { t } = useTranslation('server_ownership');
    const history = useHistory();
    const { addError, clearFlashes, addFlash } = useFlash();
    const [offers, setOffers] = useState<IncomingOwnership[]>([]);
    const [busy, setBusy] = useState<number | null>(null);

    useEffect(() => {
        getIncomingOwnership()
            .then(setOffers)
            .catch(() => setOffers([]));
    }, []);

    if (offers.length === 0) return null;

    const remove = (id: number) => setOffers((list) => list.filter((offer) => offer.id !== id));

    const accept = (offer: IncomingOwnership) => {
        clearFlashes('dashboard');
        setBusy(offer.id);
        acceptOwnership(offer.id)
            .then((server) => {
                remove(offer.id);
                addFlash({ key: 'dashboard', type: 'success', message: t('accepted', { server: offer.server }) });
                history.push(`/server/${server}`);
            })
            .catch((error) => {
                addError({ key: 'dashboard', message: httpErrorToHuman(error) });
                setBusy(null);
            });
    };

    const decline = (offer: IncomingOwnership) => {
        clearFlashes('dashboard');
        setBusy(offer.id);
        declineOwnership(offer.id)
            .then(() => {
                remove(offer.id);
                addFlash({ key: 'dashboard', type: 'success', message: t('declined') });
            })
            .catch((error) => addError({ key: 'dashboard', message: httpErrorToHuman(error) }))
            .then(() => setBusy(null));
    };

    return (
        <div css={tw`mb-4 space-y-2`}>
            {offers.map((offer) => (
                <div
                    key={offer.id}
                    css={tw`bg-cyan-600 bg-opacity-10 border border-cyan-500 border-opacity-50 rounded p-4 flex flex-wrap gap-3 items-center`}
                >
                    <FontAwesomeIcon icon={faGift} css={tw`text-cyan-400 flex-shrink-0`} />
                    <div css={tw`min-w-0 flex-1`}>
                        <p css={tw`text-sm font-medium text-cyan-50`}>{t('incoming_title')}</p>
                        <p css={tw`text-sm text-cyan-100 mt-1 break-words`}>
                            {t('incoming_line', { user: offer.from, server: offer.server })}
                        </p>
                        <p css={tw`text-xs text-neutral-300 mt-1`}>
                            {t('resources', { memory: offer.memory, disk: offer.disk, cpu: offer.cpu })}
                            {' · '}
                            {t('expires', { date: new Date(offer.expires_at).toLocaleDateString() })}
                        </p>
                        {offer.coins && (
                            <p css={tw`text-xs text-yellow-300 mt-1`}>
                                {t('coins_line', {
                                    coins: offer.coins.monthly,
                                    date: new Date(offer.coins.paid_until).toLocaleDateString(),
                                })}
                            </p>
                        )}
                    </div>
                    <div css={tw`flex gap-2`}>
                        <Button.Text disabled={busy === offer.id} onClick={() => decline(offer)}>
                            {t('decline')}
                        </Button.Text>
                        <Button disabled={busy === offer.id} onClick={() => accept(offer)}>
                            {t('accept')}
                        </Button>
                    </div>
                </div>
            ))}
        </div>
    );
};
