import { useContext, useEffect, useState } from "react"
import axios from "axios";
import { flashMessageContext } from "../context/FlashMessageContext";
import { securityTokenContext } from "../context/security/securityTokenContext";
import { useNavigate } from "react-router-dom";

const RequestNote = () => {
    const [datas, setDatas] = useState([]);
    const [loading, setLoading] = useState(true);

    const { getTokenFunction } = useContext(securityTokenContext);
    const { getFlashFunction } = useContext(flashMessageContext);

    const navigate = useNavigate();

    /**
     * Manage the recovery of all notes
     */
    const _fetchNotes = async () => {
        const TOKEN_AUTH = getTokenFunction.getTokenLocalStorage('token_at');

        try {
            const responseAxios = await axios({
                method: 'GET',
                url: '/api/notes',
                headers: {
                    Authorization: `Bearer ${TOKEN_AUTH}`,
                },
            });
            const RESPONSE_DATAS = responseAxios?.data?.datas;
            setDatas(RESPONSE_DATAS);

            getFlashFunction.deleteAllFlashErrorsMessage();
        } catch (error) {
            _actionDenied(error);
            getFlashFunction.addErrors(error, null, 'no-content');
        } finally {
            setLoading(false);
        }
    };

    /**
     * Create note
     * 
     * @param {object} datas Datas from the note creation form
     * @param {function} handleDisplayForm Function that allows you to change the display of a component
     */
    const _createNote = async (datas, handleDisplayForm) => {
        const TOKEN_AUTH = getTokenFunction.getTokenLocalStorage('token_at');

        try {
            const responseAxios = await axios({
                method: 'POST',
                url: '/api/notes',
                data: datas,
                headers: {
                    Authorization: `Bearer ${TOKEN_AUTH}`,
                },
            });
            const RESPONSE_DATA = responseAxios?.data?.datas;

            setDatas(prevDatas => [RESPONSE_DATA, ...prevDatas]);

            getFlashFunction.deleteAllFlashErrorsMessage();
            getFlashFunction.addSuccess(responseAxios?.data?.success, handleDisplayForm);
            getFlashFunction.addWarning(responseAxios?.data?.warning);
            getFlashFunction.addOpenPopupSuccess();
        } catch (error) {
            _actionDenied(error);
            getFlashFunction.addErrors(error, handleDisplayForm);
        }
    }

    /**
     * Deletion of a note
     * 
     * @param {number} note_id Note identifier to delete
     */
    const _deleteNote = async (note_id) => {
        const TOKEN = getTokenFunction.getTokenLocalStorage('token_at');

        try {
            await axios({
                method: 'DELETE',
                url: `/api/notes/${note_id}`,
                headers: {
                    Authorization: `Bearer ${TOKEN}`,
                },
            });

            setDatas(prevDatas => prevDatas.filter(data => data.id !== note_id));

            getFlashFunction.deleteAllFlashErrorsMessage();
            getFlashFunction.addSuccess("J'ai supprimé ta tâche. J'espère qu'elle vivra des jours heureux où qu'elle soit.");
            getFlashFunction.addOpenPopupSuccess();
        } catch (error) {
            _actionDenied(error);
            getFlashFunction.addErrors(error);
        }
    }

    /**
     * Modification of a note
     * 
     * @param {int} note_id Note identifier to modify
     * @param {object} datas Data from the note creation form
     * @param {function} handleDisplayForm Function that allows you to change the display of a component
     */
    const _updateNote = async (note_id, datasForm, handleDisplayForm) => {
        const TOKEN_AUTH = getTokenFunction.getTokenLocalStorage('token_at');
        try {
            const responseAxios = await axios({
                method: 'PATCH',
                url: `/api/notes/${note_id}`,
                data: datasForm,
                headers: {
                    Authorization: `Bearer ${TOKEN_AUTH}`,
                },
            });
            const RESPONSE_DATAS = responseAxios?.data?.datas;
            setDatas(prevDatas => prevDatas.map(data => {
                if (data.id === RESPONSE_DATAS.id) {
                    return {
                        ...data,
                        title: RESPONSE_DATAS.title,
                        tasks: RESPONSE_DATAS.tasks
                    };
                }

                return data;
            }));

            getFlashFunction.deleteAllFlashErrorsMessage();
            getFlashFunction.addSuccess(responseAxios?.data?.success, handleDisplayForm);
            getFlashFunction.addWarning(responseAxios?.data?.warning);
            getFlashFunction.addOpenPopupSuccess();
        } catch (error) {
            _actionDenied(error);
            getFlashFunction.addErrors(error, handleDisplayForm);
        }
    }

    /**
     * Manage the modification of the CHECKED value of a task
     */
    const _updateTaskChecked = async (task_id) => {
        const TOKEN_AUTH = getTokenFunction.getTokenLocalStorage('token_at');

        try {
            const responseAxios = await axios({
                method: 'PATCH',
                url: `/api/note/task/checked/${task_id}`,
                headers: {
                    Authorization: `Bearer ${TOKEN_AUTH}`,
                },
            });
            const RESPONSE_DATA = responseAxios.data.datas;
            setDatas(prevDatas => prevDatas.map(data => ({
                ...data,
                tasks: data.tasks.map(task =>
                    task.id === RESPONSE_DATA.id ? { ...task, checked: !task.checked } : task
                )
            })));

            getFlashFunction.deleteAllFlashErrorsMessage();
            getFlashFunction.addSuccess(responseAxios?.data?.success);
            getFlashFunction.addOpenPopupSuccess();
        } catch (error) {
            _actionDenied(error);
            getFlashFunction.addErrors(error);
        }
    };

    const _actionDenied = (error) => {
        const ERROR = error?.response?.data;
        if (ERROR?.code === 401) {
            navigate('/login');
            getTokenFunction.removeTokenLocalStorage('token_at');
        }
    }

    /**
     * Brings together all the raw action of the tasks
     */
    const getActionFunction = {
        fetchNotes: _fetchNotes,
        updateTaskChecked: _updateTaskChecked,
        createNote: _createNote,
        deleteNote: _deleteNote,
        updateNote: _updateNote,
    };

    useEffect(() => {
        _fetchNotes();
    }, []);

    return {
        datas,
        setDatas,
        loading,
        getActionFunction
    }
}

export default RequestNote;