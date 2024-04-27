<?php

namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\Console;
use function Laravel\Prompts\pause;

class AddInertiaPlugin extends InstallStep
{
	
	protected function handle(): array|string|null
	{
		$output = [
			'This step is not automated, because it depends on the structure and setup of your app.',
			'Add the following lines to your resources/js/app.js file, where the app is created:',
			"\n",
			'+------------------------------------------------------------------------------------+',
			'|  JS Code to add to the app.js file:                                                |',
			'+------------------------------------------------------------------------------------+',
			"\n",
			'    <bg=black;fg=default>//import the translator plugin from the Laravel JS Localization package</>',
			'    <bg=black;fg=bright-blue>import {translatorPlugin} from "laravel-inertia-vue-translator";</>',
			"\n",
			'    <bg=black;fg=default>//add the translator plugin to the app (before the .mount() method)</>',
			'    <bg=black;fg=bright-blue>.use(translatorPlugin);</>',
			"\n",
			'+------------------------------------------------------------------------------------+',
		];
		
		collect($output)->each(function ($line) {
			$message = is_array($line) ? $line[0] : $line;
			$style = is_array($line) ? $line[1] : null;
			Console::instance()->line($message, $style);
		});
		
		pause('Once you have read the information above and added the necessary code to your project, press any key to continue...');
		
		return null;
	}
	
	protected function stepDescription(): string
	{
		return 'Manually add the Inertia plugin to the app.js file';
	}
}