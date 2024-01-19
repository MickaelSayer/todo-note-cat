import React, { useContext, useEffect } from "react";
import FormForgotPasswordPassword from "../component/forgotPassword/FormPassword";
import { securityContext } from "../../context/security/securityContext";
import Loading from "../../components/loading";

const ForgotPassword = () => {
    const { getSecurityFunction, loadingAuth } = useContext(securityContext);

    useEffect(() => {
        getSecurityFunction.checkAccessRoute("token_fp");
    }, []);

    return loadingAuth ? (
        <Loading text="Je vérifie si tu as l'accès" is_absolute="true" />
    ) : (
        <section id="login">
            <div className="my-5">
                <h1 className="display-3 text-center fw-bold">
                    Modification du mot de passe
                </h1>
            </div>
            <FormForgotPasswordPassword />
        </section>
    );
};

export default ForgotPassword;
