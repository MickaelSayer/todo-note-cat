import React from "react";

const Loading = ({ text, is_absolute = 'false' }) => {
    const middle = is_absolute === 'true' ? 'position-absolute top-50 start-50 translate-middle' : '';

    return (
        <div className={`loading d-flex align-items-center flex-column ${middle}`}>
            <img
                src={require('../../../images/kitty_loading.png')}
                alt="Un chat qui attend"
                height="64"
                width="64"
            />
            {text}...
            <div className="leap-frog">
                <div className="leap-frog__dot"></div>
                <div className="leap-frog__dot"></div>
                <div className="leap-frog__dot"></div>
            </div>
        </div>
    )
}

export default Loading;