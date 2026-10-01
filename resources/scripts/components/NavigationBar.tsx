import * as React from 'react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, NavLink } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCogs, faCoins, faLayerGroup, faLifeRing, faMoon, faSignOutAlt, faSun } from '@fortawesome/free-solid-svg-icons';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import tw, { theme } from 'twin.macro';
import styled from 'styled-components/macro';
import http from '@/api/http';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import Avatar from '@/components/Avatar';
import getCoinOptions from '@/api/coins/getCoinOptions';
import { getTheme, setTheme, Theme } from '@/lib/pwa';

const RightNavigation = styled.div`
    & > a,
    & > button,
    & > .navigation-link {
        ${tw`flex items-center h-full no-underline text-neutral-300 px-6 cursor-pointer transition-all duration-150`};

        &:active,
        &:hover {
            ${tw`text-neutral-100 bg-black`};
        }

        &:active,
        &:hover,
        &.active {
            box-shadow: inset 0 -2px ${theme`colors.cyan.600`.toString()};
        }
    }
`;

export default () => {
    const { t } = useTranslation('navigation');
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.branding?.logo);
    const [theme, setThemeState] = useState<Theme>(getTheme());
    const staff = useStoreState((state: ApplicationStore) => state.user.data!.staff);
    const balance = useStoreState((state: ApplicationStore) => state.coins.balance);
    const setBalance = useStoreActions((actions: Actions<ApplicationStore>) => actions.coins.setBalance);
    const [isLoggingOut, setIsLoggingOut] = useState(false);

    useEffect(() => {
        const sync = () => setThemeState(getTheme());
        window.addEventListener('rp-theme', sync);
        return () => window.removeEventListener('rp-theme', sync);
    }, []);

    useEffect(() => {
        getCoinOptions()
            .then((data) => setBalance(data.balance))
            .catch((error) => console.error(error));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    return (
        <div className={'w-full bg-neutral-900 shadow-md overflow-x-auto'}>
            <SpinnerOverlay visible={isLoggingOut} />
            <div className={'mx-auto w-full flex items-center h-[3.5rem] max-w-[1200px]'}>
                <div id={'logo'} className={'flex-1'}>
                    <Link
                        to={'/'}
                        className={
                            'text-2xl font-header font-medium px-4 no-underline text-neutral-200 hover:text-neutral-100 transition-colors duration-150'
                        }
                    >
                        {logo ? <img src={logo} alt={name} className={'h-9 max-w-[220px] object-contain inline-block'} /> : name}
                    </Link>
                </div>
                <RightNavigation className={'flex h-full items-center justify-center'}>
                    <SearchContainer />
                    <Tooltip placement={'bottom'} content={t('coins_tooltip')}>
                        <NavLink to={'/coins/earn'} className={'navigation-link'}>
                            <FontAwesomeIcon icon={faCoins} css={tw`mr-2 text-yellow-400`} />
                            {balance ?? '…'}
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t('dashboard')}>
                        <NavLink to={'/'} exact>
                            <FontAwesomeIcon icon={faLayerGroup} />
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t('tickets_tooltip')}>
                        <NavLink to={'/tickets'}>
                            <FontAwesomeIcon icon={faLifeRing} />
                        </NavLink>
                    </Tooltip>
                    {staff && (
                        <Tooltip placement={'bottom'} content={t('admin')}>
                            <a href={'/admin'} rel={'noreferrer'}>
                                <FontAwesomeIcon icon={faCogs} />
                            </a>
                        </Tooltip>
                    )}
                    <Tooltip placement={'bottom'} content={t('theme_tooltip')}>
                        <button onClick={() => setTheme(theme === 'dark' ? 'light' : 'dark')}>
                            <FontAwesomeIcon icon={theme === 'dark' ? faSun : faMoon} />
                        </button>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t('account_settings')}>
                        <NavLink to={'/account'}>
                            <span className={'flex items-center w-5 h-5'}>
                                <Avatar.User />
                            </span>
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={t('sign_out')}>
                        <button onClick={onTriggerLogout}>
                            <FontAwesomeIcon icon={faSignOutAlt} />
                        </button>
                    </Tooltip>
                </RightNavigation>
            </div>
        </div>
    );
};
