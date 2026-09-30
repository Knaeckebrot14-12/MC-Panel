import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useHistory } from 'react-router-dom';
import { Form, Formik, FormikHelpers, useField } from 'formik';
import { object, string } from 'yup';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Field from '@/components/elements/Field';
import Select from '@/components/elements/Select';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import { createTicket, getTicketServers } from '@/api/tickets/tickets';

interface Values {
    subject: string;
    category: string;
    priority: string;
    serverId: string;
    message: string;
}

const NativeSelect = ({ name, children }: { name: string; children: React.ReactNode }) => {
    const [field] = useField(name);
    return <Select {...field}>{children}</Select>;
};

const MessageField = () => {
    const { t } = useTranslation('tickets');
    const [field, meta] = useField('message');

    return (
        <div css={tw`mt-6`}>
            <label htmlFor={'message'} css={tw`text-xs uppercase text-neutral-200 block mb-1`}>
                {t('new.message')}
            </label>
            <textarea
                id={'message'}
                {...field}
                rows={8}
                css={tw`w-full bg-neutral-600 border border-neutral-500 rounded p-3 text-sm text-neutral-100`}
            />
            {meta.touched && meta.error && <p css={tw`text-xs text-red-400 mt-1`}>{meta.error}</p>}
        </div>
    );
};

export default () => {
    const { t } = useTranslation('tickets');
    const history = useHistory();
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [servers, setServers] = useState<{ id: number; name: string }[]>([]);

    useEffect(() => {
        getTicketServers()
            .then(setServers)
            .catch(() => setServers([]));
    }, []);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('tickets:new');
        createTicket({
            subject: values.subject,
            category: values.category,
            priority: values.priority,
            serverId: values.serverId ? Number(values.serverId) : null,
            message: values.message,
        })
            .then((ticket) => history.push(`/tickets/${ticket.id}`))
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'tickets:new', error });
                setSubmitting(false);
            });
    };

    return (
        <PageContentBlock title={t('new.title')} showFlashKey={'tickets:new'}>
            <h1 css={tw`text-5xl mb-8`}>{t('new.title')}</h1>
            <TitledGreyBox title={t('new.box_title')}>
                <Formik<Values>
                    onSubmit={submit}
                    initialValues={{ subject: '', category: 'general', priority: 'normal', serverId: '', message: '' }}
                    validationSchema={object().shape({
                        subject: string().min(3, t('new.subject_short')).max(191).required(t('new.subject_required')),
                        message: string().min(5, t('new.message_short')).max(5000).required(t('new.message_required')),
                    })}
                >
                    {({ isSubmitting }) => (
                        <Form>
                            <Field name={'subject'} label={t('new.subject')} />
                            <div css={tw`grid grid-cols-1 md:grid-cols-3 gap-4 mt-6`}>
                                <div>
                                    <label css={tw`text-xs uppercase text-neutral-200 block mb-1`}>
                                        {t('new.category')}
                                    </label>
                                    <NativeSelect name={'category'}>
                                        {['general', 'technical', 'billing', 'other'].map((c) => (
                                            <option key={c} value={c}>
                                                {t(`categories.${c}`)}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                </div>
                                <div>
                                    <label css={tw`text-xs uppercase text-neutral-200 block mb-1`}>
                                        {t('new.priority')}
                                    </label>
                                    <NativeSelect name={'priority'}>
                                        {['low', 'normal', 'high'].map((p) => (
                                            <option key={p} value={p}>
                                                {t(`priorities.${p}`)}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                </div>
                                <div>
                                    <label css={tw`text-xs uppercase text-neutral-200 block mb-1`}>
                                        {t('new.server')}
                                    </label>
                                    <NativeSelect name={'serverId'}>
                                        <option value={''}>{t('new.no_server')}</option>
                                        {servers.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.name}
                                            </option>
                                        ))}
                                    </NativeSelect>
                                </div>
                            </div>
                            <MessageField />
                            <div css={tw`mt-6 text-right`}>
                                <Button type={'submit'} isLoading={isSubmitting} disabled={isSubmitting}>
                                    {t('new.submit')}
                                </Button>
                            </div>
                        </Form>
                    )}
                </Formik>
            </TitledGreyBox>
        </PageContentBlock>
    );
};
