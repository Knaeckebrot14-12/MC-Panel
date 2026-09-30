import React, { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import PageContentBlock from '@/components/elements/PageContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Spinner from '@/components/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getCoinOptions, { CoinOptions } from '@/api/coins/getCoinOptions';
import sendAfkTick from '@/api/coins/sendAfkTick';

// Classic "bait" element adblock detection: most content/ad blockers apply
// cosmetic filters to elements matching these very common ad-related class
// names and hide them, which we can then detect.
const detectAdblock = (): Promise<boolean> => {
    return new Promise((resolve) => {
        const bait = document.createElement('div');
        bait.className = 'adsbox ad-banner ad-placement adsbygoogle';
        bait.style.cssText = 'width: 1px; height: 1px; position: absolute; left: -9999px; top: -9999px;';
        document.body.appendChild(bait);

        window.setTimeout(() => {
            const blocked =
                bait.offsetParent === null ||
                bait.offsetHeight === 0 ||
                bait.offsetWidth === 0 ||
                window.getComputedStyle(bait).display === 'none' ||
                window.getComputedStyle(bait).visibility === 'hidden';

            document.body.removeChild(bait);
            resolve(blocked);
        }, 100);
    });
};

export default () => {
    const { t } = useTranslation('coins');
    const [options, setOptions] = useState<CoinOptions | null>(null);
    const [secondsLeft, setSecondsLeft] = useState(60);
    const [adblockWarning, setAdblockWarning] = useState(false);
    const [totalEarned, setTotalEarned] = useState(0);
    const { clearFlashes, addFlash } = useFlash();
    const setBalance = useStoreActions((actions: Actions<ApplicationStore>) => actions.coins.setBalance);
    const intervalRef = useRef<number | null>(null);

    useEffect(() => {
        clearFlashes('coins:afk');
        getCoinOptions()
            .then((data) => {
                setOptions(data);
                setBalance(data.balance);
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:afk', type: 'error', message: httpErrorToHuman(error) });
            });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        const tick = async () => {
            const blocked = await detectAdblock();
            setAdblockWarning(blocked);

            sendAfkTick(blocked)
                .then((response) => {
                    setBalance(response.balance);
                    setSecondsLeft(response.secondsUntilNextTick);
                    if (response.credited && response.reward) {
                        setTotalEarned((total) => total + response.reward!);
                    }
                })
                .catch((error) => console.error(error));
        };

        intervalRef.current = window.setInterval(() => {
            setSecondsLeft((seconds) => {
                if (seconds <= 1) {
                    tick();
                    return 60;
                }
                return seconds - 1;
            });
        }, 1000);

        return () => {
            if (intervalRef.current) window.clearInterval(intervalRef.current);
        };
    }, []);

    if (!options) {
        return (
            <PageContentBlock title={t('afk.title')} showFlashKey={'coins:afk'}>
                <Spinner centered size={'large'} />
            </PageContentBlock>
        );
    }

    return (
        <PageContentBlock title={t('afk.title')} showFlashKey={'coins:afk'}>
            <h1 css={tw`text-5xl mb-2`}>{t('afk.heading')}</h1>
            <p css={tw`text-sm text-neutral-400 mb-8`}>
                {t('afk.body', {
                    reward: options.afk.rewardPerMinute,
                    total: totalEarned,
                    seconds: secondsLeft,
                })}
            </p>

            {adblockWarning && (
                <div css={tw`bg-red-500 bg-opacity-25 border border-red-500 rounded p-4 mb-6`}>
                    <p css={tw`text-sm text-red-100`}>{t('afk.adblock_warning')}</p>
                </div>
            )}

            <TitledGreyBox title={t('afk.advertisement')}>
                {options.afk.adSlotHtml ? (
                    <iframe
                        title={'afk-ad-slot'}
                        srcDoc={options.afk.adSlotHtml}
                        css={tw`w-full border-0`}
                        style={{ minHeight: '280px' }}
                        sandbox={'allow-scripts allow-popups allow-same-origin'}
                    />
                ) : (
                    <div
                        css={tw`w-full flex items-center justify-center bg-neutral-900 rounded text-neutral-500 text-sm`}
                        style={{ minHeight: '280px' }}
                    >
                        {t('afk.no_ad_configured')}
                    </div>
                )}
            </TitledGreyBox>
        </PageContentBlock>
    );
};
