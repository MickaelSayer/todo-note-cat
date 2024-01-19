import React, { useContext } from "react";
import { AnimatePresence, LazyMotion, domAnimation, m } from "framer-motion";
import Mascot from "./Mascot";
import { flashMessageContext } from "../context/FlashMessageContext";

const Exception = () => {
    const { flash, getFlashFunction } = useContext(flashMessageContext);
    const IS_EXCEPTION = Object.keys(flash?.exception).length === 0;

    return (
        <LazyMotion features={domAnimation}>
            <AnimatePresence>
                {!IS_EXCEPTION &&
                    <m.div
                        initial={{ y: "-100%", opacity: 0 }}
                        animate={{ y: "0", opacity: 1 }}
                        exit={{ y: "-100%", opacity: 0 }}
                        className="alert critical-error text-center"
                        role="alert"
                    >
                        <Mascot type="exception">
                            {flash?.exception?.error_type !== 'no-content' &&
                                <div className="position-absolute top-0 start-100 translate-middle">
                                    <img
                                        height="24"
                                        width="24"
                                        title="Tu veux fermer ce méchant message ?"
                                        src={require("../../../images/x.png")}
                                        alt="Fermer"
                                        onClick={() => getFlashFunction.deleteAllFlashErrorsMessage()}
                                        style={{ cursor: "pointer" }}
                                        className="btn-exit"
                                    />
                                </div>}
                            {flash?.exception?.error_message}
                        </Mascot>
                        {flash?.exception?.error_type === 'no-content' &&
                            <m.div
                                initial={{ y: "-100%", opacity: 0 }}
                                animate={{ y: "0", opacity: 1 }}
                                exit={{ y: "-100%", opacity: 0 }}
                                transition={{ delay: .5 }}
                                className="d-flex flex-column align-items-center mt-4"
                            >
                                <span className="text-decoration-underline mb-2">
                                    Aide-moi en rechargeant la page.
                                </span>
                                <img
                                    height="64"
                                    width="64"
                                    src={require("../../../images/kitty_reset.png")}
                                    alt="Un chat robot"
                                    onClick={() => window.location.reload()}
                                    className="btn-reload"
                                    title="Clique... sur... moi... pour... recharger LA page."
                                />
                            </m.div>}
                    </m.div>}
            </AnimatePresence>
        </LazyMotion>
    )
}

export default Exception;