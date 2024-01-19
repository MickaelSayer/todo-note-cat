import React from 'react';
import Mascot from '../Mascot';
import { Link } from 'react-router-dom';

const PageError404 = () => {
    return (
        <>
            <h1 className="display-3 text-center my-5">Cette page n'existe pas</h1>

            <Mascot type='lost'>
                Je n'ai pas trouvé la page que tu demande. <br /> Essaye de caresser cette belle sirène pour voir
            </Mascot>

            <ButtonRedirect />
        </>
    )
}

const ButtonRedirect = () => {
    return (
        <div className="d-flex justify-content-center mt-5">
            <Link
                className="btn-lg gaps kitty-sirene text-decoration-none me-2 py-2"
                title="Héhé, aidons-le à retrouver son chemin, docteur Watson."
                to='/login'
            >
                <div className='wrapper'>
                    <img
                        src={require("../../../../images/kitty_sirene.png")}
                        alt="Un chat siréne"
                        height="45"
                        width="45"
                    />
                </div>
                <span className="ms-3 text-center"> YouOU, par ici &#x1F618;</span>
            </Link>
        </div>
    );
};

export default PageError404;