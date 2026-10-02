import * as React from 'react';
import { useTranslation } from 'react-i18next';
import ContentBox from '@/components/elements/ContentBox';
import UpdatePasswordForm from '@/components/dashboard/forms/UpdatePasswordForm';
import UpdateEmailAddressForm from '@/components/dashboard/forms/UpdateEmailAddressForm';
import ConfigureTwoFactorForm from '@/components/dashboard/forms/ConfigureTwoFactorForm';
import UpdateLanguageForm from '@/components/dashboard/forms/UpdateLanguageForm';
import PageContentBlock from '@/components/elements/PageContentBlock';
import tw from 'twin.macro';
import { breakpoint } from '@/theme';
import styled from 'styled-components/macro';
import MessageBox from '@/components/MessageBox';
import { useLocation } from 'react-router-dom';
import { useStoreState } from 'easy-peasy';
import DiscordLinkForm from '@/components/dashboard/forms/DiscordLinkForm';
import AppSettingsForm from '@/components/dashboard/forms/AppSettingsForm';
import PasskeysForm from '@/components/dashboard/forms/PasskeysForm';

const Container = styled.div`
    ${tw`flex flex-wrap`};

    & > div {
        ${tw`w-full`};

        ${breakpoint('sm')`
      width: calc(50% - 1rem);
    `}

        ${breakpoint('md')`
      ${tw`w-auto flex-1`};
    `}
    }
`;

export default () => {
    const { t } = useTranslation('dashboard/account');
    const { state } = useLocation<undefined | { twoFactorRedirect?: boolean }>();
    const discordEnabled = useStoreState((s) => !!s.settings.data?.discord?.enabled);

    return (
        <PageContentBlock title={t('overview_title')}>
            {state?.twoFactorRedirect && (
                <MessageBox title={t('two_factor_required_title')} type={'error'}>
                    {t('two_factor_required_body')}
                </MessageBox>
            )}

            <Container css={[tw`lg:grid lg:grid-cols-3 mb-10`, state?.twoFactorRedirect ? tw`mt-4` : tw`mt-10`]}>
                <ContentBox title={t('password.title')} showFlashes={'account:password'}>
                    <UpdatePasswordForm />
                </ContentBox>
                <ContentBox css={tw`mt-8 sm:mt-0 sm:ml-8`} title={t('email.title')} showFlashes={'account:email'}>
                    <UpdateEmailAddressForm />
                </ContentBox>
                <ContentBox css={tw`md:ml-8 mt-8 md:mt-0`} title={t('two_factor.title')}>
                    <ConfigureTwoFactorForm />
                </ContentBox>
                <ContentBox css={tw`mt-8 sm:ml-8 lg:ml-0`} title={t('language_title')} showFlashes={'account:language'}>
                    <UpdateLanguageForm />
                </ContentBox>
                {discordEnabled && (
                    <ContentBox css={tw`mt-8 lg:ml-8`} title={t('discord.title')} showFlashes={'account:discord'}>
                        <DiscordLinkForm />
                    </ContentBox>
                )}
                <ContentBox css={tw`mt-8 sm:ml-8`} title={t('app_title')} showFlashes={'account:app'}>
                    <AppSettingsForm />
                </ContentBox>
                <PasskeysForm css={tw`mt-8 lg:col-span-3`} />
            </Container>
        </PageContentBlock>
    );
};
