<?php
namespace AntonioPrimera\LaravelJsLocalization;

class LocaleManager
{
	
	public function availableLocales(): array
	{
		return config('app.locales', ['en']);
	}
	
	public function defaultLocale(): string
	{
		return config('app.locale', 'en');
	}
	
	public function fallbackLocale()
	{
		return config('app.fallback_locale', 'en');
	}
	
	public function currentLocale(): string
	{
		return app()->getLocale();
	}
	
	public function setLocale(string $locale): void
	{
		app()->setLocale($locale);
		$this->setSessionLocale($locale);
	}
	
	public function setSessionLocale(string $locale): void
	{
		session()->put('locale', $locale);
	}
	
	public function sessionLocale(): string
	{
		return session()->get('locale', $this->defaultLocale());
	}
	
	public function isValidLocale(string $locale): bool
	{
		return in_array($locale, $this->availableLocales());
	}
}