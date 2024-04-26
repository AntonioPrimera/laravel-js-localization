<?php
namespace AntonioPrimera\LaravelJsLocalization\Facades;

use Illuminate\Support\Facades\Facade;

class LocaleManager extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'locale-manager';
	}
}