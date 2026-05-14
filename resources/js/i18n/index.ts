import { createI18n } from "vue-i18n";

import en from './locales/en.ts'
import ar from './locales/ar.ts'
import he from './locales/he.ts'

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',


    messages: {
        en,
        he,
        ar,
    },
})


export default i18n;