// twin.macro (the tw`...` macro) still runs its own Tailwind 2, which has no line-clamp utilities
// built in yet. Tailwind 3 has them, so only twin gets the plugin; that keeps Tailwind 3 from
// warning about it on every build.
const config = require('./tailwind.config.js');

module.exports = {
    ...config,
    plugins: [require('@tailwindcss/line-clamp'), ...config.plugins],
};
