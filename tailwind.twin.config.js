// twin.macro (the tw`...` macro) still runs its own Tailwind 2, which has no line-clamp utilities
// built in yet and doesn't understand Tailwind 3's "<alpha-value>". Tailwind 3 has line-clamp, so
// only twin gets the plugin; that keeps Tailwind 3 from warning about it on every build.
const config = require('./tailwind.config.js');

const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
const gray = Object.fromEntries(shades.map((shade) => [shade, `hsl(var(--rp-gray-${shade}))`]));
const accent = Object.fromEntries(shades.map((shade) => [shade, `rgb(var(--rp-primary-${shade}))`]));

module.exports = {
    ...config,
    theme: {
        ...config.theme,
        extend: {
            ...config.theme.extend,
            colors: {
                ...config.theme.extend.colors,
                black: 'hsl(var(--rp-black))',
                primary: accent,
                blue: accent,
                gray,
                neutral: gray,
            },
        },
    },
    plugins: [require('@tailwindcss/line-clamp'), ...config.plugins],
};
