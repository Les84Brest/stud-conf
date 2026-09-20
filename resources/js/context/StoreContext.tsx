// resources/js/context/StoreContext.tsx
import { createContext, useContext, type ReactNode } from 'react';
import { RootStore, rootStore } from '@/stores/RootStore';

const StoreContext = createContext<RootStore | null>(null);

interface StoreProviderProps {
    children: ReactNode;
    store?: RootStore;
}

export function StoreProvider({
    children,
    store = rootStore,
}: StoreProviderProps) {
    return (
        <StoreContext.Provider value={store}>{children}</StoreContext.Provider>
    );
}

export function useStore(): RootStore {
    const store = useContext(StoreContext);
    if (!store) {
        throw new Error('useStore must be used within StoreProvider');
    }
    
    return store;
}