<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\PackageJsonFile;

class AddLangWatchCommandToPackageJson extends InstallStep
{
	
	protected function handle(): array|string|null
	{
		$packageJsonFile = PackageJsonFile::instance();
		if (!$packageJsonFile->exists())
			return $this->failed('The package.json file does not exist. Please create it first.');
		
		if ($packageJsonFile->has('scripts.lang'))
			return $this->skippedNotNeeded('The "lang" script is already present in the scripts section of the package.json file. No action taken.');
		
		$packageJsonFile->add('scripts.lang', 'node lang-watcher.js')
			->save();
		
		return $this->success('The lang-watcher.js script has been added to the scripts section of the package.json file. You can now run "npm run lang" to watch for changes in the lang folder.');
	}
	
	protected function stepDescription(): string
	{
		return 'Add the "lang" script to the scripts section of the package.json file.';
	}
}