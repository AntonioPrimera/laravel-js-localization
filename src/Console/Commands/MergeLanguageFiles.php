<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Folder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MergeLanguageFiles extends Command
{
    protected $signature = 'dictionary {--pretty}';
    protected $description = 'Merge language files from the lang/{locale} folder into corresponding {locale}.json files in the lang folder.';

    public function handle(): void
    {
        $this->info('Merging language files...');

        $langFolder = $this->languageFolder();
		if (!$langFolder->exists()) {
			$this->error("Language folder not found at {$langFolder->path}. If you haven't published the language files yet, run: php artisan lang:publish");
			return;
		}
			
        $localeFolders = $langFolder->getFolders();

        foreach ($localeFolders as $localeFolder) {
            $localeTranslations = $this->localeTranslations($localeFolder);
            $langFolder->file('_' . $localeFolder->name . '.json')
                ->putContents(json_encode($localeTranslations, $this->option('pretty') ? JSON_PRETTY_PRINT : 0));
        }

        $this->outputCompiledLangFiles();

        $this->newLine();
        $this->info('Done!');
    }

    protected function localeTranslations(Folder $localeFolder): array
    {
        $this->info("Merging locale {$localeFolder->name}...");
        $files = $this->getTranslationFilesForLocale($localeFolder);

        $localeTranslations = [];

        foreach ($files as $file) {
            $translations = $this->getTranslationsFromFile($file['file']);
            Arr::set($localeTranslations, $file['key'], $translations);
        }

        return $localeTranslations;
    }

    protected function getTranslationFilesForLocale(Folder $localeFolder): Collection
    {
        //get all php files in the locale folder (deep), remove the extension and replace directory separators with dots
        return collect($localeFolder->getAllFiles('/\.php$/'))
            ->map(fn (File $file) => [
                'file' => $file,
                'key' => str_replace(
                    DIRECTORY_SEPARATOR,
                    '.',
                    substr($file->relativePath($localeFolder->path), 0, -4)
                ),
            ]);
    }

    protected function getTranslationsFromFile(File $file): array
    {
        return require $file->path;
    }

    protected function outputCompiledLangFiles(): void
    {
        $this->newLine();
        $this->info('Compiled language files:');

        foreach ($this->languageFolder()->getFiles('/_.*\.json$/') as $langFile)
            $this->info("$langFile->name - $langFile->humanReadableFileSize");
    }
	
	//the root folder where the locale folders are stored, as a Folder instance
	protected function languageFolder(): Folder
	{
		return Folder::instance(base_path(config('js-localization.language-folder')));
	}
}
