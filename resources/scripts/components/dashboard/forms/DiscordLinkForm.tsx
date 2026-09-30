import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { useStoreActions, useStoreState } from 'easy-peasy';
import Button from '@/components/elements/Button';
import DiscordIcon from '@/components/elements/DiscordIcon';
import useFlash from '@/plugins/useFlash';
import { unlinkDiscord } from '@/api/account/community';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const discordUsername = useStoreState((state) => state.user.data!.discordUsername);
    const updateUserData = useStoreActions((actions) => actions.user.updateUserData);
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();
    const [busy, setBusy] = useState(false);

    // Result of the link flow: /account?discord=linked or ?discord_error=...
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const linked = params.get('discord');
        const error = params.get('discord_error');
        if (!linked && !error) return;

        clearFlashes('account:discord');
        if (linked) {
            addFlash({ key: 'account:discord', type: 'success', message: t('discord.linked_success') });
        } else {
            addFlash({
                key: 'account:discord',
                type: 'error',
                message: t(`discord.errors.${error}`, t('discord.errors.discord')),
            });
        }
        window.history.replaceState(null, '', window.location.pathname);
    }, []);

    const unlink = () => {
        clearFlashes('account:discord');
        setBusy(true);
        unlinkDiscord()
            .then(() => {
                updateUserData({ discordUsername: null });
                addFlash({ key: 'account:discord', type: 'success', message: t('discord.unlinked_success') });
            })
            .catch((error) => clearAndAddHttpError({ key: 'account:discord', error }))
            .then(() => setBusy(false));
    };

    return discordUsername ? (
        <div>
            <p css={tw`text-sm flex items-center`}>
                <DiscordIcon className={'mr-2 text-lg'} />
                {t('discord.linked_as', { name: discordUsername })}
            </p>
            <p css={tw`text-xs text-neutral-400 mt-2`}>{t('discord.unlink_hint')}</p>
            <div css={tw`mt-6`}>
                <Button color={'red'} isSecondary disabled={busy} isLoading={busy} onClick={unlink}>
                    {t('discord.unlink')}
                </Button>
            </div>
        </div>
    ) : (
        <div>
            <p css={tw`text-sm text-neutral-300`}>{t('discord.not_linked')}</p>
            <div css={tw`mt-6`}>
                <a
                    href={'/auth/discord'}
                    css={tw`inline-flex items-center px-4 py-2 rounded text-white text-sm no-underline`}
                    style={{ backgroundColor: '#5865F2' }}
                >
                    <DiscordIcon className={'mr-2'} />
                    {t('discord.link')}
                </a>
            </div>
        </div>
    );
};
