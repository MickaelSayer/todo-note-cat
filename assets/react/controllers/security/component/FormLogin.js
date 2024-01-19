import React, { useContext, useState } from "react";
import { securityContext } from "../../context/security/securityContext";
import ContainerErrorFormField from "../../components/form/ContainerErrorFormField";
import Label from "../../components/form/Label";
import InputText from "../../components/form/InputText";
import SubmitButton from "../../components/form/SubmitButton";
import Form from "../../components/form/Form";
import CriticalError from "../../components/CriticalError";
import Exception from "../../components/Exception";
import Popup from "../../components/Popup";
import FormPasswordForgotEmail from "./forgotPassword/FormEmail";

const FormLogin = () => {
    const { getSecurityFunction } = useContext(securityContext);
    const [IsForgotPassword, setIsForgotPassword] = useState(false);

    const DEFAULT_VALUE = {
        defaultValues: {
            email: "",
            password: "",
        },
    };

    const onSubmit = (formData) => {
        getSecurityFunction.checkFormLogin(formData);
    };

    return (
        <>
            <Popup />
            <CriticalError />
            <Exception />
            <Form
                on_submit={onSubmit}
                default_value={DEFAULT_VALUE}
            >
                <InputMail />
                <InputPassword />
                <div
                    className="forgot-password"
                    title="À ce que je vois, tu as oublié ton mot de passe."
                >
                    <span
                        onClick={() => setIsForgotPassword(!IsForgotPassword)}
                    >
                        Mot de passe oublié ?
                    </span>
                </div>
                <SubmitButton
                    className="kitty-astro"
                    btn_title="Aaaaah, ta demande sera plus rapide que les étoiles."
                    optionsButton={{
                        path: require("../../../../images/kitty_astro.png"),
                        description: "Un chat astronaute",
                        size: 40,
                        label: "Se connecter",
                    }}
                />
            </Form>

            <FormPasswordForgotEmail
                IsForgotPassword={IsForgotPassword}
                setIsForgotPassword={setIsForgotPassword}
            />
        </>
    );
};

const InputMail = () => {
    const NAME = "email";

    return (
        <>
            <Label name={NAME} content="Ton adresse mail"></Label>
            <InputText
                name={NAME}
                description="Tu n'as qu'à inscrire ton adresse e-mail"
                options_validation={{
                    pattern: {
                        value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                        message:
                            "Ton adresse e-mail doit ressembler à une adresse e-mail.",
                    },
                    required:
                        "Sans adresse mail, tu n'iras pas loin, je t'assure !",
                }}
            />
            <ContainerErrorFormField name={NAME} />
        </>
    );
};

const InputPassword = () => {
    const NAME = "password";

    return (
        <>
            <Label name={NAME} content="Ton mot de passe"></Label>
            <InputText
                name={NAME}
                description="Indique ton plus beau mot de passe"
                options_validation={{
                    required: "Sans ton mot de passe je ne peux rien faire",
                }}
                type="password"
            />
            <ContainerErrorFormField name={NAME} />
        </>
    );
};

export default FormLogin;
