import React from "react";

/**
 * @param {object} optionsButton {path, description, size, label}
 */
const SubmitButton = ({ className, btn_title, optionsButton }) => {
    return (
        <button
            type="submit"
            className={`btn-lg gaps mt-5 m-auto ${className}`}
            title={btn_title}
        >
            <div className="wrapper">
                <img
                    src={optionsButton.path}
                    alt={optionsButton.description}
                    height={optionsButton.size}
                    width={optionsButton.size}
                />
            </div>
            <span className="ms-2">{optionsButton.label}</span>
        </button>
    )
}

export default SubmitButton;