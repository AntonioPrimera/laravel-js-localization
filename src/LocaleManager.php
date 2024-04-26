<?php
namespace AntonioPrimera\LaravelJsLocalization;

class LocaleManager
{
	
	public function availableLocales(): array
	{
		return config('app.available_locales', ['en']);
	}
	
	public function defaultLocale(): string
	{
		return config('app.default_locale', 'en');
	}
	
	public function currentLocale(): string
	{
		return app()->getLocale();
	}
	
	public function setLocale(string $locale): void
	{
		app()->setLocale($locale);
		session()->put('locale', $locale);
	}
	
	public function isValidLocale(string $locale): bool
	{
		return in_array($locale, $this->availableLocales());
	}
}