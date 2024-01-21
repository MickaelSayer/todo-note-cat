import { useContext, useState } from "react"
import axios from "axios";
import { useNavigate } from 'react-router-dom';
import { flashMessageContext } from "../context/FlashMessageContext";
import { securityTokenContext } from "../context/security/securityTokenContext";

const RequestSecurity = () => {
    const [loading, setLoading] = useState(false);
    const [loadingAuth, setLoadingAuth] = useState(true);

    const { getFlashFunction } = useContext(flashMessageContext);
    const { getTokenFunction } = useContext(securityTokenContext);

    const navigate = useNavigate();

    const _checkFormLogin = async (dataForm) => {
        try {
            setLoading(true);
            setLoadingAuth(true)
            const responseAxios = await axios({
                method: 'post',
                url: '/api/user/login/validation',
                data: dataForm
            });

            const IS_VALIDATE = responseAxios?.status === 204;
            if (IS_VALIDATE) {
                const is_auth = await axios({
                    method: 'post',
                    url: '/api/user/login',
                    data: {
                        username: dataForm['email'],
                        password: dataForm['password']
                    }
                })

                const TOKEN = is_auth?.data?.token;
                getTokenFunction.setTokenLocalStorage('token_at', TOKEN);
                getFlashFunction.deleteAllFlashErrorsMessage();

                navigate('/');
            }
        } catch (error) {
            getFlashFunction.addErrors(error);
        } finally {
            setLoading(false);
            setLoadingAuth(false)
        }
    };

    const _signUp = async (dataForm) => {
        try {
            setLoading(true);
            const responseAxios = await axios({
                method: 'post',
                url: '/api/user/signUp',
                data: dataForm
            });

            navigate('/login');

            getFlashFunction.addSuccess(responseAxios?.data?.success);
            getFlashFunction.addOpenPopupSuccess();
            getFlashFunction.deleteAllFlashErrorsMessage();
        } catch (error) {
            getFlashFunction.addErrors(error);
        } finally {
            setLoading(false);
        }
    };

    const _forgotPassword = async (dataForm, setIsForgotPassword) => {
        try {
            setLoading(true);
            const responseAxios = await axios({
                method: 'post',
                url: '/api/user/forgotPassword/email',
                data: dataForm
            });

            const TOKEN = responseAxios?.data?.token;
            getTokenFunction.setTokenLocalStorage('token_fp', TOKEN);

            getFlashFunction.deleteAllFlashErrorsMessage();
            getFlashFunction.addSuccess(responseAxios?.data?.success, setIsForgotPassword);
            getFlashFunction.addOpenPopupSuccess();
        } catch (error) {
            getFlashFunction.addErrors(error);
        } finally {
            setLoading(false);
        }
    };

    const _updateForgotPassword = async (dataForm) => {
        const TOKEN_FORGOT_PASSWORD = getTokenFunction.getTokenLocalStorage('token_fp');

        try {
            setLoading(true);
            const responseAxios = await axios({
                method: 'post',
                url: '/api/user/forgotPassword/passwordToken',
                data: dataForm,
                headers: {
                    Authorization: `Bearer ${TOKEN_FORGOT_PASSWORD}`,
                },
            });

            navigate('/login');
            getFlashFunction.addSuccess(responseAxios?.data?.success);
            getFlashFunction.addOpenPopupSuccess();
            getFlashFunction.deleteAllFlashErrorsMessage();
            getTokenFunction.removeTokenLocalStorage('token_fp');
        } catch (error) {
            getFlashFunction.addErrors(error);
        } finally {
            setLoading(false);
        }
    };

    const _checkAccessRoute = async (type_token) => {
        const COOKIE_TOKEN = getTokenFunction.getTokenLocalStorage(type_token);

        try {
            await axios({
                method: 'GET',
                url: '/api/user/security/check',
                headers: {
                    Authorization: `Bearer ${COOKIE_TOKEN}`,
                    Type_token: type_token
                },
            });
        } catch (error) {
            if (type_token === 'token_at') {
                getTokenFunction.removeTokenLocalStorage(type_token);
            }
            navigate('/login');
        } finally {
            setLoadingAuth(false)
        }
    };

    const getSecurityFunction = {
        checkFormLogin: _checkFormLogin,
        signUp: _signUp,
        forgotPassword: _forgotPassword,
        updateForgotPassword: _updateForgotPassword,
        checkAccessRoute: _checkAccessRoute,
    };

    return {
        loading,
        loadingAuth,
        getSecurityFunction
    }
}

export default RequestSecurity;