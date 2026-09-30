import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useHistory } from 'react-router-dom';
import { Formik, Form, FormikHelpers, useField } from 'formik';
import { object, string } from 'yup';
import tw from 'twin.macro';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import PageContentBlock from '@/components/elements/PageContentBlock';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Button from '@/components/elements/Button';
import Field from '@/components/elements/Field';
import Select from '@/components/elements/Select';
import Spinner from '@/components/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getCoinOptions, { CoinOptions } from '@/api/coins/getCoinOptions';
import getSelfServiceServerOptions, { SelfServiceServerOptions } from '@/api/getSelfServiceServerOptions';
import purchaseShopResource, { ShopResourceType } from '@/api/coins/purchaseShopResource';
import purchaseShopServer from '@/api/coins/purchaseShopServer';

const FormikNativeSelect = ({ name, children }: { name: string; children: React.ReactNode }) => {
    const [field] = useField(name);
    return <Select {...field}>{children}</Select>;
};

const ResourceRow = ({
    label,
    unitLabel,
    price,
    buying,
    onBuy,
}: {
    label: string;
    unitLabel: string;
    price: number;
    buying: boolean;
    onBuy: (quantity: number) => void;
}) => {
    const { t } = useTranslation('coins');
    const [quantity, setQuantity] = useState(1);

    return (
        <GreyRowBox css={tw`mb-2 last:mb-0 items-center`}>
            <div css={tw`flex-1`}>
                <p css={tw`text-sm`}>{label}</p>
                <p css={tw`text-xs text-neutral-400`}>{t('shop.price_per_unit', { price, unit: unitLabel })}</p>
            </div>
            <input
                type={'number'}
                min={1}
                max={100}
                value={quantity}
                onChange={(e) => setQuantity(Math.max(1, Number(e.target.value) || 1))}
                css={tw`w-16 mr-4 bg-neutral-900 border border-neutral-600 rounded text-center text-sm p-1`}
            />
            <Button size={'small'} isLoading={buying} disabled={buying} onClick={() => onBuy(quantity)}>
                {t('shop.buy_button', { cost: price * quantity })}
            </Button>
        </GreyRowBox>
    );
};

interface ServerFormValues {
    name: string;
    eggId: string;
    nodeId: string;
    planId: string;
}

export default () => {
    const { t } = useTranslation('coins');
    const history = useHistory();
    const [options, setOptions] = useState<CoinOptions | null>(null);
    const [serverOptions, setServerOptions] = useState<SelfServiceServerOptions | null>(null);
    const [buying, setBuying] = useState<ShopResourceType | null>(null);
    const [buyingServer, setBuyingServer] = useState(false);
    const { clearFlashes, addFlash, clearAndAddHttpError } = useFlash();
    const setBalance = useStoreActions((actions: Actions<ApplicationStore>) => actions.coins.setBalance);

    const refresh = () => {
        getCoinOptions().then((data) => {
            setOptions(data);
            setBalance(data.balance);
        });
        getSelfServiceServerOptions().then(setServerOptions);
    };

    useEffect(() => {
        clearFlashes('coins:shop');
        refresh();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const buyResource = (type: ShopResourceType, quantity: number) => {
        setBuying(type);
        clearFlashes('coins:shop');

        purchaseShopResource(type, quantity)
            .then((response) => {
                setBalance(response.balance);
                setOptions((current) => (current ? { ...current, balance: response.balance } : current));
                addFlash({ key: 'coins:shop', type: 'success', message: t('shop.purchase_success') });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'coins:shop', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setBuying(null));
    };

    const buyServer = (values: ServerFormValues, { setSubmitting }: FormikHelpers<ServerFormValues>) => {
        clearFlashes('coins:shop');
        setBuyingServer(true);

        purchaseShopServer({
            name: values.name,
            nodeId: Number(values.nodeId),
            eggId: Number(values.eggId),
            planId: values.planId ? Number(values.planId) : undefined,
        })
            .then((identifier) => {
                history.push(`/server/${identifier}`);
            })
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'coins:shop', error });
                setSubmitting(false);
                setBuyingServer(false);
            });
    };

    if (!options || !serverOptions) {
        return (
            <PageContentBlock title={t('shop.title')} showFlashKey={'coins:shop'}>
                <Spinner centered size={'large'} />
            </PageContentBlock>
        );
    }

    const firstEgg = serverOptions.nests.find((n) => n.eggs.length > 0)?.eggs[0];
    const firstNode = serverOptions.nodes[0];
    const plans = options.shop.server.plans;

    return (
        <PageContentBlock title={t('shop.title')} showFlashKey={'coins:shop'}>
            <h1 css={tw`text-5xl mb-2`}>{t('shop.title')}</h1>
            <p css={tw`text-sm text-neutral-400 mb-8`}>{t('balance_line', { balance: options.balance })}</p>

            <TitledGreyBox title={t('shop.expand_pool_title')} css={tw`mb-8`}>
                <ResourceRow
                    label={t('shop.resource.memory')}
                    unitLabel={`${options.shop.memoryUnitMib} MiB`}
                    price={options.shop.memoryPrice}
                    buying={buying === 'memory'}
                    onBuy={(q) => buyResource('memory', q)}
                />
                <ResourceRow
                    label={t('shop.resource.disk')}
                    unitLabel={`${options.shop.diskUnitMib} MiB`}
                    price={options.shop.diskPrice}
                    buying={buying === 'disk'}
                    onBuy={(q) => buyResource('disk', q)}
                />
                <ResourceRow
                    label={t('shop.resource.cpu')}
                    unitLabel={`${options.shop.cpuUnitPercent}%`}
                    price={options.shop.cpuPrice}
                    buying={buying === 'cpu'}
                    onBuy={(q) => buyResource('cpu', q)}
                />
                <ResourceRow
                    label={t('shop.resource.backups')}
                    unitLabel={t('shop.slot_unit')}
                    price={options.shop.backupPrice}
                    buying={buying === 'backups'}
                    onBuy={(q) => buyResource('backups', q)}
                />
                <ResourceRow
                    label={t('shop.resource.slots')}
                    unitLabel={t('shop.slot_unit')}
                    price={options.shop.slotPrice}
                    buying={buying === 'slots'}
                    onBuy={(q) => buyResource('slots', q)}
                />
            </TitledGreyBox>

            <TitledGreyBox title={t('shop.buy_server_title')}>
                {plans.length === 0 && (
                    <p css={tw`text-sm text-neutral-300 mb-4`}>
                        {t('shop.buy_server_body', {
                            memory: options.shop.server.memory,
                            disk: options.shop.server.disk,
                            cpu: options.shop.server.cpu,
                            backups: options.shop.server.backups,
                            price: options.shop.server.monthlyPrice,
                        })}
                    </p>
                )}
                {!firstEgg || !firstNode ? (
                    <p css={tw`text-sm text-neutral-400`}>{t('shop.no_nodes')}</p>
                ) : (
                    <Formik
                        onSubmit={buyServer}
                        initialValues={{
                            name: '',
                            eggId: String(firstEgg.id),
                            nodeId: String(firstNode.id),
                            planId: plans.length > 0 ? String(plans[0].id) : '',
                        }}
                        validationSchema={object().shape({
                            name: string().required(t('shop.server_name_required')).min(1).max(191),
                            eggId: string().required(),
                            nodeId: string().required(),
                        })}
                    >
                        {({ isSubmitting, values }) => {
                            const selectedPlan = plans.find((p) => String(p.id) === values.planId);
                            const price = selectedPlan ? selectedPlan.monthlyPrice : options.shop.server.monthlyPrice;

                            return (
                            <Form>
                                {plans.length > 0 && (
                                    <div css={tw`mb-4`}>
                                        <label css={tw`block text-xs uppercase text-neutral-300 mb-1`}>
                                            {t('shop.plan_label')}
                                        </label>
                                        <FormikNativeSelect name={'planId'}>
                                            {plans.map((plan) => (
                                                <option key={plan.id} value={plan.id}>
                                                    {plan.name} — {plan.monthlyPrice}
                                                </option>
                                            ))}
                                        </FormikNativeSelect>
                                        {selectedPlan && (
                                            <p css={tw`text-xs text-neutral-400 mt-2`}>
                                                {selectedPlan.description && <>{selectedPlan.description}<br /></>}
                                                {t('shop.plan_summary', {
                                                    memory: selectedPlan.memory,
                                                    disk: selectedPlan.disk,
                                                    cpu: selectedPlan.cpu,
                                                    backups: selectedPlan.backups,
                                                    price: selectedPlan.monthlyPrice,
                                                })}
                                            </p>
                                        )}
                                    </div>
                                )}
                                <Field
                                    id={'name'}
                                    name={'name'}
                                    type={'text'}
                                    label={t('shop.server_name_label')}
                                    placeholder={t('shop.server_name_placeholder')}
                                    disabled={isSubmitting || buyingServer}
                                />
                                <div css={tw`grid grid-cols-2 gap-4 mt-4`}>
                                    <div>
                                        <label css={tw`block text-xs uppercase text-neutral-300 mb-1`}>
                                            {t('shop.egg_label')}
                                        </label>
                                        <FormikNativeSelect name={'eggId'}>
                                            {serverOptions.nests.map((nest) => (
                                                <optgroup key={nest.id} label={nest.name}>
                                                    {nest.eggs.map((egg) => (
                                                        <option key={egg.id} value={egg.id}>
                                                            {egg.name}
                                                        </option>
                                                    ))}
                                                </optgroup>
                                            ))}
                                        </FormikNativeSelect>
                                    </div>
                                    <div>
                                        <label css={tw`block text-xs uppercase text-neutral-300 mb-1`}>
                                            {t('shop.node_label')}
                                        </label>
                                        <FormikNativeSelect name={'nodeId'}>
                                            {serverOptions.nodes.map((node) => (
                                                <option key={node.id} value={node.id}>
                                                    {node.name}
                                                </option>
                                            ))}
                                        </FormikNativeSelect>
                                    </div>
                                </div>
                                <div css={tw`mt-6`}>
                                    <Button
                                        type={'submit'}
                                        size={'xlarge'}
                                        isLoading={isSubmitting || buyingServer}
                                        disabled={isSubmitting || buyingServer || options.balance < price}
                                    >
                                        {t('shop.buy_button', { cost: price })}
                                    </Button>
                                </div>
                            </Form>
                            );
                        }}
                    </Formik>
                )}
            </TitledGreyBox>
        </PageContentBlock>
    );
};
