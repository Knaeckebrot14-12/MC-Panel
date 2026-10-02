import React, { useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCheck, faChevronDown, faDownload, faSearch, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import FlashMessageRender from '@/components/FlashMessageRender';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Can from '@/components/elements/Can';
import Spinner from '@/components/elements/Spinner';
import { Dialog } from '@/components/elements/dialog';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import { bytesToString } from '@/lib/formatters';
import loadDirectory, { FileObject } from '@/api/server/files/loadDirectory';
import deleteFiles from '@/api/server/files/deleteFiles';
import {
    CURATED_PLUGIN_LISTS,
    ModrinthSearchHit,
    getCuratedPlugins,
    getInstallableModrinthFile,
    searchModrinthPlugins,
} from '@/api/server/plugins/modrinth';
import pullFile from '@/api/server/plugins/pullFile';
import GeyserBox from '@/components/server/plugins/GeyserBox';

type InstallState = 'idle' | 'installing' | 'installed' | 'error';
type View = 'search' | 'citybuild' | 'pvp' | 'installed';
type StatusFilter = 'all' | 'installed' | 'not_installed';

const PLUGINS_DIRECTORY = '/plugins';

// Reduces a file or directory name down to a comparable "core" token so that
// e.g. "LuckPerms-Bukkit-5.5.71.jar" and the "LuckPerms" data folder it
// creates can be recognised as belonging to the same plugin, without being
// thrown off by version numbers baked into the jar's filename.
const coreName = (name: string): string => {
    const withoutExtension = name.replace(/\.[^.]+$/, '');
    const tokens = withoutExtension.split(/[-_ ]+/);
    const kept: string[] = [];
    for (const token of tokens) {
        if (/^\d/.test(token)) break;
        kept.push(token);
    }
    return (kept.length ? kept : tokens)
        .join('')
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '');
};

// True if two "core" tokens plausibly refer to the same plugin: either an
// exact match, or one fully contains the other and the shorter one is long
// enough that the match isn't just coincidental (e.g. avoids "tab" matching
// half the alphabet).
const isRelatedKey = (a: string, b: string): boolean => {
    if (!a || !b) return false;
    if (a === b) return true;
    const [shorter, longer] = a.length <= b.length ? [a, b] : [b, a];
    return shorter.length >= 4 && longer.includes(shorter);
};

// Clusters the raw contents of /plugins into one group per plugin — a jar
// plus whatever data folder(s) it created (e.g. "LuckPerms-Bukkit-5.5.71.jar"
// and the "LuckPerms" folder) — so they can be shown, and deleted, as a
// single unit instead of as unrelated rows.
const groupInstalledFiles = (files: FileObject[]): FileObject[][] => {
    const groups: FileObject[][] = [];
    const used = new Set<string>();

    files.forEach((file) => {
        if (used.has(file.key)) return;

        const group = [file];
        used.add(file.key);

        // Keep absorbing files related to anything already in the group, so
        // e.g. a jar pulls in its folder even if they weren't adjacent.
        let expanded = true;
        while (expanded) {
            expanded = false;
            files.forEach((candidate) => {
                if (used.has(candidate.key)) return;
                if (group.some((member) => isRelatedKey(coreName(member.name), coreName(candidate.name)))) {
                    group.push(candidate);
                    used.add(candidate.key);
                    expanded = true;
                }
            });
        }

        groups.push(group);
    });

    return groups;
};

const FilterMenuItem = ({
    active,
    onClick,
    children,
}: {
    active: boolean;
    onClick: () => void;
    children: React.ReactNode;
}) => (
    <button
        type={'button'}
        onClick={onClick}
        css={[
            tw`flex items-center justify-between w-full px-3 py-2 text-sm text-left transition-colors duration-150 hover:bg-neutral-600`,
            active ? tw`text-primary-300` : tw`text-neutral-200`,
        ]}
    >
        {children}
        {active && <FontAwesomeIcon icon={faCheck} css={tw`text-2xs`} />}
    </button>
);

export default () => {
    const { t } = useTranslation('server_plugins');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const variables = ServerContext.useStoreState((state) => state.server.data!.variables);
    const { addError, clearFlashes, addFlash } = useFlash();

    const minecraftVersion = variables.find((v) => v.envVariable === 'MINECRAFT_VERSION')?.serverValue;

    const [view, setView] = useState<View>('search');
    const [statusFilter, setStatusFilter] = useState<StatusFilter>('all');
    const [filterMenuOpen, setFilterMenuOpen] = useState(false);
    const filterCloseTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);

    const openFilterMenu = () => {
        if (filterCloseTimeout.current) {
            clearTimeout(filterCloseTimeout.current);
            filterCloseTimeout.current = null;
        }
        setFilterMenuOpen(true);
    };

    const scheduleFilterMenuClose = () => {
        filterCloseTimeout.current = setTimeout(() => setFilterMenuOpen(false), 2000);
    };

    useEffect(
        () => () => {
            if (filterCloseTimeout.current) clearTimeout(filterCloseTimeout.current);
        },
        []
    );

    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [hasSearched, setHasSearched] = useState(false);
    const [results, setResults] = useState<ModrinthSearchHit[]>([]);
    const [installState, setInstallState] = useState<Record<string, InstallState>>({});
    const [pendingDelete, setPendingDelete] = useState<{ label: string; names: string[] } | null>(null);
    const [deleting, setDeleting] = useState<Record<string, boolean>>({});

    const [installedFiles, setInstalledFiles] = useState<FileObject[]>([]);
    const [installedLoading, setInstalledLoading] = useState(true);

    const refreshInstalled = () => {
        setInstalledLoading(true);
        loadDirectory(uuid, PLUGINS_DIRECTORY)
            .then((files) => setInstalledFiles(files))
            .catch(() => setInstalledFiles([]))
            .then(() => setInstalledLoading(false));
    };

    useEffect(() => {
        refreshInstalled();
    }, []);

    const doSearch = (q: string) => {
        clearFlashes('plugins');
        setView('search');
        setLoading(true);
        setHasSearched(true);

        searchModrinthPlugins(q)
            .then((hits) => setResults(hits))
            .catch((error) => {
                console.error(error);
                addError({ key: 'plugins', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    };

    const loadCurated = (list: 'citybuild' | 'pvp') => {
        clearFlashes('plugins');
        setView(list);
        setLoading(true);
        setHasSearched(true);

        getCuratedPlugins(CURATED_PLUGIN_LISTS[list])
            .then((hits) => setResults(hits))
            .catch((error) => {
                console.error(error);
                addError({ key: 'plugins', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    };

    useEffect(() => {
        doSearch('');
    }, []);

    const relatedInstalledFiles = (hit: ModrinthSearchHit): FileObject[] => {
        const slugKey = coreName(hit.slug);
        const titleKey = coreName(hit.title);

        return installedFiles.filter((f) => {
            const key = coreName(f.name);
            return isRelatedKey(key, slugKey) || isRelatedKey(key, titleKey);
        });
    };

    const isHitInstalled = (hit: ModrinthSearchHit): boolean => relatedInstalledFiles(hit).length > 0;

    const install = (hit: ModrinthSearchHit) => {
        clearFlashes('plugins');
        setInstallState((state) => ({ ...state, [hit.project_id]: 'installing' }));

        getInstallableModrinthFile(hit.project_id, minecraftVersion || undefined)
            .then((result) => {
                if (!result) {
                    throw new Error(t('install_incompatible', { title: hit.title }));
                }

                return pullFile(uuid, result.file.url, PLUGINS_DIRECTORY, result.file.filename);
            })
            .then(() => {
                setInstallState((state) => ({ ...state, [hit.project_id]: 'installed' }));
                addFlash({
                    key: 'plugins',
                    type: 'success',
                    message: t('install_success', { title: hit.title, directory: PLUGINS_DIRECTORY }),
                });
                refreshInstalled();
            })
            .catch((error) => {
                console.error(error);
                setInstallState((state) => ({ ...state, [hit.project_id]: 'error' }));
                addError({
                    key: 'plugins',
                    message: error instanceof Error ? error.message : httpErrorToHuman(error),
                });
            });
    };

    const runDelete = (key: string, names: string[]) => {
        clearFlashes('plugins');
        setDeleting((state) => ({ ...state, [key]: true }));

        deleteFiles(uuid, PLUGINS_DIRECTORY, names)
            .then(() => {
                addFlash({ key: 'plugins', type: 'success', message: t('delete_success', { names: names.join(', ') }) });
                refreshInstalled();
            })
            .catch((error) => {
                console.error(error);
                addError({ key: 'plugins', message: httpErrorToHuman(error) });
            })
            .then(() => setDeleting((state) => ({ ...state, [key]: false })));
    };

    const requestDeleteForHit = (hit: ModrinthSearchHit) => {
        const files = relatedInstalledFiles(hit);
        if (!files.length) {
            addError({ key: 'plugins', message: t('delete_not_found', { title: hit.title }) });
            return;
        }

        setPendingDelete({ label: hit.title, names: files.map((f) => f.name) });
    };

    const requestDeleteForJar = (jar: FileObject, group: FileObject[]) => {
        setPendingDelete({ label: jar.name, names: group.map((f) => f.name) });
    };

    const visibleResults = useMemo(() => {
        if (statusFilter === 'all') return results;
        return results.filter((hit) => (statusFilter === 'installed' ? isHitInstalled(hit) : !isHitInstalled(hit)));
    }, [results, statusFilter, installedFiles]);

    // Folders (a plugin's data directory, shared libraries like bStats/spark,
    // etc.) are never shown on their own — only the jars that were actually
    // installed. Deleting a jar still takes its folder(s) with it, via the
    // grouping below.
    const installedJars = useMemo(() => installedFiles.filter((f) => f.isFile), [installedFiles]);
    const installedGroups = useMemo(() => groupInstalledFiles(installedFiles), [installedFiles]);
    const groupForFile = (file: FileObject): FileObject[] =>
        installedGroups.find((group) => group.some((f) => f.key === file.key)) || [file];

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'plugins'} css={tw`mb-4`} />
            <GeyserBox onChange={refreshInstalled} />
            <Dialog.Confirm
                open={pendingDelete !== null}
                onClose={() => setPendingDelete(null)}
                title={t('delete_title', { label: pendingDelete?.label ?? '' })}
                confirm={t('delete_confirm')}
                onConfirmed={() => {
                    if (pendingDelete) {
                        runDelete(pendingDelete.names.join(':'), pendingDelete.names);
                    }
                    setPendingDelete(null);
                }}
            >
                {t('delete_body', { directory: PLUGINS_DIRECTORY })}
                <ul css={tw`list-disc list-inside mt-2`}>
                    {pendingDelete?.names.map((name) => (
                        <li key={name}>{name}</li>
                    ))}
                </ul>
            </Dialog.Confirm>

            <form
                css={tw`flex mb-4`}
                onSubmit={(e: React.FormEvent<HTMLFormElement>) => {
                    e.preventDefault();
                    doSearch(query);
                }}
            >
                <Input
                    value={query}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setQuery(e.currentTarget.value)}
                    placeholder={t('search_placeholder')}
                />
                <Button type={'submit'} css={tw`ml-2 flex-shrink-0`} isLoading={loading && view === 'search'}>
                    <FontAwesomeIcon icon={faSearch} css={tw`mr-2`} />
                    {t('search_button')}
                </Button>
            </form>

            <div
                css={tw`relative inline-block mb-4`}
                onMouseEnter={openFilterMenu}
                onMouseLeave={scheduleFilterMenuClose}
            >
                <div
                    css={tw`inline-flex items-center px-3 py-2 text-sm rounded border border-neutral-600 text-neutral-300 cursor-pointer select-none transition-colors duration-150 hover:text-neutral-100 hover:border-neutral-500`}
                >
                    {t('filter.button')}
                    <FontAwesomeIcon icon={faChevronDown} css={tw`ml-2 text-2xs`} />
                </div>
                {filterMenuOpen && (
                    <div
                        css={tw`absolute z-20 mt-1 w-56 rounded border border-neutral-600 bg-neutral-700 shadow-lg py-2`}
                    >
                        <p css={tw`px-3 pt-1 pb-1 text-2xs text-neutral-500 uppercase select-none`}>
                            {t('filter.category_label')}
                        </p>
                        <FilterMenuItem active={view === 'search'} onClick={() => doSearch(query)}>
                            {t('filter.search')}
                        </FilterMenuItem>
                        <FilterMenuItem active={view === 'citybuild'} onClick={() => loadCurated('citybuild')}>
                            {t('filter.citybuild')}
                        </FilterMenuItem>
                        <FilterMenuItem active={view === 'pvp'} onClick={() => loadCurated('pvp')}>
                            {t('filter.pvp')}
                        </FilterMenuItem>
                        <FilterMenuItem active={view === 'installed'} onClick={() => setView('installed')}>
                            {t('filter.installed_count', { count: installedJars.length })}
                        </FilterMenuItem>
                        <div css={tw`border-t border-neutral-600 my-2`} />
                        <p css={tw`px-3 pt-1 pb-1 text-2xs text-neutral-500 uppercase select-none`}>
                            {t('filter.status_label')}
                        </p>
                        <FilterMenuItem active={statusFilter === 'all'} onClick={() => setStatusFilter('all')}>
                            {t('filter.all')}
                        </FilterMenuItem>
                        <FilterMenuItem
                            active={statusFilter === 'installed'}
                            onClick={() => setStatusFilter('installed')}
                        >
                            {t('filter.installed')}
                        </FilterMenuItem>
                        <FilterMenuItem
                            active={statusFilter === 'not_installed'}
                            onClick={() => setStatusFilter('not_installed')}
                        >
                            {t('filter.not_installed')}
                        </FilterMenuItem>
                    </div>
                )}
            </div>

            {minecraftVersion && view !== 'installed' && (
                <p css={tw`text-xs text-neutral-400 mb-4`}>
                    {t('compatible_with', { version: minecraftVersion })}
                </p>
            )}

            {view === 'installed' ? (
                installedLoading ? (
                    <Spinner size={'large'} centered />
                ) : installedJars.length === 0 ? (
                    <p css={tw`text-center text-sm text-neutral-300`}>
                        {t('installed_empty', { directory: PLUGINS_DIRECTORY })}
                    </p>
                ) : (
                    installedJars.map((jar) => {
                        const group = groupForFile(jar);
                        const deleteKey = group.map((f) => f.name).join(':');

                        return (
                            <GreyRowBox key={jar.key} $hoverable={false} css={tw`mb-2`}>
                                <FontAwesomeIcon icon={faDownload} fixedWidth />
                                <div css={tw`flex-1 ml-4 overflow-hidden`}>
                                    <p css={tw`text-sm truncate`}>{jar.name}</p>
                                    <p css={tw`text-xs text-neutral-400`}>{bytesToString(jar.size)}</p>
                                </div>
                                <Can action={'file.delete'}>
                                    <Button
                                        size={'small'}
                                        color={'red'}
                                        isSecondary
                                        isLoading={!!deleting[deleteKey]}
                                        onClick={() => requestDeleteForJar(jar, group)}
                                    >
                                        <FontAwesomeIcon icon={faTrashAlt} fixedWidth />
                                    </Button>
                                </Can>
                            </GreyRowBox>
                        );
                    })
                )
            ) : loading && !results.length ? (
                <Spinner size={'large'} centered />
            ) : (
                <>
                    {hasSearched && !loading && visibleResults.length === 0 && (
                        <p css={tw`text-center text-sm text-neutral-300`}>{t('no_results')}</p>
                    )}
                    {visibleResults.map((hit) => {
                        const state = installState[hit.project_id] || (isHitInstalled(hit) ? 'installed' : 'idle');
                        const deleteKey = relatedInstalledFiles(hit)
                            .map((f) => f.name)
                            .join(':');

                        return (
                            <GreyRowBox key={hit.project_id} $hoverable={false} css={tw`mb-2 items-center`}>
                                <a
                                    href={`https://modrinth.com/plugin/${hit.slug}`}
                                    target={'_blank'}
                                    rel={'noreferrer'}
                                    title={t('view_on_modrinth', { title: hit.title })}
                                >
                                    {hit.icon_url ? (
                                        <img src={hit.icon_url} css={tw`w-10 h-10 rounded`} alt={hit.title} />
                                    ) : (
                                        <div css={tw`w-10 h-10 rounded bg-neutral-600 flex-shrink-0`} />
                                    )}
                                </a>
                                <div css={tw`flex-1 ml-4 overflow-hidden`}>
                                    <p css={tw`text-sm font-medium truncate`}>{hit.title}</p>
                                    <p css={tw`text-xs text-neutral-400 truncate`}>{hit.description}</p>
                                </div>
                                <div css={tw`ml-4 text-right hidden sm:block`}>
                                    <p css={tw`text-sm`}>{hit.downloads.toLocaleString()}</p>
                                    <p css={tw`mt-1 text-2xs text-neutral-500 uppercase select-none`}>
                                        {t('downloads_label')}
                                    </p>
                                </div>
                                <Can action={'file.create'}>
                                    <div css={tw`ml-4 flex`}>
                                        {state === 'installed' && (
                                            <Button
                                                size={'small'}
                                                color={'red'}
                                                isSecondary
                                                css={tw`mr-2`}
                                                isLoading={!!deleting[deleteKey]}
                                                onClick={() => requestDeleteForHit(hit)}
                                            >
                                                <FontAwesomeIcon icon={faTrashAlt} fixedWidth />
                                            </Button>
                                        )}
                                        <Button
                                            size={'small'}
                                            color={state === 'installed' ? 'green' : 'primary'}
                                            isSecondary={state === 'installed'}
                                            isLoading={state === 'installing'}
                                            disabled={state === 'installing' || state === 'installed'}
                                            onClick={() => install(hit)}
                                        >
                                            <FontAwesomeIcon
                                                icon={state === 'installed' ? faCheck : faDownload}
                                                css={tw`mr-2`}
                                            />
                                            {state === 'installed' ? t('installed_button') : t('install_button')}
                                        </Button>
                                    </div>
                                </Can>
                            </GreyRowBox>
                        );
                    })}
                </>
            )}
        </ServerContentBlock>
    );
};
