import React, { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import register from '@/api/auth/register';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, ref as yupRef, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';

interface Values {
    nameFirst: string;
    nameLast: string;
    username: string;
    email: string;
    password: string;
    passwordConfirmation: string;
}

const readReferralCode = (): string | null => {
    try {
        return window.sessionStorage.getItem('referral_code');
    } catch (e) {
        return null;
    }
};

const RegisterContainer = () => {
    const { t } = useTranslation('auth');
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);

    useEffect(() => {
        clearFlashes();

        // Remember an invite link's code so it survives navigating between the auth pages.
        try {
            const invite = new URLSearchParams(window.location.search).get('ref');
            if (invite) window.sessionStorage.setItem('referral_code', invite);
        } catch (e) {
            // storage unavailable, the invite simply isn't remembered
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

        register({ ...values, recaptchaData: token, referralCode: readReferralCode() })
            .then((response) => {
                if (response.complete) {
                    // @ts-expect-error this is valid
                    window.location = response.intended || '/';
                }
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
            initialValues={{
                nameFirst: '',
                nameLast: '',
                username: '',
                email: '',
                password: '',
                passwordConfirmation: '',
            }}
            validationSchema={object().shape({
                nameFirst: string().required(t('register.validation.first_name_required')),
                nameLast: string().required(t('register.validation.last_name_required')),
                username: string().required(t('register.validation.username_required')),
                email: string()
                    .email(t('register.validation.email_invalid'))
                    .required(t('register.validation.email_required')),
                password: string()
                    .min(8, t('register.validation.password_min'))
                    .required(t('register.validation.password_required')),
                passwordConfirmation: string()
                    .oneOf([yupRef('password')], t('register.validation.password_confirmation_mismatch'))
                    .required(t('register.validation.password_confirmation_required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('register.title')} css={tw`w-full flex`}>
                    <div css={tw`grid grid-cols-2 gap-4`}>
                        <Field
                            light
                            type={'text'}
                            label={t('register.first_name_label')}
                            name={'nameFirst'}
                            disabled={isSubmitting}
                        />
                        <Field
                            light
                            type={'text'}
                            label={t('register.last_name_label')}
                            name={'nameLast'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'text'}
                            label={t('register.username_label')}
                            name={'username'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'email'}
                            label={t('register.email_label')}
                            name={'email'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'password'}
                            label={t('register.password_label')}
                            name={'password'}
                            description={t('register.password_description')}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            light
                            type={'password'}
                            label={t('register.confirm_password_label')}
                            name={'passwordConfirmation'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} isLoading={isSubmitting} disabled={isSubmitting}>
                            {t('register_button')}
                        </Button>
                    </div>
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
                            to={'/auth/login'}
                            css={tw`text-xs text-neutral-500 tracking-wide no-underline uppercase hover:text-neutral-600`}
                        >
                            {t('register.already_have_account')}
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;
