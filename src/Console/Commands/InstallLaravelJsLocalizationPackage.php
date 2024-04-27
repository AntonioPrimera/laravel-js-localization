<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands;

use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\AddNpmPackagesToPackageJson;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\AddInertiaPlugin;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\AddLangWatchCommandToPackageJson;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\AddSetLocaleMiddleware;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\CreateLangWatcherSymLink;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\Console;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\Steps;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\PublishConfigFile;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\PublishLangFiles;
use Illuminate\Console\Command;

class InstallLaravelJsLocalizationPackage extends Command
{
    protected $signature = 'js-localization:install';
    protected $description = 'Install the Laravel JS Localization package.';
	
	protected array $stepInstances = [];
	//protected bool $requiresNpmInstall = false;

    public function handle(): void
    {
        $this->info('Installing the Laravel JS Localization package...');
		Console::instantiate($this->input, $this->output);	//this is used inside the steps, so they can output messages
		
		$steps = Steps::create([
			//Symlink lang-watcher.js from this package to the root of the project
			CreateLangWatcherSymLink::class,
			
			//Add the lang-watcher.js script to the scripts section of package.json as "lang": "node lang-watcher.js"
			AddLangWatchCommandToPackageJson::class,
			
			//Add "chalk" and "chokidar" as devDependencies in the package.json file
			AddNpmPackagesToPackageJson::class,
			
			//Ask the user if they want to publish the config file
			PublishConfigFile::class,
			
			//Publish the lang folder if necessary ("php artisan lang:publish")
			PublishLangFiles::class,
			
			//Add the SetLocale middleware to the web middleware group in bootstrap/app.php
			AddSetLocaleMiddleware::class,
			
			//Inform the user that they need to add the inertia plugin to the app.js file, where the app is created
			AddInertiaPlugin::class,
		]);
		
		$steps->run();
		
        $this->newLine();
        $this->info('Done!');
    }
}


