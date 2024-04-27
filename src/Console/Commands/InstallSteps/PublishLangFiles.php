<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\FileSystem\Folder;
use Illuminate\Support\Facades\Artisan;
use function Laravel\Prompts\confirm;

class PublishLangFiles extends InstallStep
{
	
	protected function handle(): array|string|null
	{
		$langFolder = Folder::instance(base_path('lang'));
		
		if ($langFolder->exists())
			return $this->skippedNotNeeded('The lang folder already exists. No action taken.');
		
		$publishLang = confirm(
			label: 'Do you want to publish the default lang files?',
			default: true,
			hint: 'You can always publish the lang folder later by running "php artisan lang:publish" or you can manually create the lang folder and add your language files.'
		);
		
		if (!$publishLang)
			return $this->skippedByUser('Step skipped by user: The lang folder has not been published.');
		
		return Artisan::call('lang:publish')
			? $this->failed('An error occurred while publishing the lang folder.')
			: $this->success('The lang folder has been published successfully.');
	}
	
	protected function stepDescription(): string
	{
		return 'Publish the lang files.';
	}
}