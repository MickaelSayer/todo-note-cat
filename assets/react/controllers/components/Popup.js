import { m, AnimatePresence, LazyMotion, domAnimation } from "framer-motion";
import Mascot from "./Mascot";
import React, { useContext } from "react";
import { flashMessageContext } from "../context/FlashMessageContext";

const Popup = () => {
    const { flash } = useContext(flashMessageContext);

    return (
        <LazyMotion features={domAnimation}>
            <AnimatePresence>
                {flash.success !== null && (
                    <m.div
                        className={`alert is-valid position-fixed top-0 start-0  m-4 z-3`}
                        role="alert"
                        initial={{ y: "-100%", opacity: 0 }}
                        animate={{ y: "0", opacity: 1 }}
                        exit={{ y: "-100%", opacity: 0 }}
                        transition={{ duration: 0.3, type: "tween" }}
                    >
                        <Mascot type="success">
                            <div className="d-flex justify-content-center align-items-center flex-column">
                                <span>{flash.success}</span>
                                {flash.warning !== null && (
                                    <span className="fst-italic warning">
                                        {flash.warning}
                                    </span>
                                )}
                            </div>
                        </Mascot>
                    </m.div>
                )}
            </AnimatePresence>
        </LazyMotion>
    )
}

export default Popup;