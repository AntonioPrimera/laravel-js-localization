<?php
namespace AntonioPrimera\LaravelJsLocalization\LocaleSetters;

use AntonioPrimera\LaravelJsLocalization\Facades\LocaleManager;
use Illuminate\Support\Facades\App;

class SessionLocaleSetter implements LocaleSetterInterface
{
	
	public function setLocale(): void
	{
		$locale = LocaleManager::sessionLocale() ?? LocaleManager::defaultLocale();
		
		if ($locale && LocaleManager::isValidLocale($locale))
			LocaleManager::setLocale($locale);
	}
}