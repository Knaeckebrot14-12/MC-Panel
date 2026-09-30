import React from 'react';
import { useTranslation } from 'react-i18next';
import { NavLink, Route, Switch } from 'react-router-dom';
import NavigationBar from '@/components/NavigationBar';
import DashboardContainer from '@/components/dashboard/DashboardContainer';
import CreateServerContainer from '@/components/dashboard/CreateServerContainer';
import { NotFound } from '@/components/elements/ScreenBlock';
import TransitionRouter from '@/TransitionRouter';
import SubNavigation from '@/components/elements/SubNavigation';
import { useLocation } from 'react-router';
import Spinner from '@/components/elements/Spinner';
import routes from '@/routers/routes';

export default () => {
    const { t } = useTranslation('navigation');
    const location = useLocation();

    return (
        <>
            <NavigationBar />
            {location.pathname.startsWith('/account') && (
                <SubNavigation>
                    <div>
                        {routes.account
                            .filter((route) => !!route.name)
                            .map(({ path, name, exact = false }) => (
                                <NavLink key={path} to={`/account/${path}`.replace('//', '/')} exact={exact}>
                                    {t(name!)}
                                </NavLink>
                            ))}
                    </div>
                </SubNavigation>
            )}
            {location.pathname.startsWith('/coins') && (
                <SubNavigation>
                    <div>
                        {routes.coins
                            .filter((route) => !!route.name)
                            .map(({ path, name, exact = false }) => (
                                <NavLink key={path} to={`/coins/${path}`.replace('//', '/')} exact={exact}>
                                    {t(name!)}
                                </NavLink>
                            ))}
                    </div>
                </SubNavigation>
            )}
            {location.pathname.startsWith('/tickets') && (
                <SubNavigation>
                    <div>
                        {routes.tickets
                            .filter((route) => !!route.name)
                            .map(({ path, name, exact = false }) => (
                                <NavLink key={path} to={`/tickets/${path}`.replace('//', '/')} exact={exact}>
                                    {t(name!)}
                                </NavLink>
                            ))}
                    </div>
                </SubNavigation>
            )}
            <TransitionRouter>
                <React.Suspense fallback={<Spinner centered />}>
                    <Switch location={location}>
                        <Route path={'/'} exact>
                            <DashboardContainer />
                        </Route>
                        <Route path={'/create-server'} exact>
                            <CreateServerContainer />
                        </Route>
                        {routes.account.map(({ path, component: Component }) => (
                            <Route key={path} path={`/account/${path}`.replace('//', '/')} exact>
                                <Component />
                            </Route>
                        ))}
                        {routes.coins.map(({ path, component: Component }) => (
                            <Route key={path} path={`/coins/${path}`.replace('//', '/')} exact>
                                <Component />
                            </Route>
                        ))}
                        {routes.tickets.map(({ path, component: Component }) => (
                            <Route key={path} path={`/tickets/${path}`.replace('//', '/')} exact>
                                <Component />
                            </Route>
                        ))}
                        <Route path={'*'}>
                            <NotFound />
                        </Route>
                    </Switch>
                </React.Suspense>
            </TransitionRouter>
        </>
    );
};
