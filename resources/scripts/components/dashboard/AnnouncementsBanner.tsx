import React from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faBullhorn, faTimes } from '@fortawesome/free-solid-svg-icons';
import tw from 'twin.macro';
import useSWR from 'swr';
import { useStoreState } from 'easy-peasy';
import getAnnouncements, { Announcement } from '@/api/getAnnouncements';
import { usePersistedState } from '@/plugins/usePersistedState';

export default () => {
    const uuid = useStoreState((state) => state.user.data?.uuid);
    const { data: announcements } = useSWR<Announcement[]>('/api/client/announcements', getAnnouncements);
    const [dismissed, setDismissed] = usePersistedState<number[]>(`${uuid}:dismissed_announcements`, []);

    if (!announcements || announcements.length === 0) {
        return null;
    }

    const visible = announcements.filter((announcement) => !(dismissed || []).includes(announcement.id));
    if (visible.length === 0) {
        return null;
    }

    return (
        <div css={tw`mb-4`}>
            {visible.map((announcement, index) => (
                <div
                    key={announcement.id}
                    css={tw`bg-yellow-600 bg-opacity-10 border border-yellow-500 border-opacity-50 rounded p-4 flex`}
                    className={index > 0 ? 'mt-2' : undefined}
                >
                    <FontAwesomeIcon icon={faBullhorn} css={tw`text-yellow-400 mt-1 mr-3 flex-shrink-0`} />
                    <div css={tw`min-w-0 flex-1`}>
                        <p css={tw`text-sm font-medium text-yellow-100`}>{announcement.title}</p>
                        <p css={tw`text-sm text-yellow-200 mt-1 whitespace-pre-line break-words`}>
                            {announcement.content}
                        </p>
                    </div>
                    <button
                        type={'button'}
                        aria-label={'Dismiss announcement'}
                        css={tw`ml-3 text-yellow-400 hover:text-yellow-200 flex-shrink-0`}
                        onClick={() => setDismissed([...(dismissed || []), announcement.id])}
                    >
                        <FontAwesomeIcon icon={faTimes} />
                    </button>
                </div>
            ))}
        </div>
    );
};
