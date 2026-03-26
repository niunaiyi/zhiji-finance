import React, { createContext, useContext, useState, useEffect, type ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';

export interface TabItem {
    key: string;
    label: string;
    path: string;
    closable?: boolean;
}

interface TabContextType {
    tabs: TabItem[];
    activeKey: string;
    activePath: string;
    openTab: (key: string, label: string, path: string) => void;
    removeTab: (targetKey: string) => void;
    setActiveKey: (key: string) => void;
}

const TabContext = createContext<TabContextType | undefined>(undefined);

export const TabProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const navigate = useNavigate();

    const initialTabs: TabItem[] = [
        { key: '/', label: '控制面板', path: '/', closable: false }
    ];

    const [tabs, setTabs] = useState<TabItem[]>(() => {
        const saved = localStorage.getItem('openTabs');
        return saved ? JSON.parse(saved) : initialTabs;
    });

    const [activeKey, setActiveKey] = useState<string>(() => {
        return localStorage.getItem('activeTabKey') || '/';
    });

    const activePath = tabs.find(t => t.key === activeKey)?.path || '/';

    useEffect(() => {
        localStorage.setItem('openTabs', JSON.stringify(tabs));
    }, [tabs]);

    useEffect(() => {
        localStorage.setItem('activeTabKey', activeKey);
    }, [activeKey]);

    const openTab = (key: string, label: string, path: string) => {
        if (!tabs.find(t => t.key === key)) {
            const newTabs = [...tabs, { key, label, path }];
            setTabs(newTabs);
            localStorage.setItem('openTabs', JSON.stringify(newTabs));
        }
        setActiveKey(key);
        localStorage.setItem('activeTabKey', key);
        navigate(path);
    };

    const handleTabChange = (key: string) => {
        const tab = tabs.find(t => t.key === key);
        if (tab) {
            setActiveKey(key);
            localStorage.setItem('activeTabKey', key);
            navigate(tab.path);
        }
    };

    const removeTab = (targetKey: string) => {
        let newActiveKey = activeKey;
        let lastIndex = -1;
        tabs.forEach((tab, i) => {
            if (tab.key === targetKey) {
                lastIndex = i - 1;
            }
        });

        const newTabs = tabs.filter(tab => tab.key !== targetKey);
        if (newTabs.length && activeKey === targetKey) {
            if (lastIndex >= 0) {
                newActiveKey = newTabs[lastIndex].key;
            } else {
                newActiveKey = newTabs[0].key;
            }
        }

        setTabs(newTabs);
        setActiveKey(newActiveKey);

        const targetPath = newTabs.find(t => t.key === newActiveKey)?.path || '/';
        navigate(targetPath);
    };

    return (
        <TabContext.Provider value={{ tabs, activeKey, activePath, openTab, removeTab, setActiveKey: handleTabChange }}>
            {children}
        </TabContext.Provider>
    );
};

export const useTabs = () => {
    const context = useContext(TabContext);
    if (!context) {
        throw new Error('useTabs must be used within a TabProvider');
    }
    return context;
};
