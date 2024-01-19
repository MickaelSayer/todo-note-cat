import { m, AnimatePresence, LazyMotion, domAnimation } from "framer-motion"
import React from "react";
import { useFormContext } from "react-hook-form";

const ContainerErrorFormField = ({ name, is_list = false }) => {
    const { formState: { errors } } = useFormContext();

    let ERROR = [];
    let ERROR_MESSAGE = '';
    if (is_list) {
        const parts = name.split(".");
        const [field, index, key] = parts;

        ERROR = errors?.[field];
        ERROR_MESSAGE = errors?.[field]?.[index]?.[key]?.message;
    } else {
        ERROR = errors?.[name];
        ERROR_MESSAGE = ERROR?.message;
    }

    return (
        <LazyMotion features={domAnimation}>
            <AnimatePresence>
                <div className="is-invalid ms-1">

                    {ERROR && (
                        <m.div
                            initial={{ x: "-100%", opacity: 0 }}
                            animate={{ x: "0", opacity: 1 }}
                            exit={{ x: "-100%", opacity: 0 }}
                        >
                            {ERROR_MESSAGE}
                        </m.div>
                    )}

                </div>
            </AnimatePresence>
        </LazyMotion>
    )
}

export default ContainerErrorFormField;