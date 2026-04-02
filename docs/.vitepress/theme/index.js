import DefaultTheme from 'vitepress/theme'
import TechCard from './TechCard.vue'
import './custom.css'

export default {
    ...DefaultTheme,
    enhanceApp({ app }) {
        app.component('TechCard', TechCard)
    },
}
