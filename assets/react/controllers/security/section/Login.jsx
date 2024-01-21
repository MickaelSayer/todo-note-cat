import React, { useContext, useEffect } from "react";
import FormLogin from "../component/FormLogin";
import { flashMessageContext } from "../../context/FlashMessageContext";
import { useNavigate } from "react-router-dom";
import { securityTokenContext } from "../../context/security/securityTokenContext";

const Login = () => {
    const { getFlashFunction } = useContext(flashMessageContext);
    const { getTokenFunction } = useContext(securityTokenContext);
    const navigate = useNavigate();

    const TOKEN_AUTH = getTokenFunction.getTokenLocalStorage("token_at");

    useEffect(() => {
        getFlashFunction.deleteAllFlashErrorsMessage();

        if (TOKEN_AUTH) {
            navigate("/");
        } else {
            const searchParams = new URLSearchParams(location.search);
            const validEmail = searchParams.get("validEmail");
            const fortgotPassword = searchParams.get("fortgotPassword");

            if (validEmail && validEmail == 1) {
                getFlashFunction.addSuccess(
                    "Ton adresse e-mail a été validée; tu peux te connecter maintenant."
                );

                const timeoutId = setTimeout(() => {
                    getFlashFunction.addSuccess(null);
                }, 5000);

                return () => clearTimeout(timeoutId);
            }

            if (fortgotPassword && fortgotPassword == 0) {
                console.log('test');
                getFlashFunction.addCriticalError(
                    "Aïe, je ne peux pas modifier ton mot de passe. Réessaie."
                );
            }
        }
    }, []);

    return (
        !TOKEN_AUTH && (
            <section id="login">
                <div className="my-5">
                    <h1 className="display-3 text-center fw-bold">Bienvenue</h1>
                    <h3 className="text-center">On se connait ?</h3>
                </div>
                <FormLogin />
            </section>
        )
    );
};

export default Login;
