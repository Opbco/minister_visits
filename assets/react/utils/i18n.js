import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import { DateTime } from "luxon";
import Backend from "i18next-http-backend";
import LanguageDetector from "i18next-browser-languagedetector";

i18n
  .use(Backend)
  .use(initReactI18next)
  .use(LanguageDetector) // passes i18n down to react-i18next
  .init({
    backend: {
      loadPath: "/locales/{{lng}}/{{ns}}.json",
    },
    ns: [
      "common",
      "login",
      "students",
      "paiements",
      "reports",
      "depenses",
      "classes",
      "teachers",
      "notes",
    ],
    fallbackLng: "fr",
    interpolation: {
      format: (value, format, lng) => {
        // legacy usage
        if (value instanceof Date) {
          return DateTime.fromJSDate(value)
            .setLocale(lng)
            .toLocaleString(DateTime[format]);
        }
        return value;
      },
    },
  });