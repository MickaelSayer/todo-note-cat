import React from "react";
import { FormProvider, useForm } from "react-hook-form";
import Validation from "../Validation";

const Form = ({ on_submit, default_value, children }) => {
    const METHODS = useForm(default_value);

    return (
        <FormProvider {...METHODS}>
            <Validation />
            <form
                onSubmit={METHODS.handleSubmit(on_submit)}
                className="overflow-hidden p-3"
            >
                {children}
            </form>
        </FormProvider>
    )
}

export default Form;