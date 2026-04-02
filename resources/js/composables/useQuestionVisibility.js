import { ref, computed } from "vue";

export function useQuestionVisibility() {
    const onboarderData = ref(null);
    const answers = ref({});
    const userAnswers = ref({});
    const addedConvivientes = ref([]);
    const currentSection = ref({});

    const normalizeCondition = (condition) => {
        if (!condition) return condition;

        if (typeof condition === "string") {
            try {
                condition = JSON.parse(condition);
            } catch (e) {
                console.warn("Error parsing condition JSON:", e);
                return condition;
            }
        }

        if (!condition.personType) {
            if (currentSection.value.type === "conviviente") {
                condition.personType = "conviviente";
                condition.personIndex = currentSection.value.convivienteIndex;
            } else {
                condition.personType = "solicitante";
                condition.personIndex = null;
            }
        }

        if (condition.dependsOnQuestionId && typeof condition.dependsOnQuestionId === 'object') {
            condition.dependsOnQuestionId = condition.dependsOnQuestionId.id;
        }

        return condition;
    };

    const isDependentQuestionVisible = (
        questionId,
        personType,
        personIndex,
    ) => {
        if (!onboarderData.value) {
            return false;
        }

        if (
            personType === "solicitante" ||
            personType === null ||
            personType === undefined
        ) {
            for (const section of onboarderData.value.sections || []) {
                for (const question of section.questions || []) {
                    if (question.question?.id === questionId) {
                        if (isQuestionHiddenForDependency(question)) {
                            return false;
                        }

                        if (!question.condition) {
                            return true;
                        }

                        const normalizedCondition = normalizeCondition(
                            question.condition,
                        );
                        const result = evaluateCondition(
                            normalizedCondition,
                            getAnswerByQuestionId,
                        );
                        return result;
                    }
                }
            }
        }

        if (personType === "conviviente" && personIndex !== null) {
            const conviviente = addedConvivientes.value[personIndex];
            if (conviviente?.type?.sections) {
                for (const section of conviviente.type.sections) {
                    for (const question of section.questions || []) {
                        if (question.question?.id === questionId) {
                            if (isQuestionHiddenForDependency(question)) {
                                return false;
                            }

                            if (!question.condition) {
                                return true;
                            }

                            const normalizedCondition = normalizeCondition(
                                question.condition,
                            );
                            const result = evaluateCondition(
                                normalizedCondition,
                                getAnswerByQuestionId,
                            );
                            return result;
                        }
                    }
                }
            }

            for (const section of onboarderData.value.sections || []) {
                for (const question of section.questions || []) {
                    if (question.question?.id === questionId) {                        
                        if (isQuestionHiddenForDependency(question)) {
                            return false;
                        }

                        if (!question.condition) {
                            return true;
                        }

                        const normalizedCondition = normalizeCondition(
                            question.condition,
                        );
                        
                        const result = evaluateCondition(
                            normalizedCondition,
                            getAnswerByQuestionId,
                        );
                        return result;
                    }
                }
            }
        }

        return false;
    };

    const shouldShowQuestion = (question) => {
        if (isQuestionHidden(question)) {
            return false;
        }

        if (
            currentSection.value.type === "conviviente" &&
            currentSection.value.convivienteIndex !== null
        ) {
            const isSkipped = isConvivienteSectionSkipped(
                currentSection.value.convivienteIndex,
                currentSection.value.sectionIndex,
            );
            if (isSkipped) {
                return false;
            }
        }

        if (!question.condition) {
            return true;
        }

        const normalizedCondition = normalizeCondition(question.condition);

        if (normalizedCondition.dependsOnQuestionId) {
            const dependentQuestionVisible = isDependentQuestionVisible(
                normalizedCondition.dependsOnQuestionId,
                normalizedCondition.personType,
                normalizedCondition.personIndex,
            );
            if (!dependentQuestionVisible) {
                return false;
            }
        }

        const result = evaluateCondition(
            normalizedCondition,
            getAnswerByQuestionId,
        );
        return result;
    };

    const isQuestionHidden = (question) => {
        if (currentSection.value.type !== "solicitante") {
            return false;
        }

        const isFromBankflip = checkIfUserIsFromBankflip();

        if (
            question.show_if_bankflip_filled === 1 ||
            question.show_if_bankflip_filled === true
        ) {
            if (!isFromBankflip) {
                return true;
            }
        } else if (
            question.show_if_bankflip_filled === 0 ||
            question.show_if_bankflip_filled === false
        ) {
            if (isFromBankflip) {
                return true;
            }
        }

        if (Boolean(question.hide_if_bankflip_filled) && isFromBankflip) {
            return true;
        }

        return false;
    };

    const isQuestionHiddenForDependency = (question) => {
        if (currentSection.value.type !== "solicitante") {
            return false;
        }

        const isFromBankflip = checkIfUserIsFromBankflip();
        if (Boolean(question.hide_if_bankflip_filled) && isFromBankflip) {
            return true;
        }

        return false;
    };

    const isConvivienteSectionSkipped = (convivienteIndex, sectionIndex) => {
        const skipKey = `conviviente_${convivienteIndex}_section_skipped_${sectionIndex}`;
        return answers.value[skipKey] === true;
    };

    const checkIfUserIsFromBankflip = () => {
        if (!userAnswers.value) {
            return false;
        }

        for (const questionId in userAnswers.value) {
            const answer = userAnswers.value[questionId];
            if (answer && answer.question_slug === "fecha_collector") {
                return true;
            }
        }

        return false;
    };

    const getAnswerByQuestionId = (questionId) => {
        if (currentSection.value.type === "solicitante") {
            const solicitanteKey = `solicitante_${questionId}`;
            if (answers.value[solicitanteKey] !== undefined) {
                return answers.value[solicitanteKey];
            }

            if (userAnswers.value[questionId]) {
                const userAnswer = userAnswers.value[questionId];
                let answerValue =
                    userAnswer.formatted_answer || userAnswer.answer;

                if (userAnswer.question_slug === "genero") {
                    if (answerValue === "M") answerValue = "Mujer";
                    else if (answerValue === "H") answerValue = "Hombre";
                }

                if (userAnswer.question_slug === "estado_civil") {
                    if (answerValue === "Soltero") answerValue = "Soltero/a";
                    else if (answerValue === "Casado") answerValue = "Casado/a";
                    else if (answerValue === "Viudo") answerValue = "Viudo/a";
                    else if (answerValue === "Divorciado")
                        answerValue = "Divorciado/a";
                }

                return answerValue;
            }

            return null;
        } else if (currentSection.value.type === "conviviente") {
            const convivienteIndex = currentSection.value.convivienteIndex;
            if (convivienteIndex !== null) {
                const convivienteKey = `conviviente_${convivienteIndex}_${questionId}`;
                if (answers.value[convivienteKey] !== undefined) {
                    return answers.value[convivienteKey];
                }
            }
            return null;
        }

        const solicitanteKey = `solicitante_${questionId}`;
        if (answers.value[solicitanteKey] !== undefined) {
            return answers.value[solicitanteKey];
        }

        if (addedConvivientes.value) {
            for (let i = 0; i < addedConvivientes.value.length; i++) {
                const convivienteKey = `conviviente_${i}_${questionId}`;
                if (answers.value[convivienteKey] !== undefined) {
                    return answers.value[convivienteKey];
                }
            }
        }

        return null;
    };

    const evaluateCondition = (condition, getAnswerFunction) => {
        if (!condition || !condition.dependsOnQuestionId) {
            return true;
        }

        condition = normalizeCondition(condition);

        if (condition.dependsOnQuestionId === "bankflip") {
            const isFromBankflip = checkIfUserIsFromBankflip();

            if (condition.conditionType === "bankflip_filled") {
                return isFromBankflip;
            } else if (condition.conditionType === "bankflip_not_filled") {
                return !isFromBankflip;
            }
            return true;
        }

        let dependentAnswer;
        if (
            condition.personType &&
            condition.personType === "conviviente" &&
            condition.personIndex !== null
        ) {
            let convivienteIndex = condition.personIndex;
            if (
                currentSection.value.type === "conviviente" &&
                currentSection.value.convivienteIndex !== null
            ) {
                convivienteIndex = currentSection.value.convivienteIndex;
            }

            const key = `conviviente_${convivienteIndex}_${condition.dependsOnQuestionId}`;
            dependentAnswer = answers.value[key];
        } else if (condition.personType === "solicitante") {
            const key = `solicitante_${condition.dependsOnQuestionId}`;
            dependentAnswer = answers.value[key];
        } else {
            dependentAnswer = getAnswerFunction(condition.dependsOnQuestionId);
        }

        if (
            condition.conditionType === "is_null" ||
            condition.conditionType === "is_not_null"
        ) {
            const isNull =
                dependentAnswer === undefined ||
                dependentAnswer === null ||
                dependentAnswer === "";
            const result =
                condition.conditionType === "is_null" ? isNull : !isNull;
            return result;
        }

        if (dependentAnswer === undefined || dependentAnswer === null) {
            return false;
        }

        switch (condition.conditionType) {
            case "equals":
                if (
                    typeof dependentAnswer === "string" &&
                    dependentAnswer.startsWith("[") &&
                    dependentAnswer.endsWith("]")
                ) {
                    try {
                        const parsedAnswer = JSON.parse(dependentAnswer);
                        if (Array.isArray(parsedAnswer)) {
                            return parsedAnswer.includes(
                                condition.expectedValue,
                            );
                        }
                    } catch (e) {
                        console.error("Error parsing condition JSON:", e);
                    }
                }
                return dependentAnswer == condition.expectedValue;
            case "not_equals":
                if (
                    typeof dependentAnswer === "string" &&
                    dependentAnswer.startsWith("[") &&
                    dependentAnswer.endsWith("]")
                ) {
                    try {
                        const parsedAnswer = JSON.parse(dependentAnswer);
                        if (Array.isArray(parsedAnswer)) {
                            return !parsedAnswer.includes(
                                condition.expectedValue,
                            );
                        }
                    } catch (e) {
                        console.error("Error parsing condition JSON:", e);
                    }
                }
                return dependentAnswer != condition.expectedValue;
            case "is_true":
                return dependentAnswer === true;
            case "is_false":
                return dependentAnswer === false;
            case "is_checked":
                return dependentAnswer === true;
            case "is_not_checked":
                return dependentAnswer === false;
            case "contains":
                return String(dependentAnswer)
                    .toLowerCase()
                    .includes(String(condition.expectedValue).toLowerCase());
            case "not_contains":
                return !String(dependentAnswer)
                    .toLowerCase()
                    .includes(String(condition.expectedValue).toLowerCase());
            case "greater_than":
                if (
                    isDateValue(dependentAnswer) &&
                    isDateValue(condition.expectedValue)
                ) {
                    return (
                        new Date(dependentAnswer) >
                        new Date(condition.expectedValue)
                    );
                }
                return (
                    Number(dependentAnswer) > Number(condition.expectedValue)
                );
            case "less_than":
                if (
                    isDateValue(dependentAnswer) &&
                    isDateValue(condition.expectedValue)
                ) {
                    return (
                        new Date(dependentAnswer) <
                        new Date(condition.expectedValue)
                    );
                }
                return (
                    Number(dependentAnswer) < Number(condition.expectedValue)
                );
            case "greater_equal":
                if (
                    isDateValue(dependentAnswer) &&
                    isDateValue(condition.expectedValue)
                ) {
                    return (
                        new Date(dependentAnswer) >=
                        new Date(condition.expectedValue)
                    );
                }
                return (
                    Number(dependentAnswer) >= Number(condition.expectedValue)
                );
            case "less_equal":
                if (
                    isDateValue(dependentAnswer) &&
                    isDateValue(condition.expectedValue)
                ) {
                    return (
                        new Date(dependentAnswer) <=
                        new Date(condition.expectedValue)
                    );
                }
                return (
                    Number(dependentAnswer) <= Number(condition.expectedValue)
                );
            case "age_less_than":
                const age1 = calculateAge(dependentAnswer);
                return age1 !== null && age1 < Number(condition.expectedValue);
            case "age_greater_than":
                const age2 = calculateAge(dependentAnswer);
                return age2 !== null && age2 > Number(condition.expectedValue);
            case "age_between":
                const age3 = calculateAge(dependentAnswer);
                return (
                    age3 !== null &&
                    age3 >= Number(condition.expectedValue) &&
                    age3 <= Number(condition.expectedValue2)
                );
            default:
                return true;
        }
    };

    const isDateValue = (value) => {
        if (!value) return false;
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        const dateTimeRegex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
        if (dateRegex.test(value) || dateTimeRegex.test(value)) {
            const date = new Date(value);
            return !isNaN(date.getTime());
        }
        if (value instanceof Date) {
            return !isNaN(value.getTime());
        }

        return false;
    };

    const calculateAge = (birthDate) => {
        if (!birthDate) return null;
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (
            monthDiff < 0 ||
            (monthDiff === 0 && today.getDate() < birth.getDate())
        ) {
            age--;
        }
        return age;
    };

    const getVisibleAnswers = () => {
        const visibleAnswers = {};

        if (onboarderData.value?.sections) {
            for (const section of onboarderData.value.sections) {
                if (section.questions) {
                    for (const question of section.questions) {
                        if (shouldShowQuestion(question)) {
                            const questionId = question.question?.id;
                            if (questionId) {
                                const key = `solicitante_${questionId}`;
                                if (answers.value[key] !== undefined) {
                                    visibleAnswers[key] = answers.value[key];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (addedConvivientes.value) {
            addedConvivientes.value.forEach((conviviente, convivienteIndex) => {
                if (conviviente?.type?.sections) {
                    for (const section of conviviente.type.sections) {
                        if (section.questions) {
                            for (const question of section.questions) {
                                const shouldShow = shouldShowQuestionForConviviente(question, convivienteIndex);
                                if (shouldShow) {
                                    const questionId = question.question?.id;
                                    if (questionId) {
                                        const key = `conviviente_${convivienteIndex}_${questionId}`;
                                        if (answers.value[key] !== undefined) {
                                            visibleAnswers[key] = answers.value[key];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
        }

        return visibleAnswers;
    };

    const shouldShowQuestionForConviviente = (question, convivienteIndex) => {
        if (isQuestionHiddenForDependency(question)) {
            return false;
        }

        if (!question.condition) {
            return true;
        }

        const normalizedCondition = normalizeCondition(question.condition);
        
        if (normalizedCondition.dependsOnQuestionId) {
            const dependentQuestionVisible = isDependentQuestionVisible(
                normalizedCondition.dependsOnQuestionId,
                normalizedCondition.personType,
                normalizedCondition.personIndex
            );
            if (!dependentQuestionVisible) {
                return false;
            }
        }

        const getAnswerFunction = (questionId) => {
            const key = `conviviente_${convivienteIndex}_${questionId}`;
            return answers.value[key];
        };

        return evaluateCondition(normalizedCondition, getAnswerFunction);
    };

    return {
        onboarderData,
        answers,
        userAnswers,
        addedConvivientes,
        currentSection,

        shouldShowQuestion,
        isDependentQuestionVisible,
        isQuestionHidden,
        isQuestionHiddenForDependency,
        isConvivienteSectionSkipped,

        normalizeCondition,
        evaluateCondition,
        getAnswerByQuestionId,
        checkIfUserIsFromBankflip,
        isDateValue,
        calculateAge,

        getVisibleAnswers,
        shouldShowQuestionForConviviente,
    };
}
