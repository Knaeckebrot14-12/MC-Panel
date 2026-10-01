import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faDatabase, faExternalLinkAlt, faEye, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import { useStoreState } from 'easy-peasy';
import openDatabaseManager from '@/api/server/databases/openDatabaseManager';
import Modal from '@/components/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/components/elements/Field';
import { object, string } from 'yup';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerContext } from '@/state/server';
import deleteServerDatabase from '@/api/server/databases/deleteServerDatabase';
import { httpErrorToHuman } from '@/api/http';
import RotatePasswordButton from '@/components/server/databases/RotatePasswordButton';
import Can from '@/components/elements/Can';
import { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Label from '@/components/elements/Label';
import Input from '@/components/elements/Input';
import GreyRowBox from '@/components/elements/GreyRowBox';
import CopyOnClick from '@/components/elements/CopyOnClick';

interface Props {
    database: ServerDatabase;
    className?: string;
}

export default ({ database, className }: Props) => {
    const { t } = useTranslation('server_databases');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();
    const [visible, setVisible] = useState(false);
    const [connectionVisible, setConnectionVisible] = useState(false);
    const [openingManager, setOpeningManager] = useState(false);
    const phpMyAdminUrl = useStoreState((state) => state.settings.data?.phpMyAdmin);

    const appendDatabase = ServerContext.useStoreActions((actions) => actions.databases.appendDatabase);
    const removeDatabase = ServerContext.useStoreActions((actions) => actions.databases.removeDatabase);

    const jdbcConnectionString = `jdbc:mysql://${database.username}${
        database.password ? `:${encodeURIComponent(database.password)}` : ''
    }@${database.connectionString}/${database.name}`;

    const schema = object().shape({
        confirm: string()
            .required(t('delete.confirm_required'))
            .oneOf([database.name.split('_', 2)[1], database.name], t('delete.confirm_required')),
    });

    const submit = (values: { confirm: string }, { setSubmitting }: FormikHelpers<{ confirm: string }>) => {
        clearFlashes();
        deleteServerDatabase(uuid, database.id)
            .then(() => {
                setVisible(false);
                setTimeout(() => removeDatabase(database.id), 150);
            })
            .catch((error) => {
                console.error(error);
                setSubmitting(false);
                addError({ key: 'database:delete', message: httpErrorToHuman(error) });
            });
    };

    // The tab is opened right away, inside the click, so popup blockers allow it; the one-time
    // sign-in URL is filled in once the panel has issued it.
    const openManager = (flashKey: string) => {
        clearFlashes(flashKey);
        const tab = window.open('', '_blank');
        if (tab) {
            tab.opener = null;
        }
        setOpeningManager(true);

        openDatabaseManager(uuid, database.id)
            .then((url) => {
                if (tab && !tab.closed) {
                    tab.location.href = url;
                } else {
                    window.location.href = url;
                }
            })
            .catch((error) => {
                console.error(error);
                tab?.close();
                addError({ key: flashKey, message: httpErrorToHuman(error) });
            })
            .then(() => setOpeningManager(false));
    };

    return (
        <>
            <Formik onSubmit={submit} initialValues={{ confirm: '' }} validationSchema={schema} isInitialValid={false}>
                {({ isSubmitting, isValid, resetForm }) => (
                    <Modal
                        visible={visible}
                        dismissable={!isSubmitting}
                        showSpinnerOverlay={isSubmitting}
                        onDismissed={() => {
                            setVisible(false);
                            resetForm();
                        }}
                    >
                        <FlashMessageRender byKey={'database:delete'} css={tw`mb-6`} />
                        <h2 css={tw`text-2xl mb-6`}>{t('delete.heading')}</h2>
                        <p css={tw`text-sm`}>
                            {t('delete.body_prefix')}
                            <strong>{database.name}</strong>
                            {t('delete.body_suffix')}
                        </p>
                        <Form css={tw`m-0 mt-6`}>
                            <Field
                                type={'text'}
                                id={'confirm_name'}
                                name={'confirm'}
                                label={t('delete.confirm_label')}
                                description={t('delete.confirm_description')}
                            />
                            <div css={tw`mt-6 text-right`}>
                                <Button type={'button'} isSecondary css={tw`mr-2`} onClick={() => setVisible(false)}>
                                    {t('delete.cancel')}
                                </Button>
                                <Button type={'submit'} color={'red'} disabled={!isValid}>
                                    {t('delete.delete_button')}
                                </Button>
                            </div>
                        </Form>
                    </Modal>
                )}
            </Formik>
            <Modal visible={connectionVisible} onDismissed={() => setConnectionVisible(false)}>
                <FlashMessageRender byKey={'database-connection-modal'} css={tw`mb-6`} />
                <h3 css={tw`mb-6 text-2xl`}>{t('connection.heading')}</h3>
                <div>
                    <Label>{t('labels.endpoint')}</Label>
                    <CopyOnClick text={database.connectionString}>
                        <Input type={'text'} readOnly value={database.connectionString} />
                    </CopyOnClick>
                </div>
                <div css={tw`mt-6`}>
                    <Label>{t('labels.connections_from')}</Label>
                    <Input type={'text'} readOnly value={database.allowConnectionsFrom} />
                </div>
                <div css={tw`mt-6`}>
                    <Label>{t('labels.username')}</Label>
                    <CopyOnClick text={database.username}>
                        <Input type={'text'} readOnly value={database.username} />
                    </CopyOnClick>
                </div>
                <Can action={'database.view_password'}>
                    <div css={tw`mt-6`}>
                        <Label>{t('connection.password_label')}</Label>
                        <CopyOnClick text={database.password} showInNotification={false}>
                            <Input type={'text'} readOnly value={database.password} />
                        </CopyOnClick>
                    </div>
                </Can>
                <div css={tw`mt-6`}>
                    <Label>{t('connection.jdbc_label')}</Label>
                    <CopyOnClick text={jdbcConnectionString} showInNotification={false}>
                        <Input type={'text'} readOnly value={jdbcConnectionString} />
                    </CopyOnClick>
                </div>
                {phpMyAdminUrl && (
                    <Can action={'database.view_password'}>
                        <div css={tw`mt-6`}>
                            <Label>{t('manager.url_label')}</Label>
                            <div css={tw`flex items-center`}>
                                <div css={tw`flex-1 min-w-0`}>
                                    <CopyOnClick text={phpMyAdminUrl}>
                                        <Input type={'text'} readOnly value={phpMyAdminUrl} />
                                    </CopyOnClick>
                                </div>
                                <Button
                                    type={'button'}
                                    css={tw`ml-2`}
                                    isLoading={openingManager}
                                    onClick={() => openManager('database-connection-modal')}
                                >
                                    <FontAwesomeIcon icon={faExternalLinkAlt} fixedWidth css={tw`mr-1`} />
                                    {t('manager.open')}
                                </Button>
                            </div>
                            <p css={tw`mt-1 text-xs text-neutral-400`}>{t('manager.url_description')}</p>
                        </div>
                    </Can>
                )}
                <div css={tw`mt-6 text-right`}>
                    <Can action={'database.update'}>
                        <RotatePasswordButton databaseId={database.id} onUpdate={appendDatabase} />
                    </Can>
                    <Button isSecondary onClick={() => setConnectionVisible(false)}>
                        {t('connection.close_button')}
                    </Button>
                </div>
            </Modal>
            <GreyRowBox $hoverable={false} className={className} css={tw`mb-2`}>
                <div css={tw`hidden md:block`}>
                    <FontAwesomeIcon icon={faDatabase} fixedWidth />
                </div>
                <div css={tw`flex-1 ml-4`}>
                    <CopyOnClick text={database.name}>
                        <p css={tw`text-lg`}>{database.name}</p>
                    </CopyOnClick>
                </div>
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <CopyOnClick text={database.connectionString}>
                        <p css={tw`text-sm`}>{database.connectionString}</p>
                    </CopyOnClick>
                    <p css={tw`mt-1 text-2xs text-neutral-500 uppercase select-none`}>{t('labels.endpoint')}</p>
                </div>
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <p css={tw`text-sm`}>{database.allowConnectionsFrom}</p>
                    <p css={tw`mt-1 text-2xs text-neutral-500 uppercase select-none`}>
                        {t('labels.connections_from')}
                    </p>
                </div>
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <CopyOnClick text={database.username}>
                        <p css={tw`text-sm`}>{database.username}</p>
                    </CopyOnClick>
                    <p css={tw`mt-1 text-2xs text-neutral-500 uppercase select-none`}>{t('labels.username')}</p>
                </div>
                <div css={tw`ml-8`}>
                    {phpMyAdminUrl && (
                        <Can action={'database.view_password'}>
                            <Button
                                isSecondary
                                css={tw`mr-2`}
                                title={t('manager.open')}
                                aria-label={t('manager.open')}
                                disabled={openingManager}
                                onClick={() => openManager('databases')}
                            >
                                <FontAwesomeIcon icon={faExternalLinkAlt} fixedWidth />
                            </Button>
                        </Can>
                    )}
                    <Button isSecondary css={tw`mr-2`} onClick={() => setConnectionVisible(true)}>
                        <FontAwesomeIcon icon={faEye} fixedWidth />
                    </Button>
                    <Can action={'database.delete'}>
                        <Button color={'red'} isSecondary onClick={() => setVisible(true)}>
                            <FontAwesomeIcon icon={faTrashAlt} fixedWidth />
                        </Button>
                    </Can>
                </div>
            </GreyRowBox>
        </>
    );
};
