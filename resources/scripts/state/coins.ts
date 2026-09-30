import { Action, action } from 'easy-peasy';

export interface CoinsStore {
    balance?: number;
    setBalance: Action<CoinsStore, number>;
}

const coins: CoinsStore = {
    balance: undefined,
    setBalance: action((state, payload) => {
        state.balance = payload;
    }),
};

export default coins;
