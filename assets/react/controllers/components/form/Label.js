import React from "react";

const Label = ({ name, content, required = true }) => {
    return (
        <label
            htmlFor={name}
            className="fw-bold form-label mb-0"
        >
            {content} {required && '*'}
        </label>
    )
}

export default Label;