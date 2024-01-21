import { useRef, useState } from "react"

const useFlashMessage = () => {
    const [flash, setFlash] = useState({
        success: null,
        validation_errors: [],
        critical_error: [],
        exception: [],
        warning: null
    })
    const popupTimeoutRef = useRef(null);

    /**
     * Add the reset of the popup
     */
    const _addOpenPopupSuccess = () => {
        if (popupTimeoutRef.current) {
            clearTimeout(popupTimeoutRef.current);
        }

        const timeout = setTimeout(() => {
            setFlash(prevResponse => ({
                ...prevResponse,
                success: null
            }))
        }, 5000);

        popupTimeoutRef.current = timeout;
    }

    /**
     * Add a critical
     * 
     * @param {string} message The critical message
     * @param {string} type_display Content display ['content', 'non-content]
     * @param {function} handleDisplayForm Function that allows you to change the display of a component
     */
    const _addCriticalError = (message, handleDisplayForm = null, type_display = 'content') => {
        _deleteAllFlashErrorsMessage();

        setFlash(prevResponse => ({
            ...prevResponse,
            critical_error: {
                error_type: type_display,
                error_message: message
            },
        }))

        if (handleDisplayForm) {
            handleDisplayForm(prevDisplay => !prevDisplay);
        }
    }

    /**
     * Add a exception
     * 
     * @param {string} message The exception message
     * @param {string} type_display Content display ['content', 'non-content]
     * @param {function} handleDisplayForm Function that allows you to change the display of a component
     */
    const _addException = (message, handleDisplayForm = null, type_display = 'content') => {
        _deleteAllFlashErrorsMessage();

        setFlash(prevResponse => ({
            ...prevResponse,
            exception: {
                error_type: type_display,
                error_message: message
            },
        }))

        if (handleDisplayForm) {
            handleDisplayForm(prevDisplay => !prevDisplay);
        }
    }

    /**
     * Add a validation form
     * 
     * @param {string} message The validation form message
     */
    const _addFormValidation = (message) => {
        _deleteAllFlashErrorsMessage();

        setFlash(prevResponse => ({
            ...prevResponse,
            validation_errors: message
        }))
    }

    /**
     * Add a message from warning
     * 
     * @param {string} message Warning's message
     */
    const _addWarning = (message) => {
        _deleteAllFlashErrorsMessage();

        setFlash(prevResponse => ({
            ...prevResponse,
            warning: message
        }));
    }

    /**
     * Add a success message
     * 
     * @param {string} message The success message
     * @param {function} handleDisplayForm Function that allows you to change the display of a component
     */
    const _addSuccess = (message, handleDisplayForm = null) => {
        _deleteAllFlashErrorsMessage();

        setFlash(prevResponse => ({
            ...prevResponse,
            success: message
        }))

        if (handleDisplayForm) {
            handleDisplayForm(prevDisplay => !prevDisplay);
        }
    }

    const _deleteAllFlashErrorsMessage = () => {
        setFlash(prevResponse => ({
            ...prevResponse,
            critical_error: [],
            exception: [],
            validation_errors: [],
            warning: null
        }))
    }

    /**
     * Add flash error message
     * 
     * @param {object} error The API response for errors
     * @param {string} typeDisplay Content display (Content, No-Content)
     * @param {function} handleDisplayForm Function that allows you to change the display of a component
     */
    const _addErrors = (error, handleDisplayForm = null, typeDisplay = 'content') => {
        const ERROR = error?.response?.data;
        const CRITICAL_ERROR = ERROR?.critical_error;
        const EXCEPTION_ERROR = ERROR?.exception;
        const VALIDATION_ERROR = ERROR?.validation;

        if (CRITICAL_ERROR) {
            _addCriticalError(CRITICAL_ERROR, handleDisplayForm, typeDisplay);
        }

        if (EXCEPTION_ERROR) {
            _addException(EXCEPTION_ERROR, handleDisplayForm, typeDisplay);
        }

        if (VALIDATION_ERROR) {
            _addFormValidation(VALIDATION_ERROR);
        }
    }

    /**
     * Brings together all the raw action of the tasks
     */
    const getFlashFunction = {
        addOpenPopupSuccess: _addOpenPopupSuccess,
        addCriticalError: _addCriticalError,
        addException: _addException,
        addFormValidation: _addFormValidation,
        addSuccess: _addSuccess,
        addWarning: _addWarning,
        deleteAllFlashErrorsMessage: _deleteAllFlashErrorsMessage,
        addErrors: _addErrors
    };

    return {
        flash,
        setFlash,
        getFlashFunction
    }
}

export default useFlashMessage;