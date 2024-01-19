import React, { useContext } from "react";
import { useFieldArray, useFormContext } from "react-hook-form";
import ContainerErrorFormField from "../../components/form/ContainerErrorFormField";
import Label from "../../components/form/Label";
import InputText from "../../components/form/InputText";
import SubmitButton from "../../components/form/SubmitButton";
import { requestNoteContext } from "../../context/NoteContext";
import Form from "../../components/form/Form";

const NoteForm = ({ setDisplayForm, data = [] }) => {
    const { getActionFunction } = useContext(requestNoteContext);
    const IS_UPDATE = Object.keys(data).length !== 0;

    const DEFAULT_VALUE = {
        defaultValues: {
            title: data?.title ? data?.title : "",
            tasks: !IS_UPDATE
                ? []
                : data.tasks.map((task) => ({
                      desc: task.description,
                      checked: task.checked ?? false,
                  })),
        },
    };

    const onSubmit = (formData) => {
        const datasForm = {
            title: formData.title,
            tasks: formData.tasks,
        };
        if (IS_UPDATE) {
            getActionFunction.updateNote(data.id, datasForm, setDisplayForm);
        } else {
            getActionFunction.createNote(datasForm, setDisplayForm);
        }
    };

    return (
        <Form on_submit={onSubmit} default_value={DEFAULT_VALUE}>
            <InputTitle />
            <InputsTasks />
            <SubmitButton
                className="kitty-save"
                btn_title="Miiiiiam, tu va finir ta note ?"
                optionsButton={{
                    path: require("../../../../images/kitty_save.png"),
                    description: "Un chat qui mange",
                    size: 40,
                    label: "Sauvegarder",
                }}
            />
        </Form>
    );
};

const InputTitle = () => {
    const NAME = "title";
    const MAX_CHARACTERS = 100;

    return (
        <>
            <Label name={NAME} content="Mon titre"></Label>
            <InputText
                name={NAME}
                description="Un jolie titre pour ta note"
                options_validation={{
                    required: "Ta note a besoin d'un titre.",
                    maxLength: {
                        value: MAX_CHARACTERS,
                        message: `Le titre est trop long.`,
                    },
                }}
            />
            <div className="d-flex justify-content-between w-97">
                <ContainerErrorFormField name={NAME} />
                <AddCountCharactersField
                    name={NAME}
                    max_characters={MAX_CHARACTERS}
                />
            </div>
        </>
    );
};

const InputsTasks = () => {
    const { fields, append, remove } = useFieldArray({
        name: "tasks",
    });

    const MAX_FIELDS = 50;
    const COND_MAX = fields.length >= MAX_FIELDS;
    const TEXT_BUTTON_ADD = fields.length === 0 ? 1 : fields.length;
    const IS_ACTIVE = fields.length >= MAX_FIELDS ? "active disabled" : "";

    return (
        <div className="mb-3">
            <InputFirstTask />
            {fields.map(
                (field, index) =>
                    index > 0 && (
                        <InputTask
                            key={field.id}
                            index={index}
                            field={field}
                            remove={remove}
                        />
                    )
            )}
            <button
                type="button"
                className={`btn-md d-block m-auto w-100 mt-4 d-flex justify-content-center align-items-center ${IS_ACTIVE}`}
                onClick={() => append({ desc: "" })}
                title="Cling, cling, tu veux cuisinier une tâche supplémentaire ?"
                disabled={COND_MAX}
            >
                <img
                    src={require("../../../../images/kitty_add.png")}
                    alt="Un chat qui cuisine"
                    height="45"
                    width="45"
                />
                <span className="ms-1">
                    &#x1F373; {TEXT_BUTTON_ADD}/{MAX_FIELDS}
                </span>
            </button>
        </div>
    );
};

const InputFirstTask = () => {
    const NAME = "tasks.0.desc";
    const MAX_CHARACTERS = 100;

    return (
        <div className="mt-4">
            <div className="d-flex flex-column">
                <Label name={NAME} content="Mes tâches"></Label>
                <span className="fst-italic info-task">
                    Si tu ajoutes des tâches, le champ est obligatoire.
                </span>
            </div>
            <div className="d-flex flex-row align-items-center">
                <InputText
                    name={NAME}
                    description="La description de ta première tâche."
                    options_validation={{
                        required:
                            "La description d'une tâche est obligatoire, tu sais ?",
                        maxLength: {
                            value: MAX_CHARACTERS,
                            message: `La description est trop longue.`,
                        },
                    }}
                />
            </div>
            <div className="d-flex justify-content-between w-97">
                <ContainerErrorFormField name={NAME} is_list={true} />
                <AddCountCharactersField
                    name={NAME}
                    max_characters={MAX_CHARACTERS}
                />
            </div>
        </div>
    );
};

const InputTask = ({ index, remove }) => {
    const NAME = `tasks.${index}.desc`;
    const MAX_CHARACTERS = 100;

    return (
        <>
            <div className="d-flex flex-row align-items-center">
                <button
                    type="button"
                    className="btn-md me-2"
                    onClick={() => remove(index)}
                    title="Mouahaha, tu veux que je m'occupe de faire disparaître cette tâche ?"
                >
                    <img
                        src={require("../../../../images/kitty_delete.png")}
                        alt="Un chat qui efface"
                        height="30"
                        width="30"
                    />
                </button>
                <InputText
                    name={NAME}
                    description="Encore... une description"
                    options_validation={{
                        required: "Une tâche sans description ?",
                        maxLength: {
                            value: MAX_CHARACTERS,
                            message: `La description est trop longue.`,
                        },
                    }}
                />
            </div>
            <div className="d-flex justify-content-between w-97">
                <ContainerErrorFormField name={NAME} is_list={true} />
                <AddCountCharactersField
                    name={NAME}
                    max_characters={MAX_CHARACTERS}
                />
            </div>
        </>
    );
};

const AddCountCharactersField = ({ name, max_characters }) => {
    const { watch } = useFormContext();
    const COUNT_TITLE_REGISTERED =
        watch(name) !== undefined ? watch(name).length : 0;

    return (
        <span
            className={`ms-1 fst-italic field-count-characters ${
                COUNT_TITLE_REGISTERED >= max_characters ? "count-error" : ""
            }`}
        >
            {COUNT_TITLE_REGISTERED}/{max_characters} lettres
        </span>
    );
};

export default NoteForm;
