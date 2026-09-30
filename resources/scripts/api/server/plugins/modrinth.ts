export interface ModrinthSearchHit {
    project_id: string;
    slug: string;
    title: string;
    description: string;
    icon_url: string | null;
    downloads: number;
    author: string;
    categories: string[];
    latest_version: string;
}

interface ModrinthSearchResponse {
    hits: ModrinthSearchHit[];
    total_hits: number;
}

// A subset of the fields Modrinth's single-project endpoint returns, mapped
// onto the same shape as a search hit so curated (non-searched) plugin lists
// can be rendered with the exact same components.
interface ModrinthProject {
    id: string;
    slug: string;
    title: string;
    description: string;
    icon_url: string | null;
    downloads: number;
    team: string;
    categories: string[];
    versions: string[];
}

export interface ModrinthVersionFile {
    url: string;
    filename: string;
    primary: boolean;
    hashes: { sha1?: string; sha512?: string };
}

export interface ModrinthVersion {
    id: string;
    version_number: string;
    game_versions: string[];
    loaders: string[];
    files: ModrinthVersionFile[];
}

// The server-side loaders that indicate a project is installable as a plugin
// on a Bukkit-family Minecraft server (as opposed to a Forge/Fabric mod).
const PLUGIN_LOADERS = ['paper', 'spigot', 'bukkit', 'purpur', 'folia'];

export const searchModrinthPlugins = async (query: string): Promise<ModrinthSearchHit[]> => {
    const facets = JSON.stringify([['project_type:plugin'], PLUGIN_LOADERS.map((loader) => `categories:${loader}`)]);

    const params = new URLSearchParams({ query, limit: '24', facets });

    const response = await fetch(`https://api.modrinth.com/v2/search?${params.toString()}`);
    if (!response.ok) {
        throw new Error('Failed to search Modrinth for plugins.');
    }

    const data: ModrinthSearchResponse = await response.json();
    return data.hits;
};

// Curated lists of well-known, actively maintained plugins for common server
// archetypes. Modrinth doesn't have "citybuild" or "pvp" categories of its
// own, so these are hand-picked project slugs, verified to exist, rather
// than a facet search.
export const CURATED_PLUGIN_LISTS: Record<'citybuild' | 'pvp', string[]> = {
    citybuild: [
        'worldedit',
        'fastasyncworldedit',
        'worldguard',
        'griefprevention',
        'towny',
        'multiverse-core',
        'luckperms',
        'essentialsx',
        'coreprotect',
        'chestshop',
        'deluxemenus',
    ],
    pvp: [
        'worldguard',
        'combat-tag',
        'duels-optimised',
        'bedwars1058',
        'themis-anti-cheat',
        'packetevents',
        'tab-was-taken',
        'luckperms',
        'essentialsx',
        'coreprotect',
    ],
};

export const getCuratedPlugins = async (slugs: string[]): Promise<ModrinthSearchHit[]> => {
    const projects = await Promise.all(
        slugs.map(async (slug): Promise<ModrinthProject | null> => {
            const response = await fetch(`https://api.modrinth.com/v2/project/${slug}`);
            return response.ok ? response.json() : null;
        })
    );

    return projects
        .filter((project): project is ModrinthProject => project !== null)
        .map((project) => ({
            project_id: project.id,
            slug: project.slug,
            title: project.title,
            description: project.description,
            icon_url: project.icon_url,
            downloads: project.downloads,
            author: project.team,
            categories: project.categories,
            latest_version: project.versions[project.versions.length - 1] || '',
        }));
};

const fetchVersions = async (projectId: string, gameVersion?: string): Promise<ModrinthVersion[]> => {
    const params = new URLSearchParams({ loaders: JSON.stringify(PLUGIN_LOADERS) });
    if (gameVersion) {
        params.set('game_versions', JSON.stringify([gameVersion]));
    }

    const response = await fetch(`https://api.modrinth.com/v2/project/${projectId}/version?${params.toString()}`);
    if (!response.ok) {
        return [];
    }

    return response.json();
};

// Finds the newest version of a plugin that is compatible with a Bukkit-family
// server, preferring one that matches the server's exact Minecraft version and
// falling back to the newest compatible version of the plugin otherwise.
export const getInstallableModrinthFile = async (
    projectId: string,
    minecraftVersion?: string
): Promise<{ version: ModrinthVersion; file: ModrinthVersionFile } | null> => {
    const exact = minecraftVersion && minecraftVersion.toLowerCase() !== 'latest' ? minecraftVersion : undefined;

    let versions = await fetchVersions(projectId, exact);
    if (versions.length === 0 && exact) {
        versions = await fetchVersions(projectId);
    }

    if (versions.length === 0) {
        return null;
    }

    const version = versions[0];
    const file = version.files.find((f) => f.primary) || version.files[0];

    return file ? { version, file } : null;
};
