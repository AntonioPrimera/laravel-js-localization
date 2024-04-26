<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\LaravelJsLocalization\Http\Middleware\SetLocale;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\pause;

class InstallLaravelJsLocalizationPackage extends Command
{
    protected $signature = 'js-localization:install';
    protected $description = 'Install the Laravel JS Localization package.';
	
	protected bool $requiresNpmInstall = false;

    public function handle(): void
    {
        $this->info('Installing the Laravel JS Localization package...');
		
		//steps to install the package (as an array of callable functions)
		$steps = [
			//Step: Symlink lang-watcher.js from this package to the root of the project
			[$this, 'symlinkLangWatcher'],
			
			//Step: Add the lang-watcher.js script to the scripts section of package.json as "lang": "node lang-watcher.js"
			[$this, 'addLangWatcherScriptToPackageJson'],
			
			//Step: Add "chalk" and "chokidar" as devDependencies in the package.json file
			[$this, 'addChalkAndChokidarToPackageJson'],
			
			//Step: Ask the user if they want to publish the config file
			[$this, 'askToPublishConfigFile'],
			
			//Step: Check if the lang folder exists, if not, ask the user if they want to run "php artisan lang:publish" to publish the lang folder
			[$this, 'publishLangFolder'],
			
			//Step: Ask the user if they want to add the SetLocale middleware to the web middleware group in the Kernel.php file
			[$this, 'addSetLocaleMiddleware'],
			
			//Step: Inform the user that they need to add the inertia plugin to the app.js file, where the app is created
			[$this, 'informUserToAddInertiaPlugin'],
			
			//Step: Run "npm install" to install the new devDependencies
			[$this, 'runNpmInstall']
		];
		
		//run each step
		foreach ($steps as $index => $step)
			call_user_func($step, $index + 1, count($steps));
		
        $this->newLine();
        $this->info('Done!');
    }

	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	//Step: Symlink lang-watcher.js from this package to the root of the project
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
	
	//Step: Add the lang-watcher.js script to the scripts section of package.json as "lang": "node lang-watcher.js"
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
	
	//Step: Ask the user if they want to publish the config file
	protected function askToPublishConfigFile(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Publishing the config file...");
		if (File::instance(base_path('config/js-localization.php'))->exists()) {
			$this->info('The config file already exists. No action taken.');
			return;
		}
		
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
	
	//Step: Check if the lang folder exists, if not, ask the user if they want to run "php artisan lang:publish" to publish the lang folder
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
	
	//Step: Ask the user if they want to add the SetLocale middleware to the web middleware group in the bootstrap/app.php file
	protected function addSetLocaleMiddleware(int $step, int $maxSteps): void
	{
		$appFile = File::instance(base_path('bootstrap/app.php'));
		
		//if the middleware is already present in the app.php file, skip this step (no need to ask the user)
		if ($appFile->exists() && str_contains($appFile->getContents(), SetLocale::class)) {
			$this->info("[Step $step/$maxSteps] The SetLocale middleware is already present in the web middleware group in the bootstrap/app.php file. No action taken.");
			return;
		}
		
		$this->info("[Step $step/$maxSteps] Adding the SetLocale middleware to the web middleware group in bootstrap/app.php...");
		
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
	
	//Step: Inform the user that they need to add the inertia plugin to the app.js file, where the app is created
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
		pause('Once you have read the information above and added the necessary code to your project, press any key to continue...');
	}
	
	//Step: Add "chalk" and "chokidar" as devDependencies in the package.json file
	protected function addChalkAndChokidarToPackageJson(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Adding 'chalk' and 'chokidar' as devDependencies in the package.json file as devDependencies...");
		$packageJsonFile = File::instance(base_path('package.json'));
		if (!$packageJsonFile->exists()) {
			$this->error('The package.json file does not exist. Please create it first.');
			return;
		}
		
		$packageJson = json_decode($packageJsonFile->getContents(), true);
		
		//check if chalk and chokidar are already in the devDependencies
		$requiresChalk = !(Arr::has($packageJson, 'devDependencies.chalk') || Arr::has($packageJson, 'dependencies.chalk'));
		$requiresChokidar = !(Arr::has($packageJson, 'devDependencies.chokidar') || Arr::has($packageJson, 'dependencies.chokidar'));
		if (!$requiresChalk && !$requiresChokidar) {
			$this->info('The devDependencies "chalk" and "chokidar" are already present in the package.json file. No action taken.');
			return;
		}
		
		//add chalk and chokidar to the devDependencies
		if ($requiresChalk)
			$packageJson['devDependencies']['chalk'] = '^5.3.0';
		
		if ($requiresChokidar)
			$packageJson['devDependencies']['chokidar'] = '^3.6.0';
		
		$packageJsonFile->putContents(json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		$this->info('The devDependencies "chalk" and "chokidar" have been added to the package.json file.');
		$this->requiresNpmInstall = true;
	}
	
	//Step: Run "npm install" to install the new devDependencies
	protected function runNpmInstall(int $step, int $maxSteps): void
	{
		if (!$this->requiresNpmInstall) {
			return;
		}
		
		$this->info("[Step $step/$maxSteps] Running 'npm install' to install the new devDependencies...");
		
		exec('npm install', $output, $returnCode);
		if ($returnCode !== 0) {
			$this->error('An error occurred while running "npm install". Please run "npm install" manually to install the new devDependencies.');
			return;
		}
		
		$this->info('The new devDependencies have been installed successfully.');
	}
	
}


