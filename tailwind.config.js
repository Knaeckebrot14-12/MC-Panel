const colors = require('tailwindcss/colors');

// Gray scale and accent come from CSS variables (set in templates/wrapper.blade.php and
// partials/branding.blade.php), so Settings -> Design can change the accent colour and users can
// switch between the dark and the light theme without rebuilding anything.
const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
const gray = Object.fromEntries(shades.map((shade) => [shade, `hsl(var(--rp-gray-${shade}) / <alpha-value>)`]));
const accent = Object.fromEntries(shades.map((shade) => [shade, `rgb(var(--rp-primary-${shade}) / <alpha-value>)`]));

module.exports = {
    content: [
        './resources/scripts/**/*.{js,ts,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                header: ['"IBM Plex Sans"', '"Roboto"', 'system-ui', 'sans-serif'],
            },
            colors: {
                black: 'hsl(var(--rp-black) / <alpha-value>)',
                // "primary" and "neutral" are deprecated, prefer the use of "blue" and "gray"
                // in new code.
                primary: accent,
                blue: accent,
                gray: gray,
                neutral: gray,
                cyan: colors.cyan,
            },
            fontSize: {
                '2xs': '0.625rem',
            },
            transitionDuration: {
                250: '250ms',
            },
            borderColor: theme => ({
                default: theme('colors.neutral.400', 'currentColor'),
            }),
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ]
};
