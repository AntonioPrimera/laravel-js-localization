<?php
namespace AntonioPrimera\LaravelJsLocalization\Providers;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallLaravelJsLocalizationPackage;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\MergeLanguageFiles;
use AntonioPrimera\LaravelJsLocalization\LocaleManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class LaravelJsLocalizationServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__ . '/../../config/js-localization.php', 'js-localization');
		
		//register the LocaleManager class in the service container
		$this->app->singleton('locale-manager', LocaleManager::class);
	}
	
    public function boot(): void
    {
		//publish the config file
		$this->publishes([
			__DIR__.'/../../config/js-localization.php' => config_path('js-localization.php'),
		], 'js-localization-config');
		
		//register the artisan commands
		if ($this->app->runningInConsole()) {
			$this->commands([
				MergeLanguageFiles::class,
				InstallLaravelJsLocalizationPackage::class
			]);
		}
		
		//set the locale (if a locale setter class is configured)
		$this->setLocale();
		
		//check if Inertia is installed
		if (!class_exists('Inertia\\Inertia'))
			return;
		
		//prepare the locale and the dictionary file corresponding to the current locale
        $locale = App::getLocale();
        $dictionary = $this->translations($locale);

		//share the locale and the dictionary to all Inertia views
        \Inertia\Inertia::share(compact('locale', 'dictionary'));
    }

    //--- Protected helpers -------------------------------------------------------------------------------------------

    protected function translations(string $locale): array
    {
        $translationsFile = File::instance(base_path("lang/_$locale.json"));

        return $translationsFile->exists()
            ? json_decode($translationsFile->getContents(), true)
            : [];
    }
	
	protected function setLocale(): void
	{
		//determine the locale setter class from the config (if not set, the locale will not be set)
		$configuredLocaleSetterClass = Config::get('js-localization.locale-setter');
		if (!$configuredLocaleSetterClass)
			return;
		
		//set the locale (assuming the class has a setLocale method)
		(new $configuredLocaleSetterClass())->setLocale();
	}
}
