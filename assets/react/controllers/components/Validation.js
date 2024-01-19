import React, { useContext, useEffect, useRef } from "react";
import Mascot from "./Mascot";
import { flashMessageContext } from "../context/FlashMessageContext";

const Validation = () => {
    const { flash } = useContext(flashMessageContext);

    const IS_ERROR_VALIDATION = Object.keys(flash.validation_errors).length === 0;

    const ERROR_VALIDATION_REF = useRef();
    useEffect(() => {
        if (ERROR_VALIDATION_REF.current) {
            ERROR_VALIDATION_REF.current.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
        }
    }, [flash.validation_errors]);

    return (
        !IS_ERROR_VALIDATION && (
            <Mascot type="validation" ref={ERROR_VALIDATION_REF}>
                {Object.keys(flash.validation_errors).map(
                    (key, index) => (
                        <div key={index}>
                            {flash.validation_errors[key]}
                        </div>
                    )
                )}
            </Mascot>
        )
    )
}

export default Validation;