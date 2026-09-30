import React from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';

const colors: Record<string, string> = {
    open: 'bg-yellow-600',
    customer_reply: 'bg-blue-600',
    answered: 'bg-green-600',
    closed: 'bg-neutral-500',
};

export default ({ status }: { status: string }) => {
    const { t } = useTranslation('tickets');

    return (
        <span
            css={tw`text-xs uppercase text-white rounded px-2 py-1 ml-4 whitespace-nowrap`}
            className={colors[status] ?? 'bg-neutral-500'}
        >
            {t(`status.${status}`)}
        </span>
    );
};
