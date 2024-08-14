<?php
namespace AntonioPrimera\LaravelJsLocalization\Http\Middleware;

use AntonioPrimera\FileSystem\File;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
		try {
			//set the locale, based on the user's language property, falling back
			//to the session locale and finally to the default locale
			$this->setLocale();
			
			//provide the translations to Inertia (if Inertia is installed)
			$this->provideTranslationsToInertia();
		} catch (\Exception $e) {
			Log::critical('Failed to set locale: ' . $e->getMessage());
		}
		
        return $next($request);
    }
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function setLocale(): void
	{
		//determine the locale setter class from the config (if not set, the locale will not be set)
		$configuredLocaleSetterClass = Config::get('js-localization.locale-setter');
		if (!$configuredLocaleSetterClass)
			return;
		
		//set the locale (assuming the class has a setLocale method)
		(new $configuredLocaleSetterClass())->setLocale();
	}
	
	protected function provideTranslationsToInertia(): void
	{
		//check if Inertia is installed
		if (!class_exists('Inertia\\Inertia'))
			return;
		
		//prepare the locale and the dictionary file corresponding to the current locale
		$locale = App::getLocale();
		$dictionary = $this->translations($locale);
		
		//share the locale and the dictionary to all Inertia views
		\Inertia\Inertia::share(compact('locale', 'dictionary'));
	}
	
	protected function translations(string $locale): array
	{
		$translationsFile = File::instance(base_path("lang/_$locale.json"));
		
		return $translationsFile->exists()
			? json_decode($translationsFile->getContents(), true)
			: [];
	}
}
