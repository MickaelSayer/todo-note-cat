import React, { createContext } from 'react';
import useSecurity from '../../axios/RequestSecurity';

const securityContext = createContext(null);

const SecurityProvider = ({ children }) => {
    const { loading, loadingAuth, getSecurityFunction } = useSecurity();

    return (
        <securityContext.Provider
            value={{ loading, loadingAuth, getSecurityFunction }}
        >
            {children}
        </securityContext.Provider>
    );
};

export { securityContext, SecurityProvider };
