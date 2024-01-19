import React, { createContext } from 'react';
import useToken from '../../hooks/security/useToken';

const securityTokenContext = createContext(null);

const SecurityTokenProvider = ({ children }) => {
    const { getTokenFunction } = useToken();

    return (
        <securityTokenContext.Provider
            value={{ getTokenFunction }}
        >
            {children}
        </securityTokenContext.Provider>
    );
};

export { securityTokenContext, SecurityTokenProvider };
