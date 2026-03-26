import React, { createContext, useContext, useState } from 'react';

type TableSize = 'default' | 'middle' | 'small';

interface SettingsContextType {
    tableSize: TableSize;
    setTableSize: (size: TableSize) => void;
}

const SettingsContext = createContext<SettingsContextType | undefined>(undefined);

export const SettingsProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [tableSize, setTableSizeState] = useState<TableSize>(() => {
        const saved = localStorage.getItem('finance_table_size');
        return (saved as TableSize) || 'middle';
    });

    const setTableSize = (size: TableSize) => {
        setTableSizeState(size);
        localStorage.setItem('finance_table_size', size);
    };

    return (
        <SettingsContext.Provider value={{ tableSize, setTableSize }}>
            {children}
        </SettingsContext.Provider>
    );
};

export const useSettings = () => {
    const context = useContext(SettingsContext);
    if (!context) {
        throw new Error('useSettings must be used within a SettingsProvider');
    }
    return context;
};
