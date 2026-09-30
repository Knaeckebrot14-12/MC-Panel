import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useLocation } from 'react-router-dom';
import tw from 'twin.macro';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import PageContentBlock from '@/components/elements/PageContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Spinner from '@/components/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getCoinOptions, { CoinOptions } from '@/api/coins/getCoinOptions';
import createLinkvertiseClaim from '@/api/coins/createLinkvertiseClaim';
import { claimDailyReward, redeemVoucher } from '@/api/coins/coinExtras';

export default () => {
    const { t } = useTranslation('coins');
    const location = useLocation();
    const [options, setOptions] = useState<CoinOptions | null>(null);
    const [loading, setLoading] = useState(false);
    const [voucherCode, setVoucherCode] = useState('');
    const [voucherBusy, setVoucherBusy] = useState(false);
    const [dailyBusy, setDailyBusy] = useState(false);
    const { clearFlashes, addFlash } = useFlash();
    const setBalance = useStoreActions((actions: Actions<ApplicationStore>) => actions.coins.setBalance);

    const refresh = () => {
        getCoinOptions()
            .then((data) => {
                setOptions(data);
                setBalance(data.balance);
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:earn', type: 'error', message: httpErrorToHuman(error) });
            });
    };

    useEffect(() => {
        clearFlashes('coins:earn');
        refresh();

        const params = new URLSearchParams(location.search);
        const claim = params.get('claim');
        if (claim === 'success') {
            const coins = params.get('coins');
            addFlash({
                key: 'coins:earn',
                type: 'success',
                message: coins ? t('earn.claim_success', { coins }) : t('earn.claim_success_fallback'),
            });
        } else if (claim === 'invalid') {
            addFlash({
                key: 'coins:earn',
                type: 'error',
                message: t('earn.claim_invalid'),
            });
        }
        // Clean the query string so a refresh doesn't re-trigger the message.
        if (claim) {
            window.history.replaceState(null, document.title, '/coins/earn');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const claimDaily = () => {
        setDailyBusy(true);
        clearFlashes('coins:earn');

        claimDailyReward()
            .then((result) => {
                addFlash({
                    key: 'coins:earn',
                    type: 'success',
                    message: t('daily.success', { reward: result.reward, streak: result.streak }),
                });
                refresh();
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:earn', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setDailyBusy(false));
    };

    const submitVoucher = (e: React.FormEvent) => {
        e.preventDefault();
        if (!voucherCode.trim()) return;

        setVoucherBusy(true);
        clearFlashes('coins:earn');

        redeemVoucher(voucherCode.trim())
            .then((result) => {
                addFlash({ key: 'coins:earn', type: 'success', message: t('voucher.success', { coins: result.coins }) });
                setVoucherCode('');
                refresh();
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:earn', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setVoucherBusy(false));
    };

    const referralLink = options?.referral.code
        ? window.location.origin + '/auth/register?ref=' + options.referral.code
        : '';

    const copyReferralLink = () => {
        if (!referralLink || !navigator.clipboard) return;
        navigator.clipboard
            .writeText(referralLink)
            .then(() => addFlash({ key: 'coins:earn', type: 'success', message: t('referral.copied') }))
            .catch(() => undefined);
    };

    const startLinkvertise = () => {
        setLoading(true);
        clearFlashes('coins:earn');

        createLinkvertiseClaim()
            .then(({ url }) => {
                window.location.href = url;
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:earn', type: 'error', message: httpErrorToHuman(error) });
                setLoading(false);
            });
    };

    if (!options) {
        return (
            <PageContentBlock title={t('earn.title')} showFlashKey={'coins:earn'}>
                <Spinner centered size={'large'} />
            </PageContentBlock>
        );
    }

    return (
        <PageContentBlock title={t('earn.title')} showFlashKey={'coins:earn'}>
            <h1 css={tw`text-5xl mb-2`}>{t('earn.title')}</h1>
            <p css={tw`text-sm text-neutral-400 mb-8`}>{t('balance_line', { balance: options.balance })}</p>

            <TitledGreyBox title={t('earn.linkvertise_title')} css={tw`mb-8`}>
                {options.linkvertise.available ? (
                    <>
                        <p css={tw`text-sm text-neutral-300 mb-4`}>
                            {t('earn.linkvertise_body', { reward: options.linkvertise.reward })}
                        </p>
                        <Button onClick={startLinkvertise} isLoading={loading} disabled={loading}>
                            {t('earn.start_linkvertise')}
                        </Button>
                    </>
                ) : (
                    <p css={tw`text-sm text-neutral-400`}>{t('earn.linkvertise_unavailable')}</p>
                )}
            </TitledGreyBox>

            {options.daily.enabled && (
                <TitledGreyBox title={t('daily.title')} css={tw`mb-8`}>
                    <p css={tw`text-sm text-neutral-300 mb-2`}>{t('daily.body')}</p>
                    <p css={tw`text-xs text-neutral-400 mb-4`}>{t('daily.streak', { streak: options.daily.streak })}</p>
                    {options.daily.available ? (
                        <Button onClick={claimDaily} isLoading={dailyBusy} disabled={dailyBusy}>
                            {t('daily.claim', { reward: options.daily.reward })}
                        </Button>
                    ) : (
                        <p css={tw`text-sm text-neutral-400`}>{t('daily.claimed_today')}</p>
                    )}
                </TitledGreyBox>
            )}

            <TitledGreyBox title={t('voucher.title')} css={tw`mb-8`}>
                <p css={tw`text-sm text-neutral-300 mb-4`}>{t('voucher.body')}</p>
                <form onSubmit={submitVoucher} css={tw`flex`}>
                    <input
                        type={'text'}
                        value={voucherCode}
                        onChange={(e) => setVoucherCode(e.target.value)}
                        placeholder={t('voucher.placeholder')}
                        maxLength={64}
                        css={tw`flex-1 mr-4 bg-neutral-600 border border-neutral-500 rounded p-3 text-sm text-neutral-100`}
                    />
                    <Button type={'submit'} isLoading={voucherBusy} disabled={voucherBusy || !voucherCode.trim()}>
                        {t('voucher.button')}
                    </Button>
                </form>
            </TitledGreyBox>

            {options.referral.enabled && options.referral.code && (
                <TitledGreyBox title={t('referral.title')} css={tw`mb-8`}>
                    <p css={tw`text-sm text-neutral-300 mb-4`}>
                        {t('referral.body', {
                            bonus: options.referral.referredBonus,
                            reward: options.referral.referrerReward,
                        })}
                    </p>
                    <p css={tw`text-xs uppercase text-neutral-400 mb-1`}>{t('referral.your_link')}</p>
                    <div css={tw`flex`}>
                        <input
                            readOnly
                            value={referralLink}
                            onFocus={(e) => e.target.select()}
                            css={tw`flex-1 mr-4 bg-neutral-600 border border-neutral-500 rounded p-3 text-sm text-neutral-100`}
                        />
                        <Button onClick={copyReferralLink}>{t('referral.copy')}</Button>
                    </div>
                    <p css={tw`text-xs text-neutral-400 mt-3`}>
                        {t('referral.stats', { invited: options.referral.invited, active: options.referral.active })}
                    </p>
                </TitledGreyBox>
            )}

            <TitledGreyBox title={t('earn.afk_title')}>
                <p css={tw`text-sm text-neutral-300 mb-4`}>
                    {t('earn.afk_body', { reward: options.afk.rewardPerMinute })}
                </p>
                <a href={'/coins/afk'}>
                    <Button>{t('earn.go_to_afk')}</Button>
                </a>
            </TitledGreyBox>
        </PageContentBlock>
    );
};
