<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\FileSystem\File;
use Illuminate\Support\Facades\Artisan;

class PublishConfigFile extends InstallStep
{
	protected function handle(): array|string|null
	{
		if (File::instance(base_path('config/js-localization.php'))->exists())
			return $this->skippedNotNeeded('The js-localization.php config file already exists. No action taken.');
		
		$publishConfig = $this->confirm(
			question: 'Do you want to publish the config file?',
			default: true,
			hint: 'You can always publish the config file later by running "php artisan vendor:publish --tag=js-localization-config"'
		);
		
		if ($publishConfig)
			return Artisan::call('vendor:publish', ['--tag' => 'js-localization-config'])
				? $this->failed('An error occurred while publishing the config file.')
				: $this->success('The config file has been published successfully to config/js-localization.php');
		
		return $this->skippedByUser('Step skipped by user: The config file has not been published.');
	}
	
	protected function stepDescription(): string
	{
		return 'Publishing the config file...';
	}
}