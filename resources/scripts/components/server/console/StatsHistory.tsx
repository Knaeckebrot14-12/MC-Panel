import React, { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Line } from 'react-chartjs-2';
import {
    CategoryScale,
    Chart as ChartJS,
    ChartOptions,
    Filler,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { theme } from 'twin.macro';
import classNames from 'classnames';
import { ServerContext } from '@/state/server';
import { hexToRgba, resolveColor } from '@/lib/helpers';
import { bytesToString } from '@/lib/formatters';
import ChartBlock from '@/components/server/console/ChartBlock';
import getServerStats, { ServerStats } from '@/api/server/stats';

ChartJS.register(LineElement, PointElement, Filler, LinearScale, CategoryScale, Tooltip);

type Range = '24h' | '7d';
type Metric = 'cpu' | 'memory' | 'players';

const COLORS: Record<Metric, string> = {
    cpu: theme('colors.cyan.400'),
    memory: theme('colors.purple.400'),
    players: theme('colors.green.400'),
};

export default () => {
    const { t, i18n } = useTranslation('server_console');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [range, setRange] = useState<Range>('24h');
    const [stats, setStats] = useState<ServerStats | null>(null);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        let active = true;
        const load = () =>
            getServerStats(uuid, range)
                .then((data) => active && (setStats(data), setFailed(false)))
                .catch(() => active && setFailed(true));
        load();
        // New points arrive every five minutes.
        const timer = setInterval(load, 5 * 60 * 1000);
        return () => {
            active = false;
            clearInterval(timer);
        };
    }, [uuid, range]);

    const points = stats?.points || [];
    const hasPlayers = points.some((point) => point.players !== null);

    const labels = useMemo(
        () =>
            points.map((point) => {
                const date = new Date(point.t);
                return range === '24h'
                    ? date.toLocaleTimeString(i18n.language, { hour: '2-digit', minute: '2-digit' })
                    : date.toLocaleString(i18n.language, { weekday: 'short', hour: '2-digit' });
            }),
        [points, range, i18n.language]
    );

    const chart = (metric: Metric, format: (value: number) => string) => {
        const color = COLORS[metric];
        const options: ChartOptions<'line'> = {
            responsive: true,
            animation: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    displayColors: false,
                    callbacks: { label: (item) => format(Number(item.raw)) },
                },
            },
            scales: {
                x: {
                    type: 'category',
                    grid: { display: false, drawBorder: false },
                    ticks: { color: resolveColor(theme('colors.gray.400')), maxTicksLimit: 6, maxRotation: 0, font: { size: 10 } },
                },
                y: {
                    min: 0,
                    // Player counts are whole numbers; an empty server still gets a readable 0-1 axis.
                    ...(metric === 'players' ? { suggestedMax: 1 } : {}),
                    grid: { color: resolveColor(theme('colors.gray.700')), drawBorder: false },
                    ticks: {
                        color: resolveColor(theme('colors.gray.200')),
                        ...(metric === 'players' ? { precision: 0, maxTicksLimit: 4 } : { count: 3 }),
                        font: { size: 11 },
                        callback: (value) => format(Number(value)),
                    },
                },
            },
            elements: { point: { radius: 0, hitRadius: 6 }, line: { tension: 0.2, borderWidth: 2 } },
        };

        return (
            <Line
                data={{
                    labels,
                    datasets: [
                        {
                            data: points.map((point) => (metric === 'players' ? point.players ?? 0 : point[metric])),
                            borderColor: color,
                            backgroundColor: hexToRgba(color, 0.15),
                            fill: true,
                        },
                    ],
                }}
                options={options}
            />
        );
    };

    const rangeButton = (value: Range) => (
        <button
            type={'button'}
            onClick={() => setRange(value)}
            className={classNames(
                'px-3 py-1 text-xs rounded transition-colors duration-150',
                range === value ? 'bg-gray-600 text-gray-50' : 'text-gray-300 hover:text-gray-100'
            )}
        >
            {t(`history.range_${value}`)}
        </button>
    );

    return (
        <div className={'mt-4'}>
            <div className={'flex items-center justify-between mb-2'}>
                <h2 className={'font-header font-medium text-lg text-gray-100'}>{t('history.title')}</h2>
                <div className={'flex bg-gray-700 rounded p-1 space-x-1'}>
                    {rangeButton('24h')}
                    {rangeButton('7d')}
                </div>
            </div>
            {failed || (stats && points.length < 2) ? (
                <p className={'text-sm text-gray-400 bg-gray-700 rounded p-4'}>{t('history.empty')}</p>
            ) : (
                <div
                    className={classNames(
                        'grid grid-cols-1 gap-2 sm:gap-4',
                        hasPlayers ? 'md:grid-cols-3' : 'md:grid-cols-2'
                    )}
                >
                    <ChartBlock title={t('history.cpu')}>{chart('cpu', (v) => `${v.toFixed(0)}%`)}</ChartBlock>
                    <ChartBlock title={t('history.memory')}>{chart('memory', (v) => bytesToString(v))}</ChartBlock>
                    {hasPlayers && (
                        <ChartBlock title={t('history.players')}>
                            {chart('players', (v) => `${Math.round(v)}`)}
                        </ChartBlock>
                    )}
                </div>
            )}
        </div>
    );
};
