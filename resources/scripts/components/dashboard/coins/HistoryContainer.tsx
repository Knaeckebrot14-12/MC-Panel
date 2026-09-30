import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Spinner from '@/components/elements/Spinner';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getCoinTransactions, { CoinTransactionsResponse } from '@/api/coins/getCoinTransactions';

export default () => {
    const { t } = useTranslation('coins');
    const [response, setResponse] = useState<CoinTransactionsResponse | null>(null);
    const [page, setPage] = useState(1);
    const { clearFlashes, addFlash } = useFlash();

    const labelFor = (type: string): string =>
        t(`history.types.${type}`, {
            defaultValue: type.replace(/[:_]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
        });

    useEffect(() => {
        clearFlashes('coins:history');
        getCoinTransactions(page)
            .then(setResponse)
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:history', type: 'error', message: httpErrorToHuman(error) });
            });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [page]);

    return (
        <PageContentBlock title={t('history.title')} showFlashKey={'coins:history'}>
            <h1 css={tw`text-5xl mb-8`}>{t('history.title')}</h1>

            {!response ? (
                <Spinner centered size={'large'} />
            ) : response.data.length === 0 ? (
                <p css={tw`text-sm text-neutral-400`}>{t('history.empty')}</p>
            ) : (
                <>
                    <div css={tw`bg-neutral-700 rounded overflow-hidden`}>
                        <table css={tw`w-full text-sm`}>
                            <thead>
                                <tr css={tw`bg-neutral-800 text-neutral-300 text-left uppercase text-xs`}>
                                    <th css={tw`p-3 font-medium`}>{t('history.column_when')}</th>
                                    <th css={tw`p-3 font-medium`}>{t('history.column_type')}</th>
                                    <th css={tw`p-3 font-medium`}>{t('history.column_description')}</th>
                                    <th css={tw`p-3 font-medium text-right`}>{t('history.column_amount')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {response.data.map((transaction, index) => (
                                    <tr key={index} css={tw`border-t border-neutral-600`}>
                                        <td css={tw`p-3 text-neutral-400 whitespace-nowrap`}>
                                            {new Date(transaction.createdAt).toLocaleString()}
                                        </td>
                                        <td css={tw`p-3`}>{labelFor(transaction.type)}</td>
                                        <td css={tw`p-3 text-neutral-300`}>
                                            {transaction.description || t('history.no_description')}
                                        </td>
                                        <td
                                            css={[
                                                tw`p-3 text-right font-medium whitespace-nowrap`,
                                                transaction.amount >= 0 ? tw`text-green-400` : tw`text-red-400`,
                                            ]}
                                        >
                                            {transaction.amount >= 0 ? '+' : ''}
                                            {transaction.amount}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    {response.meta.lastPage > 1 && (
                        <div css={tw`flex justify-between items-center mt-4`}>
                            <Button
                                size={'small'}
                                isSecondary
                                disabled={page <= 1}
                                onClick={() => setPage((p) => Math.max(1, p - 1))}
                            >
                                {t('history.previous')}
                            </Button>
                            <p css={tw`text-xs text-neutral-400`}>
                                {t('history.page_of', {
                                    current: response.meta.currentPage,
                                    last: response.meta.lastPage,
                                })}
                            </p>
                            <Button
                                size={'small'}
                                isSecondary
                                disabled={page >= response.meta.lastPage}
                                onClick={() => setPage((p) => Math.min(response.meta.lastPage, p + 1))}
                            >
                                {t('history.next')}
                            </Button>
                        </div>
                    )}
                </>
            )}
        </PageContentBlock>
    );
};
