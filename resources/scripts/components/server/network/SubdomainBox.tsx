import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Select from '@/components/elements/Select';
import CopyOnClick from '@/components/elements/CopyOnClick';
import Can from '@/components/elements/Can';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { deleteSubdomain, getSubdomain, setSubdomain, SubdomainOverview } from '@/api/server/subdomain';

/**
 * "myserver.play.example.com" for the server (Admin -> Settings -> Subdomains has to be set up).
 */
export default () => {
    const { t } = useTranslation('server_subdomain');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [data, setData] = useState<SubdomainOverview | null>(null);
    const [name, setName] = useState('');
    const [domain, setDomain] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        getSubdomain(uuid)
            .then((overview) => {
                setData(overview);
                setName(overview.current?.name || '');
                setDomain(overview.current?.domain || overview.domains[0] || '');
            })
            .catch(() => setData(null));
    }, []);

    if (!data || !data.enabled) return null;

    const save = () => {
        clearFlashes('server:network');
        setBusy(true);
        setSubdomain(uuid, name, domain)
            .then((current) => {
                setData({ ...data, current });
                addFlash({ key: 'server:network', type: 'success', message: t('saved', { fqdn: current.fqdn }) });
            })
            .catch((error) => addError({ key: 'server:network', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    const remove = () => {
        clearFlashes('server:network');
        setBusy(true);
        deleteSubdomain(uuid)
            .then(() => {
                setData({ ...data, current: null });
                setName('');
            })
            .catch((error) => addError({ key: 'server:network', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    return (
        <TitledGreyBox title={t('title')} css={tw`mb-6`}>
            {data.current && (
                <p css={tw`text-sm mb-4`}>
                    {t('current')}{' '}
                    <CopyOnClick text={data.current.fqdn}>
                        <code css={tw`font-mono bg-neutral-900 rounded px-2 py-1`}>{data.current.fqdn}</code>
                    </CopyOnClick>
                </p>
            )}
            <Can action={'allocation.update'}>
                <div css={tw`flex flex-wrap items-center gap-2`}>
                    <div css={tw`flex-1`} style={{ minWidth: '10rem' }}>
                        <Input
                            value={name}
                            maxLength={32}
                            placeholder={t('name_placeholder')}
                            onChange={(e) => setName(e.currentTarget.value.toLowerCase().replace(/[^a-z0-9-]/g, ''))}
                            disabled={busy}
                        />
                    </div>
                    <span css={tw`text-neutral-300`}>.</span>
                    <div css={tw`flex-1`} style={{ minWidth: '10rem' }}>
                        <Select value={domain} onChange={(e) => setDomain(e.currentTarget.value)} disabled={busy}>
                            {data.domains.map((d) => (
                                <option key={d} value={d}>
                                    {d}
                                </option>
                            ))}
                        </Select>
                    </div>
                    <Button type={'button'} onClick={save} isLoading={busy} disabled={busy || name.length < 3}>
                        {data.current ? t('change') : t('create')}
                    </Button>
                    {data.current && (
                        <Button type={'button'} color={'red'} isSecondary onClick={remove} disabled={busy}>
                            {t('remove')}
                        </Button>
                    )}
                </div>
                <p css={tw`text-xs text-neutral-400 mt-2`}>{t('hint')}</p>
            </Can>
        </TitledGreyBox>
    );
};
