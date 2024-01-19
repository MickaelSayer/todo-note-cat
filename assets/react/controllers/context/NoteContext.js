import React, { createContext } from 'react';
import RequestNote from '../axios/RequestNote';

const requestNoteContext = createContext(null);

const RequestNoteProvider = ({ children }) => {
    const { datas, setDatas, loading, getActionFunction} = RequestNote();

    return (
        <requestNoteContext.Provider
            value={{ datas, setDatas, loading, getActionFunction }}
        >
            {children}
        </requestNoteContext.Provider>
    );
};

export { requestNoteContext, RequestNoteProvider };
