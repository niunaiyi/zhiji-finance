import React, { createContext, useContext, useState, useEffect, type ReactNode } from 'react';
import apiClient from '../api/client';
import { useAuth } from './AuthContext';

export interface Book {
    id: number;
    code: string;
    name: string;
    fiscal_year_start: number;
    status: string;
}

interface BookContextType {
    currentBook: Book | null;
    books: Book[];
    switchBook: (bookId: number) => void;
    loading: boolean;
}

const BookContext = createContext<BookContextType | undefined>(undefined);

export const BookProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const { token } = useAuth();
    const [currentBook, setCurrentBook] = useState<Book | null>(null);
    const [books, setBooks] = useState<Book[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Only fetch companies if user is authenticated
        if (!token) {
            setLoading(false);
            setBooks([]);
            setCurrentBook(null);
            return;
        }
        
        setLoading(true);
        apiClient.get('/v1/auth/companies').then(res => {
            const fetchedBooks = res.data?.data || res.data?.companies || [];
            setBooks(fetchedBooks);

            if (fetchedBooks.length > 0) {
                // Restore from local storage
                const savedCompanyId = localStorage.getItem('currentCompanyId');

                let found = savedCompanyId
                    ? fetchedBooks.find((b: Book) => b.id === parseInt(savedCompanyId))
                    : fetchedBooks[0];

                if (!found) found = fetchedBooks[0];

                setCurrentBook(found);
                localStorage.setItem('currentCompanyId', found.id.toString());
            }
            setLoading(false);
        }).catch(err => {
            console.error("Failed to fetch companies", err);
            setLoading(false);
        });
    }, [token]);

    const switchBook = (bookId: number) => {
        const target = books.find(b => b.id === bookId);

        if (target) {
            setCurrentBook(target);
            localStorage.setItem('currentCompanyId', target.id.toString());
            window.location.reload();
        }
    };

    return (
        <BookContext.Provider value={{ currentBook, books, switchBook, loading }}>
            {children}
        </BookContext.Provider>
    );
};

export const useBook = () => {
    const context = useContext(BookContext);
    if (!context) {
        throw new Error('useBook must be used within a BookProvider');
    }
    return context;
};
