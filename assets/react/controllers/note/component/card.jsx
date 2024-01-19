import React, { useContext, useState } from "react";
import { requestNoteContext } from "../../context/NoteContext";
import Modal from "../../components/Modal";
import NoteForm from "../../note/component/form";

const Note = ({ note }) => {
    const [isUpdate, setIsUpdate] = useState(false);

    return (
        <>
            {isUpdate && (
                <Modal setExit={setIsUpdate}>
                    <NoteForm setDisplayForm={setIsUpdate} data={note} />
                </Modal>
            )}
            <CardNote
                note={note}
                setDisplayForm={setIsUpdate}
                isUpdate={isUpdate}
            />
        </>
    );
};

const CardNote = ({ note, setDisplayForm, isUpdate }) => {
    return (
        <div className="card card-note border-0 my-4 overflow-hidden">
            <HeaderNote note={note} />

            <ul className="list-group list-group-flush">
                {note.tasks.map((task, index) => (
                    <BodyTasks key={task.id} index={index} task={task} />
                ))}
            </ul>

            <ButtonActionNote
                setDisplayForm={setDisplayForm}
                noteId={note.id}
                isUpdate={isUpdate}
            />
        </div>
    );
};

const HeaderNote = ({ note }) => {
    /**
     * Management of the progress of the note
     */
    const COUNT_TASKS_CHECKED = note.tasks.filter(
        (task) => task.checked
    ).length;
    const PROGRESS_VALUE = Math.round(
        (100 / note.tasks.length) * COUNT_TASKS_CHECKED
    );

    const DATE_OBJECT = new Date(note.created_at);
    const FORMATTED_DATE = DATE_OBJECT.toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });

    return (
        <div className="card-body d-flex flex-column justify-content-center py-4">
            <NoteProgressBar progressValue={PROGRESS_VALUE} />
            <span className="text-break text-center fs-3">{note.title}</span>
            <span className="date-note text-center mt-2">{FORMATTED_DATE}</span>
        </div>
    );
};

const BodyTasks = ({ task, index }) => {
    const { getActionFunction } = useContext(requestNoteContext);

    const TASK_BG_COLOR = index % 2 === 0 ? "bg-ligth" : "";

    return (
        <li
            className={`${TASK_BG_COLOR} d-flex flex-row px-4 py-3 rounded-0 text-break`}
        >
            <input
                className="me-3 common-checkbox"
                type="checkbox"
                checked={task.checked}
                onChange={(e) =>
                    getActionFunction.updateTaskChecked(e.target.id)
                }
                id={task.id}
                name={`checkbox_${task.id}`}
                style={{ display: "none" }}
            />
            <label
                className="check"
                htmlFor={task.id}
                title={`${
                    task.checked
                        ? "Non, j'étais bien comme ça!"
                        : "Oui, coche moi!"
                }`}
            >
                <svg width="22px" height="22px" viewBox="0 0 18 18">
                    <path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z" />
                    <polyline points="1 9 7 14 15 4" />
                </svg>
            </label>

            {task.description}
        </li>
    );
};

const NoteProgressBar = ({ progressValue }) => {
    /**
     * Information on the color and text of the progression
     */
    const COLOR_BAR_PROGRESS = __getStyleProgress(progressValue);

    /**
     * Progress bar data
     */
    const CURRENT_VALUE = progressValue;
    const VALUE_MIN = 0;
    const VALUE_MAX = 100;
    const VALUE_PROGRESS = CURRENT_VALUE + "%";
    const STYLE_PROGRESS_BAR_LENGTH = {
        width: CURRENT_VALUE + "%",
        padding: "0 20px 0 40px",
    };

    return (
        <div
            className="progress"
            role="progressbar"
            aria-label="Task progression bar"
            aria-valuenow={CURRENT_VALUE}
            aria-valuemin={VALUE_MIN}
            aria-valuemax={VALUE_MAX}
        >
            <div
                className={`progress-bar d-flex align-items-end rounded-pill ${COLOR_BAR_PROGRESS}`}
                style={STYLE_PROGRESS_BAR_LENGTH}
                title="Quelle magnifique barre de progression !"
            >
                <span className="progress-value fw-bold">{VALUE_PROGRESS}</span>
            </div>
        </div>
    );
};

const ButtonActionNote = ({ setDisplayForm, noteId, isUpdate }) => {
    return (
        <div className="card-action d-flex justify-content-evenly align-items-center flex-wrap py-4 px-4">
            <ButtonUpdate setDisplayForm={setDisplayForm} isUpdate={isUpdate} />
            <ButtonDelete noteId={noteId} />
        </div>
    );
};

const ButtonUpdate = ({ setDisplayForm, isUpdate }) => {
    return (
        <button
            type="button"
            title="Hmm... tu veux que je pratique une chirurgie pour améliorer ta tâche ?"
            onClick={() => setDisplayForm(!isUpdate)}
            className={`btn-lg gaps kitty-edit m-auto mb-3 mb-sm-0 ${
                isUpdate ? "active" : ""
            }`}
        >
            <div className="wrapper">
                <img
                    src={require("../../../../images/kitty_edit.png")}
                    alt="Un chat docteur"
                    height="40"
                    width="40"
                />
            </div>
            <div className="ms-2 d-flex flex-column align-items-center justify-content-center">
                <span>Modifier</span>
            </div>
        </button>
    );
};

const ButtonDelete = ({ noteId }) => {
    const { getActionFunction } = useContext(requestNoteContext);

    return (
        <button
            type="button"
            title="Ha... toi aussi tu as besoin d'une bonne douche ?"
            onClick={() => getActionFunction.deleteNote(noteId)}
            className="gaps kitty-clean m-auto btn-lg mb-3 mb-sm-0"
        >
            <div className="wrapper">
                <img
                    src={require("../../../../images/kitty_clean.png")}
                    alt="Un chat qui prend une douche"
                    height="40"
                    width="40"
                />
            </div>
            <div className="ms-2 d-flex flex-column align-items-center justify-content-center">
                <span>Supprimer</span>
            </div>
        </button>
    );
};

const __getStyleProgress = (progressValue) => {
    let COLOR_BAR_PROGRESS = "lvl-0";

    switch (true) {
        case progressValue > 0 && progressValue <= 25:
            COLOR_BAR_PROGRESS = "lvl-25";
            break;
        case progressValue > 25 && progressValue <= 50:
            COLOR_BAR_PROGRESS = "lvl-50";
            break;
        case progressValue > 50 && progressValue < 100:
            COLOR_BAR_PROGRESS = "lvl-75";
            break;
        case progressValue === 100:
            COLOR_BAR_PROGRESS = "lvl-100";
            break;
    }

    return COLOR_BAR_PROGRESS;
};

export default Note;
