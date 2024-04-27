<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\PackageJsonFile;

class AddNpmPackagesToPackageJson extends InstallStep
{
	public bool $updatedPackageJson = false;
	
	protected function handle(): array|string|null
	{
		$packageJsonFile = PackageJsonFile::instance();
		if (!$packageJsonFile->exists())
			return $this->failed('The package.json file does not exist. Please create it first.');
		
		$packages = $this->packageList();
		$addedPackages = false;
		
		//add all packages that are not already present in the package.json file
		foreach ($packages as $package => $version) {
			if ($packageJsonFile->hasAny("devDependencies.$package", "dependencies.$package"))
				continue;
			
			$packageJsonFile->add("devDependencies.$package", $version);
			$addedPackages = true;
		}
		
		//if no packages were added, return early
		if (!$addedPackages)
			return $this->skippedNotNeeded('The required packages are already present in package.json. No action taken.');
		
		//save the updated package.json file and run npm install
		$packageJsonFile->save();
		
		return $this->runNpmInstall()
			?? $this->success('The following npm packages have been added to the package.json file: ' . implode(', ', array_keys($packages)));
	}
	
	protected function stepDescription(): string
	{
		return 'Add '
			. implode(', ', array_keys($this->packageList()))
			. ' to the package.json file as devDependencies';
	}
	
	protected function packageList(): array
	{
		return [
			'chalk' => '^5.3.0',
			'chokidar' => '^3.6.0',
			'laravel-inertia-vue-translator' => '^0.1.2',
		];
	}
	
	protected function runNpmInstall(): array|null
	{
		exec('npm install', $output, $returnCode);
		return $returnCode === 0
			? null
			: $this->failed('An error occurred while running "npm install". Please run "npm install" manually to install the new dependencies.');
	}
}