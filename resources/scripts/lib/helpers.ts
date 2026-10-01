/**
 * Given a valid six character HEX color code, converts it into its associated
 * RGBA value with a user controllable alpha channel.
 */
function hexToRgba(hex: string, alpha = 1): string {
    // noinspection RegExpSimplifiable
    if (!/#?([a-fA-F0-9]{2}){3}/.test(hex)) {
        return hex;
    }

    // noinspection RegExpSimplifiable
    const [r, g, b] = hex.match(/[a-fA-F0-9]{2}/g)!.map((v) => parseInt(v, 16));

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

/**
 * Replaces CSS variables in a colour (the gray scale and accent are variables, see tailwind.config.js)
 * with their current values, for places like canvas charts that can't resolve variables themselves.
 */
function resolveColor(color: string): string {
    if (typeof window === 'undefined' || !color.includes('var(')) {
        return color;
    }
    const style = window.getComputedStyle(document.documentElement);

    return color.replace(/var\((--[\w-]+)\)/g, (_, name: string) => style.getPropertyValue(name).trim());
}

export { hexToRgba, resolveColor };
