import React, { useContext, useEffect, useState } from "react";
import {
    BrowserRouter as Router,
    Route,
    Routes,
    NavLink,
    useLocation,
} from "react-router-dom";
import SectionTodoNotes from "./note/section/note";
import PageError404 from "./components/page_errors/PageError404";
import Login from "./security/section/Login";
import SignUp from "./security/section/SignUp";
import ForgotPassword from "./security/section/ForgotPassword";
import { FlashMessageProvider } from "./context/FlashMessageContext";
import { SecurityProvider } from "./context/security/securityContext";
import {
    SecurityTokenProvider,
    securityTokenContext,
} from "./context/security/securityTokenContext";

const ReactRouter = () => {
    return (
        <Router>
            <SecurityTokenProvider>
                <FlashMessageProvider>
                    <SecurityProvider>
                        <Routing />
                    </SecurityProvider>
                </FlashMessageProvider>
            </SecurityTokenProvider>
        </Router>
    );
};

const Routing = () => {
    return (
        <>
            <div className="container-fluid d-flex justify-content-sm-between justify-content-center flex-wrap">
                <AddThemeColor />
                <nav className="d-flex justify-content-end align-items-center mt-4 mx-2">
                    <ButtonLogout />
                    <ButtonSignUp />
                    <ButtonReturnLogin />
                </nav>
            </div>

            <main className="my-5 container">
                <Routes>
                    <Route path="/" element={<SectionTodoNotes />} />

                    <Route path="/login" element={<Login />} />

                    <Route path="/signUp" element={<SignUp />} />

                    <Route
                        path="/forgotPassword"
                        element={<ForgotPassword />}
                    />

                    <Route path="/*" element={<PageError404 />} />
                </Routes>
            </main>
        </>
    );
};

const ButtonLogout = () => {
    const { getTokenFunction } = useContext(securityTokenContext);
    const LOCATION = useLocation();

    return (
        <>
            {LOCATION.pathname === "/" && (
                <NavLink
                    className="btn-lg gaps kitty-logout text-decoration-none me-2 py-2"
                    title="ZzZz, tu va dormir toi aussi ?"
                    onClick={() =>
                        getTokenFunction.removeTokenLocalStorage("token_at")
                    }
                    to="/login"
                >
                    <div className="wrapper">
                        <img
                            src={require("../../images/kitty_logout.png")}
                            alt="Un chat qui dort"
                            height="45"
                            width="45"
                        />
                    </div>
                    <span className="ms-2 text-center">Se déconnecter</span>
                </NavLink>
            )}
        </>
    );
};

const ButtonSignUp = () => {
    const LOCATION = useLocation();

    return (
        <>
            {LOCATION.pathname === "/login" && (
                <NavLink
                    className="btn-lg gaps kitty-signup text-decoration-none me-2 py-2"
                    title="Tu veux faire connaissance avec tous le monde ?"
                    to="/signUp"
                >
                    <div className="wrapper">
                        <img
                            src={require("../../images/kitty_signup.png")}
                            alt="Un chat qui fait la fête"
                            height="45"
                            width="45"
                        />
                    </div>
                    <span className="ms-2 text-center">Créer un compte</span>
                </NavLink>
            )}
        </>
    );
};

const ButtonReturnLogin = () => {
    const LOCATION = useLocation();

    return (
        <>
            {LOCATION.pathname === "/signUp" && (
                <NavLink
                    className="btn-lg gaps kitty-oops text-decoration-none me-2 py-2"
                    title="Apparemment, tu ne veux pas faire connaissance"
                    to="/login"
                >
                    <div className="wrapper">
                        <img
                            src={require("../../images/kitty_oops.png")}
                            alt="Un chat super héro"
                            height="45"
                            width="45"
                        />
                    </div>
                    <span className="ms-2">Me connecter</span>
                </NavLink>
            )}
        </>
    );
};

const AddThemeColor = () => {
    const { getTokenFunction } = useContext(securityTokenContext);
    const THEME_COLOR = getTokenFunction.getTokenLocalStorage('theme-color');

    const [color, setColor] = useState("theme-blue");

    const handleChangeColor = (e, theme_color) => {
        const newColorClass = `theme-${theme_color}`;

        getTokenFunction.setTokenLocalStorage('theme-color', newColorClass);

        document.querySelectorAll(".change-color span").forEach((element) => {
            element.classList.remove("active");
        });

        if (e.target.className.indexOf("active") === -1) {
            e.target.classList.add("active");
            setColor(newColorClass);
        } else {
            setColor("");
        }
    };

    useEffect(() => {
        const currentHtmlTheme = document.documentElement.className;
        document.documentElement.classList.remove(currentHtmlTheme);

        document.documentElement.classList.add(THEME_COLOR);
    }, [color]);

    const COLORS_THEME = ["blue", "red", "green", "yellow", "pink"];
    const TRAD_COLOR = ["bleu", "rouge", "vert", "jaune", "rose"];

    return (
        <div className="change-color d-flex justify-content-center mt-4 mx-2">
            {COLORS_THEME.map((color, index) => (
                <span
                    title={`Tu veux voir la vie en ${TRAD_COLOR[index]} ?`}
                    key={color}
                    className={`theme-${color} ${
                        THEME_COLOR === 'theme-' + color || THEME_COLOR === null && color === 'blue' ? "active" : ""
                    }`}
                    onClick={(e) => {
                        handleChangeColor(e, color);
                    }}
                ></span>
            ))}
        </div>
    );
};

export default ReactRouter;
