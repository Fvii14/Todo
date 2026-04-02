import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: 'Documentación Tech TTF',
    description: 'Documentación técnica para programadores de Tu Trámite Fácil',
    head: [['link', { rel: 'icon', href: '/favicon.ico' }]],
    themeConfig: {
        // https://vitepress.dev/reference/default-theme-config
        nav: [
            { text: 'Home', link: '/' },
            {
                text: 'Docs',
                link: '/laravel',
                activeMatch: '/(laravel|vue|python|infraestructura)',
            },
        ],

        sidebar: [
            { text: 'Home', link: '/' },
            {
                text: 'Docs',
                items: [
                    {
                        text: 'Laravel',
                        link: '/laravel',
                        items: [
                            {
                                text: 'Modelos',
                                link: '/laravel/modelos',
                                items: [
                                    { text: 'Alerta', link: '/laravel/modelos/Alerta' },
                                    { text: 'Answer', link: '/laravel/modelos/Answer' },
                                    { text: 'Arrendatario', link: '/laravel/modelos/Arrendatario' },
                                    { text: 'Ayuda', link: '/laravel/modelos/Ayuda' },
                                    { text: 'CCAA', link: '/laravel/modelos/CCAA' },
                                    { text: 'Contratacion', link: '/laravel/modelos/Contratacion' },
                                    { text: 'Organo', link: '/laravel/modelos/Organo' },
                                    { text: 'Question', link: '/laravel/modelos/Question' },
                                    {
                                        text: 'QuestionCondition',
                                        link: '/laravel/modelos/QuestionCondition',
                                    },
                                    {
                                        text: 'Questionnaire',
                                        link: '/laravel/modelos/Questionnaire',
                                    },
                                    { text: 'User', link: '/laravel/modelos/User' },
                                ],
                            },
                        ],
                    },
                    {
                        text: 'Vue',
                        link: '/vue',
                        items: [
                            {
                                text: 'Componentes',
                                link: '/vue/componentes',
                                items: [
                                    {
                                        text: 'ConditionalOptionsModal',
                                        link: '/vue/componentes/ConditionalOptionsModal',
                                    },
                                ],
                            },
                        ],
                    },
                    { text: 'Python', link: '/python' },
                    { text: 'Infraestructura', link: '/infraestructura' },
                ],
            },
        ],
        docFooter: {
            prev: 'Página anterior',
            next: 'Página siguiente',
        },
        outline: {
            label: 'En esta página',
            level: [2, 3],
        },
    },
})
