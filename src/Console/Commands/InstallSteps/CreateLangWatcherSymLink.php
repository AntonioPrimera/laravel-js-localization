<?php

namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

class CreateLangWatcherSymLink extends InstallStep
{
	
	protected function handle(): array|string|null
	{
		$source = $this->packageRootPath('lang-watcher.js');
		$destination = base_path('lang-watcher.js');
		
		if (file_exists($destination))
			return $this->skippedNotNeeded('The lang-watcher.js file already exists in the project root directory. No changes were made.');
			
		return symlink($source, $destination)
			? $this->success('The symlink to the lang-watcher.js file has been created in the project root directory.')
			: $this->failed('Failed to create the symlink to the lang-watcher.js file in the project root directory.');
	}
	
	protected function stepDescription(): string
	{
		return 'Create a symlink to the lang-watcher.js file in the project root directory.';
	}
}