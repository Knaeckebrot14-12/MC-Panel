import { action, Action } from 'easy-peasy';

export interface SiteSettings {
    name: string;
    locale: string;
    recaptcha: {
        enabled: boolean;
        siteKey: string;
    };
    maintenance?: {
        mode: 'off' | 'banner' | 'lock';
        message: string;
    };
    discord?: {
        enabled: boolean;
    };
    statusPage?: boolean;
    verifyEmail?: boolean;
    branding?: {
        logo: string | null;
        background: string | null;
        defaultTheme: 'dark' | 'light';
    };
    subdomains?: boolean;
    // Address of phpMyAdmin (<panel>/phpmyadmin/), null while the owner has it turned off.
    phpMyAdmin?: string | null;
}

export interface SettingsStore {
    data?: SiteSettings;
    setSettings: Action<SettingsStore, SiteSettings>;
}

const settings: SettingsStore = {
    data: undefined,

    setSettings: action((state, payload) => {
        state.data = payload;
    }),
};

export default settings;
