import React, { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, RouteComponentProps } from 'react-router-dom';
import login from '@/api/auth/login';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';
import DiscordIcon from '@/components/elements/DiscordIcon';
import PasskeyLoginButton from '@/components/auth/PasskeyLoginButton';

interface Values {
    username: string;
    password: string;
}

const LoginContainer = ({ history }: RouteComponentProps) => {
    const { t } = useTranslation(['auth', 'strings']);
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');

    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);
    const discordEnabled = useStoreState((state) => !!state.settings.data!.discord?.enabled);

    useEffect(() => {
        clearFlashes();

        // Coming back from a failed Discord login: /auth/login?discord_error=...
        const code = new URLSearchParams(window.location.search).get('discord_error');
        if (code) {
            addFlash({
                type: 'error',
                title: 'Discord',
                message: t(`discord.errors.${code}`, t('discord.errors.discord')),
            });
            window.history.replaceState(null, '', window.location.pathname);
        }
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        // If there is no token in the state yet, request the token and then abort this submit request
        // since it will be re-submitted when the recaptcha data is returned by the component.
        if (recaptchaEnabled && !token) {
            ref.current!.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });

            return;
        }

        login({ ...values, recaptchaData: token })
            .then((response) => {
                if (response.complete) {
                    // @ts-expect-error this is valid
                    window.location = response.intended || '/';
                    return;
                }

                history.replace('/auth/login/checkpoint', { token: response.confirmationToken });
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ username: '', password: '' }}
            validationSchema={object().shape({
                username: string().required(t('username_email_required')),
                password: string().required(t('password_required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('login_title')} css={tw`w-full flex`}>
                    <Field
                        light
                        type={'text'}
                        label={t('user_identifier', { ns: 'strings' })}
                        name={'username'}
                        disabled={isSubmitting}
                    />
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'password'}
                            label={t('password', { ns: 'strings' })}
                            name={'password'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} isLoading={isSubmitting} disabled={isSubmitting}>
                            {t('login', { ns: 'strings' })}
                        </Button>
                    </div>
                    <div css={tw`mt-2`}>
                        <Link to={'/auth/register'} css={tw`block`}>
                            <Button
                                type={'button'}
                                size={'xlarge'}
                                isSecondary
                                css={tw`text-primary-500 border-primary-500 hover:text-primary-50 hover:bg-primary-500 hover:border-primary-600`}
                            >
                                {t('register_button')}
                            </Button>
                        </Link>
                    </div>
                    <PasskeyLoginButton disabled={isSubmitting} />
                    {discordEnabled && (
                        <div css={tw`mt-2`}>
                            <a
                                href={'/auth/discord'}
                                css={tw`flex items-center justify-center w-full p-4 rounded text-white font-medium no-underline transition-colors duration-150`}
                                style={{ backgroundColor: '#5865F2' }}
                            >
                                <DiscordIcon className={'mr-2 text-lg'} />
                                {t('discord.login')}
                            </a>
                        </div>
                    )}
                    {recaptchaEnabled && (
                        <Reaptcha
                            ref={ref}
                            size={'invisible'}
                            sitekey={siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                                submitForm();
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                        />
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/password'}
                            css={tw`text-xs text-neutral-500 tracking-wide no-underline uppercase hover:text-neutral-600`}
                        >
                            {t('forgot_password.label')}
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default LoginContainer;
