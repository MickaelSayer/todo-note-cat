import React, { useContext, useEffect, useState } from "react";
import { securityContext } from "../../context/security/securityContext";
import ContainerErrorFormField from "../../components/form/ContainerErrorFormField";
import Label from "../../components/form/Label";
import InputText from "../../components/form/InputText";
import SubmitButton from "../../components/form/SubmitButton";
import Form from "../../components/form/Form";
import CriticalError from "../../components/CriticalError";
import Loading from "../../components/loading";
import { useFormContext } from "react-hook-form";
import Exception from "../../components/Exception";

const FormSignUp = () => {
    const { loading, getSecurityFunction } = useContext(securityContext);

    const DEFAULT_VALUE = {
        defaultValues: {
            email: "",
            password: "",
        },
    };

    const onSubmit = (formData) => {
        getSecurityFunction.signUp(formData);
    };

    return (
        <>
            <CriticalError />
            <Exception />
            {loading && <Loading text="Je vérifie tes données" />}
            <Form
                on_submit={onSubmit}
                default_value={DEFAULT_VALUE}
            >
                <_inputMail />
                <_inputPassword />
                <SubmitButton
                    className='kitty-admin'
                    btn_title="Hum..hum... Tu veux envoyer ta super création de compte ?"
                    optionsButton={{
                        path: require("../../../../images/kitty_admin.png"),
                        description: "Un chat super héros",
                        size: 40,
                        label: "Créer mon compte",
                    }}
                />
            </Form>
        </>
    );
};

const _inputMail = () => {
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

const _inputPassword = () => {
    const NAME = "password";

    return (
        <>
            <Label name={NAME} content="Ton mot de passe"></Label>
            <InputText 
                name={NAME}
                description="Indique ton plus beau mot de passe"
                options_validation={{
                    pattern: {
                        value: "^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&^#])[A-Za-z\d@$!%*?&^#]{8,50}$",
                        message: "Ton mot de passe doit contenir au moins :",
                    },
                    required: "Sans ton mot de passe je ne peux rien faire",
                }}
                type="password"
            />
            <ContainerErrorFormField name={NAME} />
            <PasswordListPattern />
        </>
    );
};

const PasswordListPattern = () => {
    const { watch } = useFormContext();

    const [pattern, setPattern] = useState([
        {
            pattern: /^(?=.*.{8,})/,
            content: '8 caractères minimum',
            checked: false,
        },
        {
            pattern: /^(?=.*[A-Z])/,
            content: '1 majuscule',
            checked: false,
        },
        {
            pattern: /^(?=.*[a-z])/,
            content: '1 minuscule',
            checked: false,
        },
        {
            pattern: /^(?=.*\d)/,
            content: '1 chiffre',
            checked: false,
        },
        {
            pattern: /^(?=.*[!@#$%^&*?&])/,
            content: '1 caractère spécial',
            checked: false,
        },
    ]);

    useEffect(() => {
        const updatedPattern = pattern.map((item) => ({
            ...item,
            checked: item.pattern.test(watch('password')),
        }));
        setPattern(updatedPattern);
    }, [watch('password')]);

    return (
        <ul className="list-group list-group-flush password-list mt-3">
            {pattern.map((item, index) => (
                <li key={index} className="list-group-item d-flex align-items-center py-2">
                    <input id={index} className='inpLock' type="checkbox" checked={item.checked} onChange={() => null} />
                    <label className="btn-lock" htmlFor={index}>
                        <svg width="16" height="20" viewBox="0 0 36 40">
                            <path className="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path className="lock" d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path className="bling" d="M29 20L31 22"></path>
                            <path className="bling" d="M31.5 15H34.5"></path>
                            <path className="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                    <span className="ms-3">{item.content}</span>
                </li>
            ))}
        </ul>
    );
}

export default FormSignUp;
