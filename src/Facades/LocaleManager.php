<?php
namespace AntonioPrimera\LaravelJsLocalization\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AntonioPrimera\LaravelJsLocalization\LocaleManager
 *
 * @method static array availableLocales()
 * @method static string defaultLocale()
 * @method static string fallbackLocale()
 * @method static string currentLocale()
 * @method static string|null authenticatedUserLocale()
 * @method static void setLocale(string $locale)
 * @method static void setSessionLocale(string $locale)
 * @method static string sessionLocale()
 * @method static bool isValidLocale(string $locale)
 */
class LocaleManager extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'locale-manager';
	}
}