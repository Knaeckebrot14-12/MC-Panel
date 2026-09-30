import http from '@/api/http';

export interface AutoBackupSettings {
    hours: number;
    intervals: number[];
    lastAt: string | null;
    nextAt: string | null;
    backupLimit: number;
}

const transform = (data: any): AutoBackupSettings => ({
    hours: data.hours,
    intervals: data.intervals,
    lastAt: data.last_at,
    nextAt: data.next_at,
    backupLimit: data.backup_limit,
});

export const getAutoBackup = async (uuid: string): Promise<AutoBackupSettings> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/backups/auto`);

    return transform(data);
};

export const updateAutoBackup = async (uuid: string, hours: number): Promise<AutoBackupSettings> => {
    const { data } = await http.put(`/api/client/servers/${uuid}/backups/auto`, { hours });

    return transform(data);
};
