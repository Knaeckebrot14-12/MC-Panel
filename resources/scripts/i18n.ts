import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import I18NextHttpBackend, { HttpBackendOptions } from 'i18next-http-backend';

// If we're using HMR use a unique hash per page reload so that we're always
// doing cache busting. Otherwise just use the builder provided hash value in
// the URL to allow cache busting to occur whenever the front-end is rebuilt.
const hash = module.hot ? Date.now().toString(16) : process.env.WEBPACK_BUILD_HASH;

// The logged-in user's preferred language, set server-side on their account
// and embedded into the page on load. Guests (the login/register pages) have
// no such user yet, so they fall back to the panel's configured default
// locale — mirroring exactly what LanguageMiddleware does server-side for
// Blade/backend translations, so both sides agree on a guest's language.
const win = window as unknown as {
    PterodactylUser?: { language?: string };
    SiteConfiguration?: { locale?: string };
};
const initialLanguage = win.PterodactylUser?.language || win.SiteConfiguration?.locale;

// Note: deliberately not using i18next-multiload-backend-adapter here. It
// batches every pending (language, namespace) pair — including the
// fallbackLng — into a single request with the languages joined by "+"
// (e.g. "de+en"), which this panel's /locales/locale.json endpoint (and
// Laravel's translator) can't resolve as a locale. That only went unnoticed
// because with the default lng of "en" it collapses to a same-language,
// single-value join. i18next-http-backend handles per-namespace requests
// natively and correctly, so we use it directly instead of the wrapper.
i18n.use(I18NextHttpBackend)
    .use(initReactI18next)
    .init({
        debug: process.env.DEBUG === 'true',
        lng: initialLanguage || 'en',
        fallbackLng: 'en',
        keySeparator: '.',
        // "strings" holds generic, widely-reused terms (Save, Cancel, Error,
        // Success, ...) referenced from many namespaces via a `{ ns: 'strings' }`
        // override on individual t() calls. Namespaces used that way must
        // already be loaded — an ad-hoc override doesn't trigger its own
        // fetch in time for that same call — so it's preloaded here globally
        // instead of every component having to declare it.
        ns: ['translation', 'strings'],
        defaultNS: 'translation',
        backend: {
            loadPath: '/locales/locale.json?locale={{lng}}&namespace={{ns}}',
            queryStringParams: { hash },
            // The endpoint replies with `{ [locale]: { [namespace]: {...} } }`
            // (a shape originally meant for a multi-load backend); unwrap it
            // to the flat translation object i18next-http-backend expects.
            parse: (data: string, languages: string | string[], namespaces: string | string[]) => {
                const lng = Array.isArray(languages) ? languages[0] : languages;
                const ns = Array.isArray(namespaces) ? namespaces[0] : namespaces;

                return JSON.parse(data)?.[lng]?.[ns] || {};
            },
        } as HttpBackendOptions,
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
    });

export default i18n;
