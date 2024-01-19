import React, { forwardRef } from "react";

const Mascot = forwardRef(({ type = 'admin', children }, ref) => {
    const TYPE_MASCOT = `kitty_${type}.png`;

    return (
        <div
            ref={ref}
            className="container-connexion-info d-flex justify-content-center flex-sm-row flex-column align-items-center alert is-invalid pt-0"
            role="alert"
        >
            <img
                src={require(`../../../images/${TYPE_MASCOT}`)}
                alt="Un chat"
                height="64"
                width="64"
                className="img-admin me-2"
            />
            <div className="info-bubble text-center mt-2 px-4 py-2 rounded-3 position-relative">
                {children}
            </div>
        </div>
    )
})

export default Mascot;