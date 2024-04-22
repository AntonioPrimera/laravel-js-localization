<?php
namespace AntonioPrimera\LaravelJsI18n\Console\Commands;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Folder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class InstallLaravelJsI18nPackage extends Command
{
    protected $signature = 'js-i18n-install';
    protected $description = 'Install the Laravel JS i18n package.';

    public function handle(): void
    {
        $this->info('Installing the Laravel JS i18n package...');
		
		$stepCount = 6;
		
		//steps to install the package (as an array of callable functions)
		$steps = [
			//1. Symlink lang-watcher.js from this package to the root of the project
			[$this, 'symlinkLangWatcher'],
			//2. Add the lang-watcher.js script to the scripts section of package.json as "lang": "node lang-watcher.js"
			
			//3. Ask the user if they want to publish the config file
			//4. Check if the lang folder exists, if not, ask the user if they want to run "php artisan lang:publish" to publish the lang folder
			//5. Ask the user if they want to add the SetLocale middleware to the web middleware group in the Kernel.php file
			//6. Inform the user that they need to add the inertia plugin to the app.js file, where the app is created
		];
		
		//run each step
		foreach ($steps as $index => $step)
			call_user_func($step, $index + 1, count($steps));
		
        $this->newLine();
        $this->info('Done!');
    }

	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function symlinkLangWatcher(int $step, int $maxSteps): void
	{
		$this->info("[Step $step/$maxSteps] Symlinking lang-watcher.js...");
		$source = __DIR__ . '/../../../lang-watcher.js';
		$destination = base_path('lang-watcher.js');
		
		if (file_exists($destination))
			$this->info('The lang-watcher.js file already exists. No action taken.');
		else
			symlink($source, $destination);
	}
	
	protected function ()
	{
	
	}
}


