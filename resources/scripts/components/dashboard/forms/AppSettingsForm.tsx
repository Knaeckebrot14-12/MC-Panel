import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import {
    canInstall,
    currentSubscription,
    disablePush,
    enablePush,
    getTheme,
    isInstalled,
    onInstallChange,
    promptInstall,
    pushSupported,
    setTheme,
    testPush,
    Theme,
} from '@/lib/pwa';

/**
 * Account page box: light/dark theme, installing the panel as an app, and push notifications
 * (server crashes, coin reminders, ticket answers).
 */
export default () => {
    const { t } = useTranslation('app');
    const { addError, clearFlashes, addFlash } = useFlash();
    const [theme, setThemeState] = useState<Theme>(getTheme());
    const [installable, setInstallable] = useState(canInstall());
    const [pushOn, setPushOn] = useState<boolean | null>(null);
    const [busy, setBusy] = useState(false);

    useEffect(() => onInstallChange(() => setInstallable(canInstall())), []);
    useEffect(() => {
        currentSubscription()
            .then((sub) => setPushOn(!!sub))
            .catch(() => setPushOn(false));
    }, []);

    const switchTheme = (next: Theme) => {
        setTheme(next);
        setThemeState(next);
    };

    const togglePush = () => {
        clearFlashes('account:app');
        setBusy(true);
        (pushOn ? disablePush() : enablePush())
            .then(() => {
                setPushOn(!pushOn);
                if (!pushOn) addFlash({ key: 'account:app', type: 'success', message: t('push.enabled') });
            })
            .catch((error) => {
                const message =
                    error?.message === 'push-denied'
                        ? t('push.denied')
                        : error?.message === 'push-unsupported'
                        ? t('push.unsupported')
                        : httpErrorToHuman(error);
                addError({ key: 'account:app', message });
            })
            .then(() => setBusy(false));
    };

    const sendTest = () => {
        clearFlashes('account:app');
        testPush()
            .then((sent) =>
                addFlash({ key: 'account:app', type: sent > 0 ? 'success' : 'warning', message: sent > 0 ? t('push.test_sent') : t('push.test_none') })
            )
            .catch((error) => addError({ key: 'account:app', message: httpErrorToHuman(error) }));
    };

    return (
        <div css={tw`space-y-5`}>
            <div>
                <p css={tw`text-sm font-medium text-neutral-100 mb-2`}>{t('theme.title')}</p>
                <div css={tw`flex gap-2`}>
                    {(['dark', 'light'] as Theme[]).map((option) => (
                        <Button
                            key={option}
                            type={'button'}
                            size={'xsmall'}
                            isSecondary={theme !== option}
                            onClick={() => switchTheme(option)}
                        >
                            {t(`theme.${option}`)}
                        </Button>
                    ))}
                </div>
            </div>

            <div>
                <p css={tw`text-sm font-medium text-neutral-100 mb-1`}>{t('install.title')}</p>
                {isInstalled() ? (
                    <p css={tw`text-xs text-neutral-300`}>{t('install.installed')}</p>
                ) : installable ? (
                    <>
                        <p css={tw`text-xs text-neutral-300 mb-2`}>{t('install.description')}</p>
                        <Button type={'button'} size={'xsmall'} onClick={() => promptInstall()}>
                            {t('install.button')}
                        </Button>
                    </>
                ) : (
                    <p css={tw`text-xs text-neutral-300`}>{t('install.manual')}</p>
                )}
            </div>

            <div>
                <p css={tw`text-sm font-medium text-neutral-100 mb-1`}>{t('push.title')}</p>
                {!pushSupported() ? (
                    <p css={tw`text-xs text-neutral-300`}>{t('push.unsupported')}</p>
                ) : (
                    <>
                        <p css={tw`text-xs text-neutral-300 mb-2`}>{t('push.description')}</p>
                        <div css={tw`flex gap-2`}>
                            <Button
                                type={'button'}
                                size={'xsmall'}
                                color={pushOn ? 'red' : 'primary'}
                                isSecondary={!!pushOn}
                                isLoading={busy}
                                disabled={busy || pushOn === null}
                                onClick={togglePush}
                            >
                                {pushOn ? t('push.disable') : t('push.enable')}
                            </Button>
                            {pushOn && (
                                <Button type={'button'} size={'xsmall'} isSecondary onClick={sendTest}>
                                    {t('push.test')}
                                </Button>
                            )}
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};
