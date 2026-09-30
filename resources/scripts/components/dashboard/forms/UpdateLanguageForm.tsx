import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import Select from '@/components/elements/Select';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';
import http from '@/api/http';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';

interface LanguageOption {
    code: string;
    label: string;
}

export default () => {
    const { i18n } = useTranslation();
    const language = useStoreState((state: ApplicationStore) => state.user.data!.language);
    const updateUserData = useStoreActions((actions: Actions<ApplicationStore>) => actions.user.updateUserData);
    const { clearFlashes, addFlash } = useFlash();
    const [saving, setSaving] = useState(false);
    const [languages, setLanguages] = useState<LanguageOption[]>([]);

    // The list comes from the server so every language folder that exists is offered.
    useEffect(() => {
        http.get('/api/client/account/languages')
            .then(({ data }) => setLanguages(data))
            .catch(() => setLanguages([]));
    }, []);

    const onChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const next = e.target.value;
        setSaving(true);
        clearFlashes('account:language');

        updateAccountLanguage(next)
            .then(() => {
                updateUserData({ language: next });
                i18n.changeLanguage(next);
            })
            .catch((error) => {
                console.error(error);
                addFlash({ key: 'account:language', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setSaving(false));
    };

    return (
        <Select value={language} onChange={onChange} disabled={saving}>
            {/* Keep the current language selectable while the list is still loading. */}
            {(languages.length ? languages : [{ code: language, label: language }]).map(({ code, label }) => (
                <option key={code} value={code}>
                    {label}
                </option>
            ))}
        </Select>
    );
};
