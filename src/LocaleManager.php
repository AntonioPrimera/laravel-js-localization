<?php
namespace AntonioPrimera\LaravelJsLocalization;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class LocaleManager
{
	
	public function availableLocales(): array
	{
		return Config::get('app.locales', ['en']);
	}
	
	public function defaultLocale(): string
	{
		return Config::get('app.locale', 'en');
	}
	
	public function fallbackLocale()
	{
		return Config::get('app.fallback_locale', 'en');
	}
	
	public function currentLocale(): string
	{
		return App::getLocale();
	}
	
	public function authenticatedUserLocale(): string|null
	{
		$localeProperty = Config::get('js-localization.user-locale-property');
		return $localeProperty ? Auth::user()?->$localeProperty : null;
	}
	
	public function setLocale(string $locale): void
	{
		App::setLocale($locale);
		$this->setSessionLocale($locale);
	}
	
	public function setSessionLocale(string $locale): void
	{
		Session::put('locale', $locale);
	}
	
	public function sessionLocale(): string
	{
		return Session::get('locale', $this->defaultLocale());
	}
	
	public function isValidLocale(string $locale): bool
	{
		return in_array($locale, $this->availableLocales());
	}
}