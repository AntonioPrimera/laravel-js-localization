<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\LaravelJsLocalization\Http\Middleware\SetLocale;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;

class InstallLaravelJsLocalizationPackage extends Command
{
    protected $signature = 'js-localization:install';
    protected $description = 'Install the Laravel JS Localization package.';

    public function handle(): void
    {
        $this->info('Installing the Laravel JS Localization package...');
		
		//steps to install the package (as an array of callable functions)
		$steps = [
			//1. Symlink lang-watcher.js from this package to the root of the project
			[$this, 'symlinkLangWatcher'],
			
			//2. Add the lang-watcher.js script to the scripts section of package.json as "lang": "node lang-watcher.js"
			[$this, 'addLangWatcherScriptToPackageJson'],
			
			//3. Ask the user if they want to publish the config file
			[$this, 'askToPublishConfigFile'],
			
			//4. Check if the lang folder exists, if not, ask the user if they want to run "php artisan lang:publish" to publish the lang folder
			[$this, 'publishLangFolder'],
			
			//5. Ask the user if they want to add the SetLocale middleware to the web middleware group in the Kernel.php file
			[$this, 'addSetLocaleMiddleware'],
			
			//6. Inform the user that they need to add the inertia plugin to the app.js file, where the app is created
			[$this, 'informUserToAddInertiaPlugin']
		];
		
		//run each step
		foreach ($steps as $index => $step)
			call_user_func($step, $index + 1, count($steps));
		
        $this->newLine();
        $this->info('Done!');
    }

	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	//1. Symlink lang-watcher.js from this package to the root of the project
	protected function symlinkLangWatcher(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Symlinking lang-watcher.js...");
		$source = __DIR__ . '/../../../lang-watcher.js';
		$destination = base_path('lang-watcher.js');
		
		if (file_exists($destination))
			$this->info('The lang-watcher.js file already exists. No action taken.');
		else {
			symlink($source, $destination);
			$this->info('The lang-watcher.js file has been symlinked successfully.');
		}
	}
	
	//2. Add the lang-watcher.js script to the scripts section of package.json as "lang": "node lang-watcher.js"
	protected function addLangWatcherScriptToPackageJson(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Adding the lang-watcher.js script to the scripts section of the package.json file...");
		$packageJsonFile = File::instance(base_path('package.json'));
		if (!$packageJsonFile->exists()) {
			$this->error('The package.json file does not exist. Please create it first.');
			return;
		}
		
		$packageJson = json_decode($packageJsonFile->getContents(), true);
		$packageJson['scripts']['lang'] = 'node lang-watcher.js';
		$packageJsonFile->putContents(json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		$this->info('The lang-watcher.js script has been added to the scripts section of the package.json file. You can now run "npm run lang" to watch for changes in the lang folder.');
	}
	
	//3. Ask the user if they want to publish the config file
	protected function askToPublishConfigFile(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Publishing the config file...");
		$publishConfig = confirm(
			label: 'Do you want to publish the config file?',
			default: true,
			yes: 'Publish the config file to config/js-localization.php',
			no: 'Do not publish the config file',
			hint: 'You can always publish the config file later by running "php artisan vendor:publish --tag=js-localization-config"'
		);
		
		if ($publishConfig) {
			$this->call('vendor:publish', ['--tag' => 'js-localization-config']);
			$this->info('The config file has been published successfully to config/js-localization.php');
		} else {
			$this->info('The config file has not been published.');
		}
	}
	
	//4. Check if the lang folder exists, if not, ask the user if they want to run "php artisan lang:publish" to publish the lang folder
	protected function publishLangFolder(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Publishing the lang files if necessary...");
		$langFolder = Folder::instance(base_path('lang'));
		
		if (!$langFolder->exists()) {
			$this->info('The lang folder does not exist.');
			$publishLang = confirm(
				label: 'Do you want to run "php artisan lang:publish" to publish the lang folder?',
				default: true,
				hint: 'You can always publish the lang folder later by running "php artisan lang:publish" or you can manually create the lang folder and add your language files.'
			);
			
			if ($publishLang) {
				$this->call('lang:publish');
				$this->info('The lang folder has been published successfully.');
			} else {
				$this->info('The lang folder has not been published.');
			}
		} else {
			$this->info('The lang folder already exists. No action taken.');
		}
	}
	
	//5. Ask the user if they want to add the SetLocale middleware to the web middleware group in the bootstrap/app.php file
	protected function addSetLocaleMiddleware(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Adding the SetLocale middleware to the web middleware group in bootstrap/app.php...");
		$appFile = File::instance(base_path('bootstrap/app.php'));
		
		$confirm = confirm(
			label: 'Do you want to add the SetLocale middleware to the web middleware group in the bootstrap/app.php file?',
			default: true,
			hint: 'This will automatically set the locale based on the locale stored in the session. You can always add the middleware manually by adding \AntonioPrimera\LaravelJsLocalization\Http\Middleware\SetLocale::class to the $middleware->web() group in the bootstrap/app.php file.'
		);
		
		if (!$confirm) {
			$this->info('The SetLocale middleware has not been added.');
			return;
		}
		
		if (!$appFile->exists()) {
			$this->error('The bootstrap/app.php file does not exist. The SetLocale middleware could not be added.');
			return;
		}
		
		$appContent = $appFile->getContents();
		//find the first ']' after the '$middleware->web(' line and inject the SetLocale middleware before it (on a separate line)
		$middlewareWebIndex = strpos($appContent, '$middleware->web(');
		if ($middlewareWebIndex === false) {
			$this->error('The position to inject the SetLocale middleware could not be found. The SetLocale middleware could not be added.');
			return;
		}
		
		$middlewareWebIndex = strpos($appContent, ']', $middlewareWebIndex);
		if ($middlewareWebIndex === false) {
			$this->error('The position to inject the SetLocale middleware could not be found. The SetLocale middleware could not be added.');
			return;
		}
		
		$setLocaleMiddleware = '\AntonioPrimera\LaravelJsLocalization\Http\Middleware\SetLocale::class,';
		$appContent = substr_replace($appContent, $setLocaleMiddleware . PHP_EOL, $middlewareWebIndex, 0);
		$appFile->putContents($appContent);
		
		$this->info('The SetLocale middleware has been added to the web middleware group in the bootstrap/app.php file.');
	}
	
	//6. Inform the user that they need to add the inertia plugin to the app.js file, where the app is created
	protected function informUserToAddInertiaPlugin(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] This step is not automated, because it depends on the structure and setup of your app.");
		$this->info('Add the following lines to your resources/js/app.js file, where the app is created:');
		
		$this->newLine();
		$this->info('//import the translator plugin from the Laravel JS Localization package');
		$this->info('import {translatorPlugin} from "../../vendor/antonioprimera/laravel-js-localization/resources/js/InertiaPlugins/vue3Translator";');
		$this->newLine();
		$this->info('//add the translator plugin to the app (before the .mount() method)');
		$this->info('.use(translatorPlugin);');
		
		$this->newLine();
		$this->info('This will make the translator available in all Inertia views.');
	}
	
}


