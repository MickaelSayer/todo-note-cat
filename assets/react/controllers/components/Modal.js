import React, { useContext } from "react";
import { AnimatePresence, LazyMotion, domAnimation, m } from "framer-motion";
import { flashMessageContext } from "../context/FlashMessageContext";

const Modal = ({ setExit, children }) => {
    const { getFlashFunction } = useContext(flashMessageContext);

    return (
        <LazyMotion features={domAnimation}>
            <AnimatePresence>
                <m.div
                    className="modal"
                    tabIndex={-1}
                    role="dialog"
                    style={{ display: "block", backdropFilter: 'blur(5px)' }}
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                >
                    <div className="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <m.div
                            className="modal-content border-0 rounded-5 p-4 position-relative"
                            initial={{ x: '-100%' }}
                            animate={{ x: 0 }}
                            exit={{ x: '-100%' }}
                            transition={{ duration: 0.7, type: "tween" }}
                        >
                            <div className="modal-header justify-content-center border-0 p-0">
                                <button
                                    type="button"
                                    className="btn-md pb-2 rounded-5"
                                    onClick={() => {
                                        setExit(prevSetExit => !prevSetExit);
                                        getFlashFunction.deleteAllFlashErrorsMessage();
                                    }}
                                    title="Par mille sabords, tu veux partir, matelot ?"
                                >
                                    <img
                                        src={require("../../../images/kitty_close.png")}
                                        alt="Un chat pirate"
                                        height="50"
                                        width="50"
                                    />
                                </button>
                            </div>
                            <div className="modal-body mt-4 p-0">
                                {children}
                            </div>
                        </m.div>
                    </div>
                </m.div>
            </AnimatePresence>
        </LazyMotion>
    )
}

export default Modal;