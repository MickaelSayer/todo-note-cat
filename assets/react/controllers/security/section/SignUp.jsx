import React, { useContext, useEffect } from "react";
import FormSignUp from "../component/FormSignUp";
import { flashMessageContext } from "../../context/FlashMessageContext";
import { useNavigate } from "react-router-dom";
import { securityTokenContext } from "../../context/security/securityTokenContext";

const SignUp = () => {
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

            if (validEmail && validEmail == 0) {
                getFlashFunction.addCriticalError(
                    "Impossible de valider ton adresse e-mail. Réessaie de créer un nouveau compte."
                );
            }
        }
    }, []);

    return (
        !TOKEN_AUTH && (
            <section id="signUp">
                <div className="my-5">
                    <h1 className="display-3 text-center fw-bold">
                        Crée ton compte
                    </h1>
                    <h3 className="text-center">pour mieux se connaître.</h3>
                </div>
                <FormSignUp />
            </section>
        )
    );
};

export default SignUp;
