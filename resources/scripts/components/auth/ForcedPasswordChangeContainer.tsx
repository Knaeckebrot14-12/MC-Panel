import React from 'react';
import { useTranslation } from 'react-i18next';
import { Formik, FormikHelpers } from 'formik';
import { object, string, ref as yupRef } from 'yup';
import tw from 'twin.macro';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import Field from '@/components/elements/Field';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import updateAccountPassword from '@/api/account/updateAccountPassword';

interface Values {
    current: string;
    password: string;
    confirmPassword: string;
}

export default () => {
    const { t } = useTranslation('auth');
    const { clearFlashes, addFlash } = useFlash();

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        updateAccountPassword({ ...values })
            .then(() => {
                // The backend invalidates the current session as part of a password
                // change, so send the user back through a fresh login with their
                // new password instead of trying to keep the SPA state around.
                window.location.href = '/auth/login';
            })
            .catch((error) => {
                console.error(error);
                addFlash({ type: 'error', title: t('error', { ns: 'strings' }), message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    return (
        <div css={tw`pt-8 xl:pt-32`}>
            <Formik
                onSubmit={submit}
                initialValues={{ current: '', password: '', confirmPassword: '' }}
                validationSchema={object().shape({
                    current: string().required(t('forced_change.validation.current_required')),
                    password: string().min(8, t('forced_change.validation.password_min')).required(),
                    confirmPassword: string()
                        .oneOf([yupRef('password'), ''], t('forced_change.validation.confirmation_mismatch'))
                        .required(),
                })}
            >
                {({ isSubmitting }) => (
                    <LoginFormContainer title={t('forced_change.title')} css={tw`w-full flex`}>
                        <p css={tw`text-sm text-neutral-300 mb-6`}>{t('forced_change.body')}</p>
                        <Field
                            light
                            type={'password'}
                            id={'current_password'}
                            name={'current'}
                            label={t('forced_change.temporary_password_label')}
                        />
                        <div css={tw`mt-6`}>
                            <Field
                                light
                                type={'password'}
                                id={'new_password'}
                                name={'password'}
                                label={t('forced_change.new_password_label')}
                            />
                        </div>
                        <div css={tw`mt-6`}>
                            <Field
                                light
                                type={'password'}
                                id={'confirm_password'}
                                name={'confirmPassword'}
                                label={t('forced_change.confirm_new_password_label')}
                            />
                        </div>
                        <div css={tw`mt-6`}>
                            <Button type={'submit'} size={'xlarge'} disabled={isSubmitting} isLoading={isSubmitting}>
                                {t('forced_change.update_button')}
                            </Button>
                        </div>
                    </LoginFormContainer>
                )}
            </Formik>
        </div>
    );
};
