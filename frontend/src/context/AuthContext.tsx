// frontend/src/context/AuthContext.tsx
import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { AuthState, User, Company } from '../types/auth';

interface AuthContextType extends AuthState {
  companies: Company[];
  login: (user: User, companies: Company[]) => void;
  selectCompany: (token: string, company: Company, role: string) => void;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [authState, setAuthState] = useState<AuthState>({
    user: null,
    token: null,
    company: null,
    role: null,
    companies: [],
  });

  // Load from localStorage on mount
  useEffect(() => {
    const storedAuth = localStorage.getItem('auth');
    if (storedAuth) {
      setAuthState(JSON.parse(storedAuth));
    }
  }, []);

  // Save to localStorage on change
  useEffect(() => {
    if (authState.token) {
      localStorage.setItem('auth', JSON.stringify(authState));
    } else {
      localStorage.removeItem('auth');
    }
  }, [authState]);

  const login = (user: User, companies: Company[]) => {
    setAuthState({ user, token: null, company: null, role: null, companies });
  };

  const selectCompany = (token: string, company: Company, role: string) => {
    setAuthState(prev => ({ ...prev, token, company, role }));
  };

  const logout = () => {
    setAuthState({ user: null, token: null, company: null, role: null, companies: [] });
    localStorage.removeItem('auth');
  };

  const isAuthenticated = !!authState.token && !!authState.company;

  return (
    <AuthContext.Provider value={{ ...authState, companies: authState.companies, login, selectCompany, logout, isAuthenticated }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
