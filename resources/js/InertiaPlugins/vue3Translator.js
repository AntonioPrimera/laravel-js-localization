import {usePage} from "@inertiajs/vue3";
import {inject} from "vue";
import {translate, translatePlural} from "../translator";

const injectionKey = Symbol('translator');
export const useTranslator = () => inject(injectionKey);

export const translatorPlugin = {
    install(app, options) {
        const dictionary = () => {
            return options && options.dictionary ? options.dictionary : usePage().props.dictionary;
        };
        
        //define the translator methods, using the dictionary from the options or the page
        const txt = (key, replace) => translate(key, replace, dictionary);
        const txts = (key, count, replace) => translatePlural(key, count, replace, dictionary);

        //add the translator methods to the app
        app.config.globalProperties.dictionary = dictionary;    //get the dictionary
        app.config.globalProperties.txt = txt;                  //simple translation
        app.config.globalProperties.txts = txts;                //plural translation

        //inject the translator methods into the app
        app.provide(injectionKey, {
            dictionary,
            txt,
            txts,
        });
    }
}
