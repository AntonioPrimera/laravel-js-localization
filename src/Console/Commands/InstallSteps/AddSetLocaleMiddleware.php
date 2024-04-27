<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\LaravelJsLocalization\Http\Middleware\SetLocale;
use function Laravel\Prompts\confirm;

class AddSetLocaleMiddleware extends InstallStep
{
	
	protected function handle(): array|string|null
	{
		$appFile = File::instance(base_path('bootstrap/app.php'));
		
		if (!$appFile->exists())
			return $this->failed('The bootstrap/app.php file could not be found. SetLocale could not be added.');
		
		$appFileContents = $appFile->getContents();
		
		//if the middleware is already present in the app.php file, skip this step (no need to ask the user)
		if (str_contains($appFileContents, SetLocale::class))
			return $this->skippedNotNeeded('SetLocale is already present in the web middleware group in bootstrap/app.php. No action taken.');
		
		$confirm = confirm(
			label: 'Do you want to add SetLocale to the web middleware group in the bootstrap/app.php file?',
			default: true,
			hint: 'It will try to inject code into your bootstrap/app.php file. If you changed the file structure, this step might fail or might produce unexpected results!',
		);
		
		if (!$confirm)
			return $this->skippedByUser('Step skipped by user: SetLocale has not been added to the web middleware group. No action taken.');
		
		//find the web middleware group in the app.php file
		$middlewareWebIndex = strpos($appFileContents, '$middleware->web(');
		if ($middlewareWebIndex === false)
			return $this->failed('No standard web middleware group found in bootstrap/app.php. SetLocale could not be added.');
		
		//add it after the first '[' as the first middleware in the group
		$middlewareWebIndex = strpos($appFileContents, '[', $middlewareWebIndex);
		if ($middlewareWebIndex === false)
			return $this->failed('The position to inject the SetLocale middleware could not be found. SetLocale could not be added.');
		
		$setLocaleMiddleware = "\n            \\AntonioPrimera\\LaravelJsLocalization\\Http\\Middleware\\SetLocale::class,\n";
		$appFileContents = substr_replace($appFileContents, $setLocaleMiddleware, $middlewareWebIndex + 1, 0);
		$appFile->putContents($appFileContents);
		
		return $this->success('The SetLocale middleware has been added to the web middleware group in the bootstrap/app.php file. Please check the file to verify the changes.');
	}
	
	protected function stepDescription(): string
	{
		return 'Add SetLocale to the web middleware group in bootstrap/app.php';
	}
}