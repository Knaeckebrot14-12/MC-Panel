import React, { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import FlashMessageRender from '@/components/FlashMessageRender';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Select from '@/components/elements/Select';
import Spinner from '@/components/elements/Spinner';
import { Alert } from '@/components/elements/alert';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getFileContents from '@/api/server/files/getFileContents';
import saveFileContents from '@/api/server/files/saveFileContents';
import ServerListEditor from '@/components/server/properties/ServerListEditor';

const FILE = 'server.properties';

type Field =
    | { key: string; type: 'bool'; def: string }
    | { key: string; type: 'int'; def: string; min?: number; max?: number }
    | { key: string; type: 'text'; def: string }
    | { key: string; type: 'enum'; def: string; options: string[] };

const GROUPS: { id: string; fields: Field[] }[] = [
    {
        id: 'general',
        fields: [
            { key: 'max-players', type: 'int', def: '20', min: 1, max: 10000 },
            {
                key: 'gamemode',
                type: 'enum',
                def: 'survival',
                options: ['survival', 'creative', 'adventure', 'spectator'],
            },
            { key: 'difficulty', type: 'enum', def: 'easy', options: ['peaceful', 'easy', 'normal', 'hard'] },
            { key: 'hardcore', type: 'bool', def: 'false' },
            { key: 'pvp', type: 'bool', def: 'true' },
            { key: 'force-gamemode', type: 'bool', def: 'false' },
            { key: 'allow-flight', type: 'bool', def: 'false' },
        ],
    },
    {
        id: 'world',
        fields: [
            { key: 'level-name', type: 'text', def: 'world' },
            { key: 'level-seed', type: 'text', def: '' },
            {
                key: 'level-type',
                type: 'enum',
                def: 'minecraft\\:normal',
                options: [
                    'minecraft\\:normal',
                    'minecraft\\:flat',
                    'minecraft\\:large_biomes',
                    'minecraft\\:amplified',
                ],
            },
            { key: 'allow-nether', type: 'bool', def: 'true' },
            { key: 'generate-structures', type: 'bool', def: 'true' },
            { key: 'spawn-monsters', type: 'bool', def: 'true' },
            { key: 'spawn-npcs', type: 'bool', def: 'true' },
            { key: 'spawn-protection', type: 'int', def: '16', min: 0, max: 1000 },
            { key: 'view-distance', type: 'int', def: '10', min: 2, max: 32 },
            { key: 'simulation-distance', type: 'int', def: '10', min: 2, max: 32 },
            { key: 'max-world-size', type: 'int', def: '29999984', min: 1, max: 29999984 },
        ],
    },
    {
        id: 'access',
        fields: [
            { key: 'white-list', type: 'bool', def: 'false' },
            { key: 'enforce-whitelist', type: 'bool', def: 'false' },
            { key: 'online-mode', type: 'bool', def: 'true' },
            { key: 'enforce-secure-profile', type: 'bool', def: 'true' },
            { key: 'enable-command-block', type: 'bool', def: 'false' },
            { key: 'op-permission-level', type: 'int', def: '4', min: 1, max: 4 },
            { key: 'player-idle-timeout', type: 'int', def: '0', min: 0, max: 100000 },
        ],
    },
    {
        id: 'resource_pack',
        fields: [
            { key: 'resource-pack', type: 'text', def: '' },
            { key: 'require-resource-pack', type: 'bool', def: 'false' },
        ],
    },
];

// Managed by the panel through the server's allocation; changing them here would break the connection.
const LOCKED = ['server-port', 'server-ip', 'query.port', 'rcon.port', 'rcon.password', 'enable-rcon'];

// The MOTD has its own editor with a server list preview (ServerListEditor).
const KNOWN = ['motd', ...GROUPS.flatMap((group) => group.fields.map((field) => field.key))];

const parse = (content: string): Record<string, string> => {
    const values: Record<string, string> = {};
    content.split(/\r?\n/).forEach((line) => {
        if (!line.trim() || line.trimStart().startsWith('#') || !line.includes('=')) return;
        const index = line.indexOf('=');
        values[line.substring(0, index).trim()] = line.substring(index + 1);
    });
    return values;
};

// Replaces changed values in place, keeps comments and order, appends keys that weren't there yet.
const serialize = (content: string, changes: Record<string, string>): string => {
    const pending = { ...changes };
    const lines = content.split(/\r?\n/).map((line) => {
        if (!line.includes('=') || line.trimStart().startsWith('#')) return line;
        const key = line.substring(0, line.indexOf('=')).trim();
        if (key in pending) {
            const value = pending[key];
            delete pending[key];
            return `${key}=${value}`;
        }
        return line;
    });
    while (lines.length && lines[lines.length - 1] === '') lines.pop();
    Object.entries(pending).forEach(([key, value]) => lines.push(`${key}=${value}`));
    return lines.join('\n') + '\n';
};

export default () => {
    const { t } = useTranslation('server_properties');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [original, setOriginal] = useState<string | null>(null);
    const [missing, setMissing] = useState(false);
    const [values, setValues] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);

    const load = () => {
        clearFlashes('properties');
        getFileContents(uuid, FILE)
            .then((content) => {
                setOriginal(content);
                setValues(parse(content));
                setMissing(false);
            })
            .catch((error) => {
                if (error?.response?.status === 404) {
                    setOriginal('');
                    setValues({});
                    setMissing(true);
                } else {
                    addError({ key: 'properties', message: httpErrorToHuman(error) });
                }
            });
    };

    useEffect(() => {
        load();
    }, []);

    const parsed = useMemo(() => (original === null ? {} : parse(original)), [original]);
    const changes = useMemo(() => {
        const diff: Record<string, string> = {};
        Object.entries(values).forEach(([key, value]) => {
            if (LOCKED.includes(key)) return;
            if (parsed[key] !== value) diff[key] = value;
        });
        return diff;
    }, [values, parsed]);
    const changeCount = Object.keys(changes).length;

    const valueOf = (field: Field) => values[field.key] ?? field.def;
    const set = (key: string, value: string) => setValues((state) => ({ ...state, [key]: value }));

    const save = () => {
        if (original === null) return;
        clearFlashes('properties');
        setSaving(true);
        const content = serialize(original, changes);
        saveFileContents(uuid, FILE, content)
            .then(() => {
                setOriginal(content);
                addFlash({
                    key: 'properties',
                    type: 'success',
                    message: status === 'offline' ? t('saved') : t('saved_restart'),
                });
            })
            .catch((error) => addError({ key: 'properties', message: httpErrorToHuman(error) }))
            .then(() => setSaving(false));
    };

    if (original === null) {
        return (
            <ServerContentBlock title={t('title')}>
                <FlashMessageRender byKey={'properties'} css={tw`mb-4`} />
                <Spinner size={'large'} centered />
            </ServerContentBlock>
        );
    }

    const others = Object.keys(values).filter((key) => !KNOWN.includes(key));

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'properties'} css={tw`mb-4`} />
            {missing && (
                <Alert type={'info'} className={'mb-4'}>
                    {t('missing')}
                </Alert>
            )}

            <ServerListEditor
                motd={values['motd'] ?? 'A Minecraft Server'}
                maxPlayers={values['max-players'] ?? '20'}
                changed={'motd' in changes}
                onChange={(raw) => set('motd', raw)}
            />

            <div css={tw`grid grid-cols-1 lg:grid-cols-2 gap-4`}>
                {GROUPS.map((group) => (
                    <TitledGreyBox key={group.id} title={t(`groups.${group.id}`)}>
                        <div css={tw`space-y-4`}>
                            {group.fields.map((field) => (
                                <div key={field.key}>
                                    <div css={tw`flex items-center justify-between gap-4`}>
                                        <label htmlFor={`prop-${field.key}`} css={tw`text-sm text-neutral-100`}>
                                            {t(`fields.${field.key}.label`)}
                                            {field.key in changes && (
                                                <span css={tw`ml-2 text-xs text-yellow-400`}>●</span>
                                            )}
                                        </label>
                                        {field.type === 'bool' && (
                                            <input
                                                id={`prop-${field.key}`}
                                                type={'checkbox'}
                                                css={tw`w-5 h-5 cursor-pointer`}
                                                checked={valueOf(field) === 'true'}
                                                onChange={(e) =>
                                                    set(field.key, e.currentTarget.checked ? 'true' : 'false')
                                                }
                                            />
                                        )}
                                    </div>
                                    {field.type === 'enum' && (
                                        <Select
                                            id={`prop-${field.key}`}
                                            css={tw`mt-1`}
                                            value={valueOf(field)}
                                            onChange={(e: React.ChangeEvent<HTMLSelectElement>) =>
                                                set(field.key, e.currentTarget.value)
                                            }
                                        >
                                            {!field.options.includes(valueOf(field)) && (
                                                <option value={valueOf(field)}>{valueOf(field)}</option>
                                            )}
                                            {field.options.map((option) => (
                                                <option key={option} value={option}>
                                                    {t(`options.${option.replace('minecraft\\:', '')}`)}
                                                </option>
                                            ))}
                                        </Select>
                                    )}
                                    {(field.type === 'int' || field.type === 'text') && (
                                        <Input
                                            id={`prop-${field.key}`}
                                            css={tw`mt-1`}
                                            type={field.type === 'int' ? 'number' : 'text'}
                                            min={field.type === 'int' ? field.min : undefined}
                                            max={field.type === 'int' ? field.max : undefined}
                                            value={valueOf(field)}
                                            onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                set(field.key, e.currentTarget.value)
                                            }
                                        />
                                    )}
                                    <p css={tw`text-xs text-neutral-400 mt-1`}>
                                        {t(`fields.${field.key}.description`)}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </TitledGreyBox>
                ))}
            </div>

            {others.length > 0 && (
                <TitledGreyBox title={t('groups.other')} css={tw`mt-4`}>
                    <div css={tw`grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2`}>
                        {others.map((key) => (
                            <div key={key}>
                                <label htmlFor={`prop-${key}`} css={tw`text-xs text-neutral-300 font-mono`}>
                                    {key}
                                </label>
                                <Input
                                    id={`prop-${key}`}
                                    value={values[key]}
                                    disabled={LOCKED.includes(key)}
                                    title={LOCKED.includes(key) ? t('locked') : undefined}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                        set(key, e.currentTarget.value)
                                    }
                                />
                            </div>
                        ))}
                    </div>
                    <p css={tw`text-xs text-neutral-400 mt-3`}>{t('locked')}</p>
                </TitledGreyBox>
            )}

            <div
                css={tw`sticky bottom-0 mt-6 py-3 flex items-center justify-end gap-3 bg-neutral-800 border-t border-neutral-700`}
            >
                {changeCount > 0 && <p css={tw`text-sm text-yellow-400`}>{t('unsaved', { count: changeCount })}</p>}
                <Button
                    color={'grey'}
                    disabled={saving || changeCount === 0}
                    onClick={() => setValues(parse(original))}
                >
                    {t('reset')}
                </Button>
                <Button disabled={saving || changeCount === 0} isLoading={saving} onClick={save}>
                    {t('save')}
                </Button>
            </div>
        </ServerContentBlock>
    );
};
