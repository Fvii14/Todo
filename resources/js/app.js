import { createApp } from 'vue'
import * as Sentry from '@sentry/vue'
import VueFlowLogicas from './components/VueFlowLogicas.vue'
import QuestionnaireLogic from './components/QuestionnaireLogic.vue'
import QuestionnaireLogicTab from './components/QuestionnaireLogicTab.vue'
import PlaygroundContainer from './components/PlaygroundContainer.vue'
import VersionesComponent from './components/VersionesComponent.vue'
import TesterPerfilComponent from './components/TesterPerfilComponent.vue'
import TesterUsuarioComponent from './components/TesterUsuarioComponent.vue'
import TesterCondicionesComponent from './components/TesterCondicionesComponent.vue'
import WizardAyuda from './components/WizardAyuda.vue'
import WizardCollector from './components/WizardCollector.vue'
import OnboarderApp from './components/OnboarderApp.vue'
import GestionAyudasComponent from './components/GestionAyudasComponent.vue'
import QuestionsAdminComponent from './components/QuestionsAdminComponent.vue'

function createAppWithSentry(component, props = {}) {
    const app = createApp(component, props)

    Sentry.init({
        app,
        dsn: 'https://2f48132a68c2cfd87adf82db830b83ff@o4510391109812224.ingest.de.sentry.io/4510391115448400',
        sendDefaultPii: true,
        enableLogs: true,
    })

    return app
}

if (document.getElementById('vueflow-app')) {
    createApp(VueFlowLogicas).mount('#vueflow-app')
}
if (document.getElementById('vueflow-condiciones-app')) {
    const container = document.getElementById('vueflow-condiciones-app')
    const ayudaId = container.dataset.ayudaId
    const csrf = container.dataset.csrf

    createApp(QuestionnaireLogicTab, {
        ayudaId: ayudaId,
        csrf: csrf,
    }).mount('#vueflow-condiciones-app')
}
if (document.getElementById('questionnaire-logic-app')) {
    const el = document.getElementById('questionnaire-logic-app')
    const questions = JSON.parse(el.dataset.questions || '[]')
    const conditions = JSON.parse(el.dataset.conditions || '[]')
    const questionnaireId = el.dataset.questionnaireId
    const ayudaId = el.dataset.ayudaId
    const csrf = el.dataset.csrf

    createApp(QuestionnaireLogic, {
        questions,
        conditions,
        questionnaireId,
        ayudaId,
        csrf,
    }).mount('#questionnaire-logic-app')
}

if (document.getElementById('playground-container')) {
    const container = document.getElementById('playground-container')
    const ayudaId = container.dataset.ayudaId
    const csrf = container.dataset.csrf

    createApp(PlaygroundContainer, {
        ayudaId: parseInt(ayudaId),
        csrf: csrf,
    }).mount('#playground-container')
}

if (document.getElementById('versiones-container')) {
    const container = document.getElementById('versiones-container')
    const ayudaId = container.dataset.ayudaId
    const questionnaireId = container.dataset.questionnaireId
    const csrf = container.dataset.csrf

    createApp(VersionesComponent, {
        ayudaId: parseInt(ayudaId),
        questionnaireId: questionnaireId ? parseInt(questionnaireId) : null,
        csrf: csrf,
    }).mount('#versiones-container')
}

if (document.getElementById('tester-perfil-container')) {
    const container = document.getElementById('tester-perfil-container')
    const ayudaId = container.dataset.ayudaId
    const csrf = container.dataset.csrf

    createApp(TesterPerfilComponent, {
        ayudaId: parseInt(ayudaId),
        csrf: csrf,
    }).mount('#tester-perfil-container')
}

if (document.getElementById('tester-usuario-container')) {
    const container = document.getElementById('tester-usuario-container')
    const ayudaId = container.dataset.ayudaId
    const csrf = container.dataset.csrf

    createApp(TesterUsuarioComponent, {
        ayudaId: parseInt(ayudaId),
        csrf: csrf,
    }).mount('#tester-usuario-container')
}

if (document.getElementById('tester-condiciones-container')) {
    const container = document.getElementById('tester-condiciones-container')
    const ayudaId = container.dataset.ayudaId
    const csrf = container.dataset.csrf

    createApp(TesterCondicionesComponent, {
        ayudaId: parseInt(ayudaId),
        csrf: csrf,
    }).mount('#tester-condiciones-container')
}

if (document.getElementById('wizard-container')) {
    const container = document.getElementById('wizard-container')
    const wizard = JSON.parse(container.dataset.wizard || '{}')
    const organos = JSON.parse(container.dataset.organos || '[]')
    const sectores = JSON.parse(container.dataset.sectores || '[]')
    const questionTypes = JSON.parse(container.dataset.questionTypes || '[]')
    const questionSectores = JSON.parse(container.dataset.questionSectores || '[]')
    const questionCategorias = JSON.parse(container.dataset.questionCategorias || '[]')
    const mailClasses = JSON.parse(container.dataset.mailClasses || '{}')
    const allQuestions = JSON.parse(container.dataset.allQuestions || '[]')
    const allDocuments = JSON.parse(container.dataset.allDocuments || '[]')
    const csrf = container.dataset.csrf

    // Determinar qué componente usar basado en el tipo de wizard
    if (wizard.type === 'collector') {
        createApp(WizardCollector, {
            wizard: wizard,
        }).mount('#wizard-container')
    } else {
        createApp(WizardAyuda, {
            wizard: wizard,
            organos: organos,
            sectores: sectores,
            questionTypes: questionTypes,
            questionSectores: questionSectores,
            questionCategorias: questionCategorias,
            allDocuments: allDocuments,
            csrf: csrf,
        }).mount('#wizard-container')
    }
}

if (document.getElementById('gestion-ayudas-app')) {
    createApp(GestionAyudasComponent).mount('#gestion-ayudas-app')
}

if (document.getElementById('questions-app')) {
    createApp(QuestionsAdminComponent).mount('#questions-app')
}

if (document.getElementById('onboarder-app')) {
    createAppWithSentry(OnboarderApp).mount('#onboarder-app')
}
