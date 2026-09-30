import React, { useEffect, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCoins } from '@fortawesome/free-solid-svg-icons';
import { Alert } from '@/components/elements/alert';
import getCoinOptions from '@/api/coins/getCoinOptions';

const formatRemaining = (iso: string): string => {
    const ms = new Date(iso).getTime() - Date.now();
    if (ms <= 0) return 'Renewal is overdue — it will be suspended soon if it is not paid.';

    const days = Math.floor(ms / (1000 * 60 * 60 * 24));
    const hours = Math.floor((ms / (1000 * 60 * 60)) % 24);

    if (days > 0) return `Renews in ${days} day${days === 1 ? '' : 's'}`;
    return `Renews in ${hours} hour${hours === 1 ? '' : 's'}`;
};

export default ({ paidWithCoinsUntil }: { paidWithCoinsUntil: string }) => {
    const [monthlyPrice, setMonthlyPrice] = useState<number | null>(null);

    useEffect(() => {
        getCoinOptions()
            .then((data) => setMonthlyPrice(data.shop.server.monthlyPrice))
            .catch((error) => console.error(error));
    }, []);

    return (
        <Alert type={'info'} className={'mb-4'}>
            <FontAwesomeIcon icon={faCoins} className={'mr-2 text-yellow-400'} />
            This server was bought with coins{monthlyPrice !== null ? ` (${monthlyPrice} coins / month)` : ''}.{' '}
            {formatRemaining(paidWithCoinsUntil)}. You can manage or cancel it from the Create Server page.
        </Alert>
    );
};
