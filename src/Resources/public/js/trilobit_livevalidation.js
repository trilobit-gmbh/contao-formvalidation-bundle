var trilobit;
if (!trilobit) {
    trilobit = {};
}

trilobit.allCheckboxRadio = {};
trilobit.allFormFields = {};

/*
 * Liefert alle ID's der Formulare
 */
trilobit.getAllForms = function (trilobit_liveValidation) {
    let allForms, formCount, formElementCount, formElement, firstRun, id;

    allForms = [];

    for (formCount = 0; formCount < trilobit_liveValidation.length; formCount++) {
        firstRun = true;

        for (formElementCount = 0; formElementCount < trilobit_liveValidation[formCount].length; formElementCount++) {
            if (firstRun === false) {
                continue;
            }

            formElement = trilobit_liveValidation[formCount][formElementCount];

            // ist es eine Checkbox / Radio?
            if (typeof formElement.validations[1] !== "undefined"
                && typeof formElement.validations[1].validationType !== "undefined"
                && (formElement.validations[1].validationType === 'trilobitCheckboxValidation'
                    || formElement.validations[1].validationType === 'trilobitRadioValidation')
            ) {
                id = formElement.validations[1].validationAttributes.elements[0];

                if (document.getElementById('opt_' + formElement.key + '_' + id) !== null
                    && document.getElementById('opt_' + formElement.key + '_' + id).form !== null
                ) {
                    allForms.push(document.getElementById('opt_' + formElement.key + '_' + id).form);
                    firstRun = false;
                }
            } else {
                // Existiert das Feld?
                if (document.getElementById(formElement.key) !== null
                    && document.getElementById(formElement.key).form !== null
                ) {
                    allForms.push(document.getElementById(formElement.key).form);
                    firstRun = false;
                }
            }
        }
    }

    return allForms;
};


/*
 *  Generiert für jedes Feld aus JSON eine Validierung
 */
trilobit.configValidation = function () {
    let formCount, formElementCount, formElement;

    // Jedes Formularfeld
    for (formCount = 0; formCount < trilobit_liveValidation.length; formCount++) {
        formSubmitted = false;

        for (formElementCount = 0; formElementCount < trilobit_liveValidation[formCount].length; formElementCount++) {
            formElement = trilobit_liveValidation[formCount][formElementCount];

            if (typeof trilobit.allCheckboxRadio[formCount] === "undefined") {
                trilobit.allCheckboxRadio[formCount] = [];
            }

            if (typeof trilobit.allFormFields[formCount] === "undefined") {
                trilobit.allFormFields[formCount] = [];
            }

            if (typeof formElement.validations[1] !== "undefined"
                && typeof formElement.validations[1].validationType !== "undefined"
                && (formElement.validations[1].validationType === 'trilobitCheckboxValidation'
                    || formElement.validations[1].validationType === 'trilobitRadioValidation')
            ) {
                trilobit.checkboxRadioValidation(formCount, formElement);
                continue;
            }

            // Existiert das Feld?
            if (document.getElementById(formElement.key) == null) {
                continue;
            }

            // Hidden Feld?
            if (document.getElementById(formElement.key).type === 'hidden') {
                continue;
            }

            trilobit.setCheckRoutine(formCount, formElement);
        }

        if (typeof trilobitFormSubmitted !== "undefined"
            && trilobitFormSubmitted
        ) {
            LiveValidation.massValidate(trilobit.allFormFields[formCount]);
        }
    }

    trilobit.handleSubmitSequence(trilobit.getAllForms(trilobit_liveValidation));
};


/*
 *  Erzeugt das LiveValidation Objekt für das jeweilige Formularelement
 */
trilobit.setCheckRoutine = function (formCount, formElement) {
    var newElement, i, jsonSettings, objects, currentSetting;

    // LiveValidation Objekt für Feld anlegen
    newElement = new LiveValidation(
        formElement.key,
        {
            validMessage: (typeof formElement.validMessage != 'undefined' ? formElement.validMessage : ' '),
            onInvalid: trilobit.deleteServerErrorMessage()
        }
    );

    trilobit.allFormFields[formCount][trilobit.allFormFields[formCount].length] = newElement;

    trilobit.addOnFocus(formElement.key);

    // Lade Prüfroutinen für jedes Feld
    for (i = 0; i < 3; i++) {
        if (typeof formElement.validations[i] !== "undefined") {
            jsonSettings = {};

            for (currentSetting in formElement.validations[i]['validationAttributes']) {
                // String zu Regex-Pattern "konvertieren"
                if (currentSetting === 'pattern') {
                    jsonSettings[currentSetting] = eval(formElement.validations[i]['validationAttributes'][currentSetting]);
                } else {
                    jsonSettings[currentSetting] = formElement.validations[i]['validationAttributes'][currentSetting];
                }
            }

            // Übersetzung von String zu Variablen
            // um weiteres Eval zu vermeiden
            objects = {
                "Validate.Acceptance": Validate.Acceptance,
                "Validate.Format": Validate.Format,
                "Validate.Email": Validate.Email,
                "Validate.Presence": Validate.Presence,
                "Validate.Numericality": Validate.Numericality,
                "Validate.Exclusion": Validate.Exclusion,
                "Validate.Length": Validate.Length,
                "Validate.Confirmation": Validate.Confirmation
            };
            // Prüfroutinen zu LiveValidation Objekt hinzufügen

            newElement.add(
                objects[formElement.validations[i]['validationType']],
                jsonSettings
            );
        }
    }
};


/*
 * Nach Servercheck: Löscht ServercheckMessage und setzt LiveValidation ErrorMessage
 */
trilobit.deleteServerErrorMessage = function () {
    // "this" bezieht sich auf LiveValidation-Objekt
    return function () {
        var element = document.getElementById(this.element.id);

        if (trilobit.hasClass(element, 'error')) {
            var replace = new RegExp('(\\s|^)' + 'error' + '(\\s|$)');
            element.className = element.className.replace(replace, ' ');
        }

        var allNodes = element.parentNode.childNodes;
        var errorNode;

        for (var i = 0; i < allNodes.length; i++) {
            if (allNodes[i].nodeName.toLowerCase() === "p"
                && trilobit.hasClass(allNodes[i], 'error')
            ) {
                errorNode = allNodes[i];
            }
        }

        message = document.createElement('span');

        if (errorNode !== undefined) {
            if (errorNode.childNodes[0].textContent !== this.message) {
                message.appendChild(document.createTextNode(errorNode.childNodes[0].textContent));
                message.appendChild(document.createTextNode(' '));
            }
        }

        message.appendChild(document.createTextNode(this.message));

        if (errorNode) {
            element.parentNode.removeChild(errorNode);
        }

        this.insertMessage(message);
        //this.insertMessage(this.createMessageSpan());
        this.addFieldClass();
    };
};


/*
 * Löscht Focus-Handler
 */
trilobit.addOnFocus = function (elementId) {
    document.getElementById(elementId).onfocus = null;
};


/*
 * Überschreibt Submit-Handler
 * Führt Checkbox / Radio Submit und LiveValidation Submit zusammen
 */
trilobit.handleSubmitSequence = function (allForms) {
    var formCount;

    for (formCount = 0; formCount < allForms.length; formCount++) {
        function formClosure(formId, onsubmitForm) {
            var liveValidationOnSubmit, resultLiveValidation, resultTrilobitValidation, result;

            liveValidationOnSubmit = onsubmitForm.onsubmit;

            onsubmitForm.onsubmit = function (e) {
                if (liveValidationOnSubmit !== null) {
                    resultLiveValidation = liveValidationOnSubmit.call(this, e || window.event);
                    resultTrilobitValidation = trilobit.isCheckboxRadioValid(formId);

                    result = (resultLiveValidation && resultTrilobitValidation);
                } else {
                    result = trilobit.isCheckboxRadioValid(formId);
                }

                if (!result
                    && !trilobit.hasClass(onsubmitForm, "formSubmitted")
                ) {
                    onsubmitForm.className = onsubmitForm.className + " formSubmitted";
                }

                return result;
            };
        }

        formClosure(formCount, allForms[formCount]);

        allForms[formCount].setAttribute("novalidate", '');
    }
};


/*
 * Überprüft, ob für Objekt Klasse gesetzt ist
 */
trilobit.hasClass = function (objElement, nameOfClass) {
    return new RegExp('(\\s|^)' + nameOfClass + '(\\s|$)').test(objElement.className);
};


/*
 * Setzt click-Event auf Checkboxen / Radios
*/
trilobit.checkboxRadioValidation = function (formCount, checkboxRadioGroup) {
    var numberOfElements, i, id;

    trilobit.allCheckboxRadio[formCount][trilobit.allCheckboxRadio[formCount].length] = checkboxRadioGroup;

    numberOfElements = checkboxRadioGroup.validations[1].validationAttributes.elements.length;

    for (i = 0; i < numberOfElements; i++) {
        id = checkboxRadioGroup.validations[1].validationAttributes.elements[i];

        // ergänzende Kontrolle, ob es das Feld gibt
        if (typeof id !== "undefined"
            && document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id) !== null
        ) {
            // Setze Clickevent auf jede Checkbox
            document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id).onclick = function () {
                trilobit.countOfCheckedBoxes(checkboxRadioGroup);
            };
        }
    }
};


/*
 * Überprüft, ob alle Checkboxen / Radios valide sind
*/
trilobit.isCheckboxRadioValid = function (formCount) {
    let returnValueForm, i, checkboxRadioGroup, isMandatory, id;

    returnValueForm = true;

    for (i = 0; i < trilobit.allCheckboxRadio[formCount].length; i++) {
        isMandatory = false;

        checkboxRadioGroup = trilobit.allCheckboxRadio[formCount][i];

        id = checkboxRadioGroup.validations[1].validationAttributes.elements[0];

        // ergänzende Kontrolle, ob es das Feld gibt
        if (typeof id !== "undefined"
            && document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id) !== null
        ) {
            if (typeof checkboxRadioGroup.validations[1].validationAttributes.mandatory !== "undefined"
                && checkboxRadioGroup.validations[1].validationAttributes.mandatory === 1
            ) {
                isMandatory = true;
            }

            if (trilobit.countOfCheckedBoxes(checkboxRadioGroup) === 0
                && isMandatory
            ) {
                returnValueForm = false;
            }
        }
    }

    return returnValueForm;
};


/*
 * Liefert für die Gruppe die Anzahl der angeklickten Elemente zurück.
*/
trilobit.countOfCheckedBoxes = function (checkboxRadioGroup) {
    let numberOfElements, clickedElements, i, id, isMandatory;

    clickedElements = 0

    numberOfElements = checkboxRadioGroup.validations[1].validationAttributes.elements.length;
    // Überprüfe bei jedem Klick auf eine Checkbox, ob Checkboxen ausgewählt sind

    for (i = 0; i < numberOfElements; i++) {
        id = checkboxRadioGroup.validations[1].validationAttributes.elements[i];

        if (document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id) !== null
            && document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id).checked
        ) {
            clickedElements++;
        }
    }

    // Wenn Checkbox ausgewählt ist und Errormessage angezeigt wird
    if (clickedElements > 0) {
        trilobit.removeCheckboxRadioErrorMessage(checkboxRadioGroup);
    }

    isMandatory = typeof checkboxRadioGroup.validations[1].validationAttributes.mandatory !== "undefined"
        && checkboxRadioGroup.validations[1].validationAttributes.mandatory === 1;

    // keine Checkbox ausgewählt
    if (clickedElements < 1
        && isMandatory
    ) {
        trilobit.createCheckboxRadioErrorMessage(checkboxRadioGroup);
    }

    return clickedElements;
};


/*
 * Fals eine Fehlermeldung existiert, wird diese gelöscht
*/
trilobit.removeCheckboxRadioErrorMessage = function (checkboxRadioGroup) {
    let objSpan;

    if (document.getElementById('errormessage_' + checkboxRadioGroup.key) !== null) {
        objSpan = document.getElementById('errormessage_' + checkboxRadioGroup.key);
        objSpan.parentNode.removeChild(objSpan);
    }
};


/*
 * Legt eine span mit einer Fehlermeldung an
*/
trilobit.createCheckboxRadioErrorMessage = function (checkboxRadioGroup) {
    let errorMessage, id;

    if (document.getElementById('errormessage_' + checkboxRadioGroup.key) === null) {
        //Lege Errormessage an
        errorMessage = document.createElement("span");
        errorMessage.setAttribute('class', 'LV_validation_message LV_invalid');
        errorMessage.id = 'errormessage_' + checkboxRadioGroup.key;
        errorMessage.setAttribute('id', 'errormessage_' + checkboxRadioGroup.key);
        errorMessage.innerHTML = checkboxRadioGroup.validations[1].validationAttributes.failureMessage;

        id = checkboxRadioGroup.validations[1].validationAttributes.elements[0];

        /*
        var lastElement = document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id);
        lastElement.parentNode.parentNode.appendChild(errorMessage);
        */
        if (document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id) !== null) {
            let firstElement = document.getElementById('opt_' + checkboxRadioGroup.key + '_' + id);
            firstElement.parentNode.parentNode.insertBefore(errorMessage, firstElement.parentNode);
        }
    }
};


/*
 * setzt neues onload
*/
trilobit.addLoadEvent = function (newonload) {
    let oldonload = window.onload;

    if (typeof window.onload != 'function') {
        window.onload = newonload;
    } else {
        window.onload = function () {
            newonload();
            if (oldonload) {
                oldonload();
            }
        };
    }
};


// Ausführen, wenn DOM komplett geladen ist
trilobit.addLoadEvent(trilobit.configValidation);
