<?php
return [
	/**
	 * The folder where the language files are stored, relative to the project root
	 *
	 * By default, the language files are stored in the 'lang' folder in the project root.
	 */
	'language-folder' => 'lang',
	
	/**
	 * The class that sets the locale in the app
	 *
	 * This class must implement AntonioPrimera\LaravelJsLocalization\Interfaces\LocaleSetter.
	 * By default, the UserSessionLocaleSetter is used, which tries to determine the locale from the authenticated user,
	 * if a user is logged in, then falls back to the session locale, and finally to the default app locale.
	 * Replace this with your own class if you have a different way of determining the locale.
	 */
	'locale-setter' => \AntonioPrimera\LaravelJsLocalization\LocaleSetters\UserSessionLocaleSetter::class,
	
	/**
	 * The property of the authenticated user model that holds the locale
	 *
	 * If you have a property in your user model, that holds the locale of the user, you can set it here,
	 * and the locale will be set to the value of this property when the user is authenticated.
	 * You can leave it commented out if you don't have such a property on your user model.
	 */
	'user-locale-property' => 'language',
];