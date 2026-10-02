import React, { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import Button from '@/components/elements/Button';
import Input from '@/components/elements/Input';
import Can from '@/components/elements/Can';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import deleteFiles from '@/api/server/files/deleteFiles';
import loadDirectory from '@/api/server/files/loadDirectory';

const ICON = 'server-icon.png';

// Minecraft's colour codes (§0-§f) and formats (§k-§o, §r reset).
const COLORS: Record<string, string> = {
    '0': '#000000',
    '1': '#0000AA',
    '2': '#00AA00',
    '3': '#00AAAA',
    '4': '#AA0000',
    '5': '#AA00AA',
    '6': '#FFAA00',
    '7': '#AAAAAA',
    '8': '#555555',
    '9': '#5555FF',
    a: '#55FF55',
    b: '#55FFFF',
    c: '#FF5555',
    d: '#FF55FF',
    e: '#FFFF55',
    f: '#FFFFFF',
};
const FORMATS = ['l', 'o', 'n', 'm', 'k', 'r'];

/** server.properties is a Java properties file: § is stored as §, a line break as \n. */
export const decodeProperty = (raw: string): string =>
    raw.replace(/\\(u[0-9a-fA-F]{4}|.)/g, (_, escape: string) => {
        if (escape.length === 5) return String.fromCharCode(parseInt(escape.substring(1), 16));
        return ({ n: '\n', t: '\t', r: '\r', f: '\f' } as Record<string, string>)[escape] ?? escape;
    });

// Goes by UTF-16 code units (like Java): an emoji becomes its surrogate pair 😀.
export const encodeProperty = (text: string): string =>
    text
        .split('')
        .map((char, index) => {
            const code = char.charCodeAt(0);
            if (char === '\\') return '\\\\';
            if (char === '\n') return '\\n';
            if (char === ' ' && index === 0) return '\\ ';
            if (code < 0x20 || code > 0x7e) return '\\u' + code.toString(16).toUpperCase().padStart(4, '0');
            return char;
        })
        .join('');

// The editor shows & instead of § (easier to type); only real codes are converted.
const toEditor = (text: string) => text.replace(/§([0-9a-fk-or])/gi, '&$1');
const fromEditor = (text: string) => text.replace(/&([0-9a-fk-or])/gi, '§$1');

/** Renders a MOTD line the way the Minecraft server list does. */
const MotdLine = ({ text }: { text: string }) => {
    const parts: React.ReactNode[] = [];
    let style: React.CSSProperties = { color: COLORS['7'] };
    let buffer = '';
    const flush = (key: number) => {
        if (buffer) parts.push(<span key={key} style={style}>{buffer}</span>);
        buffer = '';
    };
    for (let i = 0; i < text.length; i++) {
        const code = text[i] === '§' ? text[i + 1]?.toLowerCase() : undefined;
        if (code && (code in COLORS || FORMATS.includes(code))) {
            flush(i);
            if (code in COLORS) style = { color: COLORS[code] };
            else if (code === 'r') style = { color: COLORS['7'] };
            else if (code === 'l') style = { ...style, fontWeight: 700 };
            else if (code === 'o') style = { ...style, fontStyle: 'italic' };
            else if (code === 'n')
                style = { ...style, textDecoration: `${style.textDecoration || ''} underline`.trim() };
            else if (code === 'm')
                style = { ...style, textDecoration: `${style.textDecoration || ''} line-through`.trim() };
            else if (code === 'k') style = { ...style, filter: 'blur(2px)' };
            i++;
            continue;
        }
        buffer += text[i];
    }
    flush(text.length);
    return <div css={tw`truncate`}>{parts.length ? parts : ' '}</div>;
};

/** Square crop in the middle, scaled to 64×64, as PNG (what Minecraft expects). */
const toIcon = (file: File): Promise<Blob> =>
    new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => {
            const size = Math.min(image.width, image.height);
            URL.revokeObjectURL(image.src);
            // e.g. an SVG without a size: nothing to draw, it would become an empty icon.
            if (!size) return reject(new Error('image'));
            const canvas = document.createElement('canvas');
            canvas.width = 64;
            canvas.height = 64;
            const context = canvas.getContext('2d')!;
            context.imageSmoothingQuality = 'high';
            context.drawImage(image, (image.width - size) / 2, (image.height - size) / 2, size, size, 0, 0, 64, 64);
            canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('canvas'))), 'image/png');
        };
        image.onerror = () => {
            URL.revokeObjectURL(image.src);
            reject(new Error('image'));
        };
        image.src = URL.createObjectURL(file);
    });

interface Props {
    motd: string;
    maxPlayers: string;
    changed: boolean;
    onChange: (raw: string) => void;
}

export default ({ motd, maxPlayers, changed, onChange }: Props) => {
    const { t } = useTranslation('server_properties');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const name = ServerContext.useStoreState((state) => state.server.data!.name);
    const { addError, clearFlashes, addFlash } = useFlash();

    const [icon, setIcon] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const [focused, setFocused] = useState(0);
    const inputs = [useRef<HTMLInputElement>(null), useRef<HTMLInputElement>(null)];
    const fileInput = useRef<HTMLInputElement>(null);

    const lines = toEditor(decodeProperty(motd)).split('\n');
    const line = (index: number) => lines[index] ?? '';
    const setLine = (index: number, value: string) => {
        const next = [line(0), line(1)];
        next[index] = value.replace(/\n/g, '');
        onChange(encodeProperty(fromEditor(next[1] ? next.join('\n') : next[0])));
    };

    const loadIcon = () =>
        loadDirectory(uuid, '/')
            .then((files) => (files.some((f) => f.name === ICON && f.isFile) ? getFileDownloadUrl(uuid, ICON) : null))
            .then((url) => setIcon(url ? `${url}&t=${Date.now()}` : null))
            .catch(() => setIcon(null));

    useEffect(() => {
        loadIcon();
    }, []);

    // Puts a code at the cursor of the line that was last focused.
    const insert = (code: string) => {
        const input = inputs[focused].current;
        const value = line(focused);
        const start = input?.selectionStart ?? value.length;
        const end = input?.selectionEnd ?? value.length;
        setLine(focused, value.substring(0, start) + '&' + code + value.substring(end));
        requestAnimationFrame(() => {
            input?.focus();
            input?.setSelectionRange(start + 2, start + 2);
        });
    };

    const upload = (file?: File) => {
        if (!file) return;
        clearFlashes('properties');
        setBusy(true);
        toIcon(file)
            .then((blob) =>
                getFileUploadUrl(uuid).then((url) =>
                    axios.post(
                        url,
                        { files: new File([blob], ICON, { type: 'image/png' }) },
                        { headers: { 'Content-Type': 'multipart/form-data' }, params: { directory: '/' } }
                    )
                )
            )
            .then(() => {
                addFlash({ key: 'properties', type: 'success', message: t('server_list.icon_saved') });
                return loadIcon();
            })
            .catch((error) =>
                addError({
                    key: 'properties',
                    message: error?.message === 'image' ? t('server_list.icon_invalid') : httpErrorToHuman(error),
                })
            )
            .then(() => setBusy(false));
    };

    const removeIcon = () => {
        clearFlashes('properties');
        setBusy(true);
        deleteFiles(uuid, '/', [ICON])
            .then(() => setIcon(null))
            .catch((error) => addError({ key: 'properties', message: httpErrorToHuman(error) }))
            .then(() => setBusy(false));
    };

    const motdText = fromEditor(lines.slice(0, 2).join('\n'));

    return (
        <TitledGreyBox title={t('server_list.title')} css={tw`mb-4`}>
            {/* Looks like an entry in Minecraft's multiplayer list. */}
            <div
                css={tw`flex gap-3 p-2 rounded mb-4 font-mono text-sm`}
                style={{ background: '#1d1d1d', border: '1px solid #3a3a3a' }}
            >
                <div
                    css={tw`flex-shrink-0 flex items-center justify-center rounded-sm overflow-hidden`}
                    style={{ width: 64, height: 64, background: '#2b2b2b' }}
                >
                    {icon ? (
                        <img
                            src={icon}
                            alt={''}
                            width={64}
                            height={64}
                            style={{ imageRendering: 'pixelated' }}
                            onError={() => setIcon(null)}
                        />
                    ) : (
                        <span css={tw`text-neutral-500 text-xs text-center px-1`}>{t('server_list.no_icon')}</span>
                    )}
                </div>
                <div css={tw`min-w-0 flex-1`}>
                    <div css={tw`flex justify-between gap-2`}>
                        <span css={tw`text-white truncate`}>{name}</span>
                        <span style={{ color: COLORS['7'] }}>0/{maxPlayers || '20'}</span>
                    </div>
                    {motdText.split('\n').map((text, index) => (
                        <MotdLine key={index} text={text} />
                    ))}
                </div>
            </div>

            <div css={tw`grid grid-cols-1 lg:grid-cols-3 gap-4`}>
                <div css={tw`lg:col-span-2 space-y-2`}>
                    <label css={tw`text-sm text-neutral-100`}>
                        {t('fields.motd.label')}
                        {changed && <span css={tw`ml-2 text-xs text-yellow-400`}>●</span>}
                    </label>
                    {[0, 1].map((index) => (
                        <Input
                            key={index}
                            ref={inputs[index]}
                            value={line(index)}
                            placeholder={t(index === 0 ? 'server_list.line1' : 'server_list.line2')}
                            onFocus={() => setFocused(index)}
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setLine(index, e.currentTarget.value)}
                        />
                    ))}
                    <div css={tw`flex flex-wrap gap-1`}>
                        {Object.entries(COLORS).map(([code, color]) => (
                            <button
                                key={code}
                                type={'button'}
                                title={`&${code}`}
                                onMouseDown={(e) => e.preventDefault()}
                                onClick={() => insert(code)}
                                css={tw`w-6 h-6 rounded border border-neutral-500`}
                                style={{ background: color }}
                            />
                        ))}
                        {FORMATS.map((code) => (
                            <button
                                key={code}
                                type={'button'}
                                title={`&${code}`}
                                onMouseDown={(e) => e.preventDefault()}
                                onClick={() => insert(code)}
                                css={tw`h-6 px-2 rounded border border-neutral-500 text-xs text-neutral-100 bg-neutral-700`}
                            >
                                {t(`server_list.format.${code}`)}
                            </button>
                        ))}
                    </div>
                    <p css={tw`text-xs text-neutral-400`}>{t('server_list.codes_hint')}</p>
                </div>
                <div css={tw`space-y-2`}>
                    <label css={tw`text-sm text-neutral-100`}>{t('server_list.icon_title')}</label>
                    <input
                        ref={fileInput}
                        type={'file'}
                        accept={'image/png,image/jpeg,image/gif,image/webp'}
                        css={tw`hidden`}
                        onChange={(e) => {
                            upload(e.currentTarget.files?.[0]);
                            e.currentTarget.value = '';
                        }}
                    />
                    <div css={tw`flex flex-wrap gap-2`}>
                        <Can action={'file.create'}>
                            <Button
                                type={'button'}
                                size={'xsmall'}
                                isLoading={busy}
                                disabled={busy}
                                onClick={() => fileInput.current?.click()}
                            >
                                {t('server_list.icon_upload')}
                            </Button>
                        </Can>
                        {icon && (
                            <Can action={'file.delete'}>
                                <Button
                                    type={'button'}
                                    size={'xsmall'}
                                    color={'red'}
                                    isSecondary
                                    disabled={busy}
                                    onClick={removeIcon}
                                >
                                    {t('server_list.icon_remove')}
                                </Button>
                            </Can>
                        )}
                    </div>
                    <p css={tw`text-xs text-neutral-400`}>{t('server_list.icon_hint')}</p>
                </div>
            </div>
        </TitledGreyBox>
    );
};
