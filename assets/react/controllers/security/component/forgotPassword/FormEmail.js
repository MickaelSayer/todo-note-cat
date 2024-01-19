import React, { useContext } from "react";
import { securityContext } from "../../../context/security/securityContext";
import ContainerErrorFormField from "../../../components/form/ContainerErrorFormField";
import Label from "../../../components/form/Label";
import InputText from "../../../components/form/InputText";
import SubmitButton from "../../../components/form/SubmitButton";
import Form from "../../../components/form/Form";
import Loading from "../../../components/loading";
import Modal from "../../../components/Modal";
import CriticalError from "../../../components/CriticalError";
import Exception from "../../../components/Exception";
import Mascot from "../../../components/Mascot";

const FormEmail = ({ IsForgotPassword, setIsForgotPassword }) => {
    const { loading, getSecurityFunction } = useContext(securityContext);

    const DEFAULT_VALUE = {
        defaultValues: {
            email: "",
        },
    };

    const onSubmit = (formData) => {
        getSecurityFunction.forgotPassword(formData, setIsForgotPassword);
    };

    return (
        IsForgotPassword && (
            <Modal setExit={setIsForgotPassword}>
                <Mascot type="admin">
                    Je t'enverrai un e-mail pour modifier ton mot de passe. <br />
                    Et tu auras 1 minute pour cliquer sur le jolie bouton.
                </Mascot>
                <CriticalError />
                <Exception />
                {loading && <Loading text="Attends, je regarde si je te connais" />}
                <Form
                    on_submit={onSubmit}
                    default_value={DEFAULT_VALUE}
                >
                    <InputForgotPasswordEmail />
                    <SubmitButton
                        className="kitty-forgot"
                        btn_title="JE VAIS ENVOYER TON ADRESSE E-MAIL PROMIS !!!"
                        optionsButton={{
                            path: require("../../../../../images/kitty_forgot.png"),
                            description: "Un chat avec un dictaphone",
                            size: 40,
                            label: "Envoyer",
                        }}
                    />
                </Form>
            </Modal>
        )
    );
};

const InputForgotPasswordEmail = () => {
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

export default FormEmail;
