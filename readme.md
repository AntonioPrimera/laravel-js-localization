# Laravel JS Localization for Vue3 and InertiaJS

This package provides a simple way to manage localization in Laravel with Vue3 and InertiaJS.
While it is very opinionated, it is also very simple to use and requires no additional configuration.

## How it works

1. You create your php language files, as you would normally do in Laravel
2. You run the `npm run lang` command to watch for changes in your language files and generate the corresponding JSON files
3. The ServiceProvider will automatically share the generated JSON file, corresponding to the current locale, with your InertiaJS app
4. You can use the "txt(...)" helper function in your Vue3 components to access the localized strings, like you would with the `__()` helper function in Laravel
5. That's it! Easy-peasy lemon squeezey!

## Installation

```bash
composer require antonioprimera/laravel-js-localization
```

After installing the package, you should run the `install` command, which will publish the package configuration file and
will set up the necessary npm script for watching the language files for changes and generate the corresponding JSON files.

```bash
php artisan js-localization:install
```

This will do the following things:

1. Create a symlink for the language watcher js file in your project's root directory.
2. Add the 'lang' npm script to your package.json file, so you can run 'npm run lang' to start the language watcher.
3. Add the `laravel-inertia-vue-translator`, `chalk` and `chokidar` npm packages to your package.json file. At the end it runs `npm install` to install the newly added npm packages.
4. Optionally publish the package configuration file (you can also do this manually with `php artisan vendor:publish --tag=js-localization-config`).
5. Optionally publish the language files, if no "lang" folder exists in your project's root directory (you can also do this manually with `php artisan lang:publish`).
6. Optionally add the SetLocale middleware to the web middleware group in your bootstrap/app.php file.
7. Provide you with the necessary steps to manually add the corresponding Inertia plugin to your Vue3 app (from package `laravel-inertia-vue-translator` installed in step 3).

**Warning!!!**

The `install` command contains some automated steps, which will inject code into your files. While it tries to be as safe
as possible, it is always recommended to check the changes made to your files and to have a clean git history, so you can
easily revert the changes if something goes wrong.

## Usage

### Laravel

In order to use the package, you need to create your language files in the `lang` directory, as you would normally do in
Laravel. You can create several files for each language and each language corresponds to a directory in the `lang` directory.

At the moment, the package only supports the .php language files, but support for JSON files is planned for the future.

While working with the language files, you should have the file watcher running, using the `npm run lang` command.
The watcher creates a _<locale>.json file in the `lang` directory, for each found locale, which contains all the
translations for that locale. These files are automatically shared with your InertiaJS app, so you can access the
translations in your Vue3 components (only the translations for the current locale are shared).

This package also handles the pluralization of the strings, as Laravel does. You can use the `:count`, `:value` or `:n`
placeholder in your strings to be replaced with the count value, when using the `txts(...)` helper function.

```php
// resources/lang/en/example.php
return [
    'welcome' => 'Welcome to our application!',
    'apples' => '{0} :name has no apples|{1} :name has one apple|[2,10] :name has :count apples|[11,*] :name has too many apples!',
];
```

### Inertia + Vue3

The `laravel-inertia-vue-translator` package exports 2 helper functions, registered directly on the Inertia object,
which you can use in your Vue3 templates to translate your strings:
- txt(key, replacements = {}): This function is used to access the localized strings. It works similarly to the `__()`
helper function in Laravel.
- txts(key, count, replacements = {}): This function is used to access the pluralized localized strings. It works
similarly to the `__()` helper function in Laravel, but expects a number as the second argument, which is used to
determine the plural form of the string.

```vue
<template>
    <div>
        <h1>{{ txt('example.welcome') }}</h1>
        <p>{{ txts('example.apples', 5, {name: 'Mary'} }}</p>
    </div>
</template>
```

In order to use the `txt(...)` and `txts(...)` helper functions in your Vue3 script section, you need to import the
`useTranslator` function from the package, which injects the `txt(...)` and `txts(...)` functions into the current
Vue3 component.

```vue
<script setup>
    // Import the useTranslator function from the laravel-inertia-vue-translator package
    import { useTranslator } from 'laravel-inertia-vue-translator';
    
    // Inject the txt(...) and txts(...) functions into the current Vue3 component
    const { txt, txts } = useTranslator();
	
	// Use the txt(...) and txts(...) functions to access the localized strings
    const welcome = txt('example.welcome');
    const apples = txts('example.apples', 5, {name: 'Mary'});
</script>
```

### LocaleManager

The package also provides a `LocaleManager` facade, which you can use to work with locales in your Laravel app.
The facade provides the following methods:

```php
use AntonioPrimera\LaravelJsLocalization\Facades\LocaleManager;

// Get all the available locales via: config('app.available_locales', ['en'])
$locales = LocaleManager::availableLocales();

// Get the default locale via: config('app.locale', 'en')
$defaultLocale = LocaleManager::defaultLocale();

// Get the fallback locale via: config('app.fallback_locale', 'en')
$fallbackLocale = LocaleManager::fallbackLocale();

// Get the current locale via: app()->getLocale()
$locale = LocaleManager::currentLocale();

// Set the locale for the current request and store it in the session
LocaleManager::setLocale('en');

// Store the locale in the session (will not change the current locale)
LocaleManager::setSessionLocale('en');

// Get the locale from the session
$locale = LocaleManager::sessionLocale();

// Check if a locale is available (checks the available locales)
$available = LocaleManager::isValidLocale('en');
```

### SetLocale Middleware

The package also provides a `SetLocale` middleware, which will get the locale from the session and set it for the
current request.

If you want to change the site locale for the next requests, you can use the `LocaleManager::setLocale(...)` method,
which will store the locale in the session, so the `SetLocale` middleware can pick it up for the next requests.

If you want to create a language switcher, you can create a route that sets the locale in the session and redirects
back to the previous page.

```php
use AntonioPrimera\LaravelJsLocalization\Facades\LocaleManager;

Route::get('set-locale/{locale}', function ($locale) {
    LocaleManager::setSessionLocale($locale);
    return back();
})->name('set-locale');
```

### Related resources

- [NPM Package: laravel-inertia-vue-translator](https://www.npmjs.com/package/laravel-inertia-vue-translator)