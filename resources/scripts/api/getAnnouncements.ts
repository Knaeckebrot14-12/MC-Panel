import http from '@/api/http';

export interface Announcement {
    id: number;
    title: string;
    content: string;
    createdAt: Date;
}

export default (): Promise<Announcement[]> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/announcements')
            .then(({ data }) =>
                resolve(
                    (data.data || []).map(
                        (announcement: any): Announcement => ({
                            id: announcement.id,
                            title: announcement.title,
                            content: announcement.content,
                            createdAt: new Date(announcement.created_at),
                        })
                    )
                )
            )
            .catch(reject);
    });
};
