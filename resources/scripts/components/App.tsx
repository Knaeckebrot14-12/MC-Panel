import React, { lazy } from 'react';
import { hot } from 'react-hot-loader/root';
import { Route, Router, Switch } from 'react-router-dom';
import { StoreProvider } from 'easy-peasy';
import { store } from '@/state';
import { SiteSettings } from '@/state/settings';
import ProgressBar from '@/components/elements/ProgressBar';
import { NotFound } from '@/components/elements/ScreenBlock';
import tw from 'twin.macro';
import GlobalStylesheet from '@/assets/css/GlobalStylesheet';
import { history } from '@/components/history';
import { setupInterceptors } from '@/api/interceptors';
import AuthenticatedRoute from '@/components/elements/AuthenticatedRoute';
import { ServerContext } from '@/state/server';
import '@/assets/tailwind.css';
import Spinner from '@/components/elements/Spinner';
import ForcedPasswordChangeContainer from '@/components/auth/ForcedPasswordChangeContainer';
import { MaintenanceBanner, MaintenanceScreen } from '@/components/MaintenanceNotice';
import ImpersonationBanner from '@/components/ImpersonationBanner';

const DashboardRouter = lazy(() => import(/* webpackChunkName: "dashboard" */ '@/routers/DashboardRouter'));
const ServerRouter = lazy(() => import(/* webpackChunkName: "server" */ '@/routers/ServerRouter'));
const AuthenticationRouter = lazy(() => import(/* webpackChunkName: "auth" */ '@/routers/AuthenticationRouter'));

interface ExtendedWindow extends Window {
    SiteConfiguration?: SiteSettings;
    // Set while a staff member is signed in as this user (support view).
    PterodactylImpersonator?: { username: string };
    PterodactylUser?: {
        uuid: string;
        username: string;
        email: string;
        /* eslint-disable camelcase */
        root_admin: boolean;
        staff?: boolean;
        role?: string;
        use_totp: boolean;
        must_change_password: boolean;
        email_verified_at?: string | null;
        discord_username?: string | null;
        language: string;
        updated_at: string;
        created_at: string;
        /* eslint-enable camelcase */
    };
}

setupInterceptors(history);

const App = () => {
    const { PterodactylUser, SiteConfiguration, PterodactylImpersonator } = window as ExtendedWindow;
    if (PterodactylUser && !store.getState().user.data) {
        store.getActions().user.setUserData({
            uuid: PterodactylUser.uuid,
            username: PterodactylUser.username,
            email: PterodactylUser.email,
            language: PterodactylUser.language,
            rootAdmin: PterodactylUser.root_admin,
            staff: PterodactylUser.staff ?? PterodactylUser.root_admin,
            useTotp: PterodactylUser.use_totp,
            mustChangePassword: PterodactylUser.must_change_password,
            emailVerified: !!PterodactylUser.email_verified_at,
            discordUsername: PterodactylUser.discord_username ?? null,
            createdAt: new Date(PterodactylUser.created_at),
            updatedAt: new Date(PterodactylUser.updated_at),
        });
    }

    if (!store.getState().settings.data) {
        store.getActions().settings.setSettings(SiteConfiguration!);
    }

    // A user who logged in with a temporary generated password (e.g. via
    // "forgot password") must set a password of their own before they can
    // reach anything else in the panel — including the auth routes, since
    // they're already logged in at this point.
    const mustChangePassword = !!PterodactylUser?.must_change_password;

    // Maintenance: "banner" is shown to everybody; "lock" shows the banner to the team and keeps
    // everyone else on the maintenance screen (they can still reach the login page).
    const maintenance = SiteConfiguration?.maintenance;
    const isStaff = !!(PterodactylUser?.staff ?? PterodactylUser?.root_admin);
    const lockedOut =
        maintenance?.mode === 'lock' && !!PterodactylUser && !isStaff && !window.location.pathname.startsWith('/auth');
    const showBanner = maintenance && (maintenance.mode === 'banner' || (maintenance.mode === 'lock' && isStaff));

    return (
        <>
            <GlobalStylesheet />
            <StoreProvider store={store}>
                <ProgressBar />
                {PterodactylImpersonator && PterodactylUser && (
                    // Its translations load on demand, which suspends rendering for a moment.
                    <React.Suspense fallback={null}>
                        <ImpersonationBanner staff={PterodactylImpersonator.username} user={PterodactylUser.username} />
                    </React.Suspense>
                )}
                {showBanner && !lockedOut && (
                    <React.Suspense fallback={null}>
                        <MaintenanceBanner maintenance={maintenance!} staffView={maintenance!.mode === 'lock'} />
                    </React.Suspense>
                )}
                <div css={tw`mx-auto w-auto`}>
                    {lockedOut ? (
                        <React.Suspense fallback={null}>
                            <MaintenanceScreen
                                maintenance={maintenance!}
                                statusPage={!!SiteConfiguration?.statusPage}
                            />
                        </React.Suspense>
                    ) : mustChangePassword ? (
                        <ForcedPasswordChangeContainer />
                    ) : (
                        <Router history={history}>
                            <Switch>
                                <Route path={'/auth'}>
                                    <Spinner.Suspense>
                                        <AuthenticationRouter />
                                    </Spinner.Suspense>
                                </Route>
                                <AuthenticatedRoute path={'/server/:id'}>
                                    <Spinner.Suspense>
                                        <ServerContext.Provider>
                                            <ServerRouter />
                                        </ServerContext.Provider>
                                    </Spinner.Suspense>
                                </AuthenticatedRoute>
                                <AuthenticatedRoute path={'/'}>
                                    <Spinner.Suspense>
                                        <DashboardRouter />
                                    </Spinner.Suspense>
                                </AuthenticatedRoute>
                                <Route path={'*'}>
                                    <NotFound />
                                </Route>
                            </Switch>
                        </Router>
                    )}
                </div>
            </StoreProvider>
        </>
    );
};

export default hot(App);
