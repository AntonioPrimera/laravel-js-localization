<?php
namespace AntonioPrimera\LaravelJsI18n\Providers;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\LaravelJsI18n\Console\Commands\MergeLanguageFiles;
use Illuminate\Support\ServiceProvider;

class LaravelJsI18nServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__.'/../../config/js-i18n.php', 'js-i18n');
	}
	
    public function boot(): void
    {
		$this->publishes([
			__DIR__.'/../../config/js-i18n.php' => config_path('js-i18n.php'),
		]);
		
		if ($this->app->runningInConsole()) {
			$this->commands([
				MergeLanguageFiles::class,
			]);
		}
		
		//check if Inertia is installed
		if (!class_exists('Inertia\\Inertia'))
			return;
		
		//prepare the locale and the dictionary file corresponding to the current locale
        $locale = app()->getLocale();
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
}
