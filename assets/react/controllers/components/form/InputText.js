import React, { useContext } from "react";
import { useFormContext } from "react-hook-form";
import { flashMessageContext } from "../../context/FlashMessageContext";

const InputText = ({ name, description, options_validation, type = 'text' }) => {
    const {
        register,
        formState: { errors },
        clearErrors
    } = useFormContext();

    const { getFlashFunction } = useContext(flashMessageContext);

    const ERROR = errors?.[name];
    const ERROR_IS_INVALID = ERROR ? "field-invalid" : "";

    const handleClearErrors = () => {
        ERROR && clearErrors(name);

        getFlashFunction.deleteAllFlashErrorsMessage();
    }

    return (
        <>
            <input
                type={type}
                className={`form-control input ${ERROR_IS_INVALID}`}
                placeholder={description}
                autoComplete="on"
                id={name}
                {...register(name, options_validation)}
                onClick={handleClearErrors}
            />
        </>
    )
}

export default InputText;