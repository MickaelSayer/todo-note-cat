import React, { useContext, useEffect, useState } from "react";
import Notes from "../component/card";
import {
    RequestNoteProvider,
    requestNoteContext,
} from "../../context/NoteContext";
import Mascot from "../../components/Mascot";
import Modal from "../../components/Modal";
import NoteForm from "../component/form";
import CriticalError from "../../components/CriticalError";
import Popup from "../../components/Popup";
import Loading from "../../components/loading";
import Exception from "../../components/Exception";
import { flashMessageContext } from "../../context/FlashMessageContext";
import { securityContext } from "../../context/security/securityContext";

const SectionNotes = () => {
    const { getSecurityFunction, loadingAuth } = useContext(securityContext);

    useEffect(() => {
        getSecurityFunction.checkAccessRoute("token_at");
    }, []);

    return loadingAuth ? (
        <Loading text="Je vérifie si tu as l'accès" is_absolute="true" />
    ) : (
        <RequestNoteProvider>
            <NotesTable />
        </RequestNoteProvider>
    );
};

const NotesTable = () => {
    const { loading } = useContext(requestNoteContext);

    return (
        <>
            {loading ? (
                <Loading
                    text="Ne bouge pas, je cherche tes notes"
                    is_absolute="true"
                />
            ) : (
                <section id="todoNotes">
                    <h1 className="display-3 text-center my-5 fw-bold">
                        Mes super notes
                    </h1>
                    <AddContentSection />
                </section>
            )}
        </>
    );
};

const AddContentSection = () => {
    const { datas } = useContext(requestNoteContext);
    const { flash } = useContext(flashMessageContext);

    const IS_CRITICAL_ERROR = Object.keys(flash?.critical_error).length === 0;
    const IS_EXCEPTION = Object.keys(flash?.exception).length === 0;

    const IS_VISIBLE_EXCEPTION =
        IS_EXCEPTION ||
        (!IS_EXCEPTION && flash?.exception?.error_type !== "no-content");

    const IS_VISIBLE_ERROR =
        IS_CRITICAL_ERROR ||
        (!IS_CRITICAL_ERROR &&
            flash?.critical_error?.error_type !== "no-content");

    const IS_VISIBLE_CONTENT = IS_VISIBLE_EXCEPTION || !IS_VISIBLE_ERROR;

    return (
        <>
            <Popup />
            <>
                <CriticalError />
                <Exception />
                {IS_VISIBLE_CONTENT && (
                    <>
                        {datas.length === 0 && (
                            <Mascot type="no_datas">
                                <p className="mb-0">
                                    Tu n'as pas encore crée de note. <br />
                                    c'est trés... étrange.
                                </p>
                            </Mascot>
                        )}
                        <DisplayNoteCreationForm />
                        <div className="container-notes d-flex flex-column align-items-center mt-5">
                            {datas.map((note) => (
                                <Notes key={note.id} note={note} />
                            ))}
                        </div>
                    </>
                )}
            </>
        </>
    );
};

const DisplayNoteCreationForm = () => {
    const [isCreate, setIsCreate] = useState(false);
    const { datas } = useContext(requestNoteContext);

    const MAX_DATAS = 25;
    const COND_MAX = datas.length >= MAX_DATAS;
    const IS_ACTIVE = COND_MAX ? "active disabled" : "";

    const handleCreateForm = () => {
        setIsCreate(!isCreate);
    };

    return (
        <>
            <button
                type="button"
                title="Ha, tu as des choses à noter ?"
                onClick={handleCreateForm}
                disabled={COND_MAX}
                className={`gaps kitty-create m-auto btn-lg my-5 ${IS_ACTIVE} ${
                    isCreate ? "active" : ""
                }`}
            >
                <div className="wrapper">
                    <img
                        src={require("../../../../images/kitty_create.png")}
                        alt="Création d'une note"
                        height="40"
                        width="40"
                    />
                </div>
                <div className="ms-2 d-flex align-items-center justify-content-center flex-wrap">
                    <span>Créer une note </span>
                    <span>
                        &#x1F449; {datas.length}/{MAX_DATAS}
                    </span>
                </div>
            </button>

            {isCreate && (
                <Modal setExit={setIsCreate}>
                    <NoteForm setDisplayForm={setIsCreate} />
                </Modal>
            )}
        </>
    );
};

export default SectionNotes;
