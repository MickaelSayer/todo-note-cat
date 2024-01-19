import React, { createContext } from 'react';
import useFlashMessage from '../hooks/useFlashMessage';

const flashMessageContext = createContext(null);

const FlashMessageProvider = ({ children }) => {
    const { flash, setFlash, getFlashFunction } = useFlashMessage();

    return (
        <flashMessageContext.Provider
            value={{ flash, setFlash, getFlashFunction }}
        >
            {children}
        </flashMessageContext.Provider>
    );
};

export { flashMessageContext, FlashMessageProvider };
