const useToken = () => {
    /**
     * Add the token to the location
     * 
     * @param {string} token_name The name of the token
     * @param {string} token The token to add
     */
    const _setTokenLocalStorage = (token_name, token) => {
        localStorage.setItem(token_name, token);
    }

    /**
     * Remove the token from the storage room
     * 
     * @param {string} token_name The name of the token
     */
    const _removeTokenLocalStorage = (token_name) => {
        localStorage.removeItem(token_name);
    }

    /**
     * Recover the token of the location
     * 
     * @param {string} token_name The name of the token
     * @returns The token
     */
    const _getTokenLocalStorage = (token_name) => {
        return localStorage.getItem(token_name)
    }

    const getTokenFunction = {
        getTokenLocalStorage: _getTokenLocalStorage,
        setTokenLocalStorage: _setTokenLocalStorage,
        removeTokenLocalStorage: _removeTokenLocalStorage
    };

    return {
        getTokenFunction
    }
}

export default useToken;