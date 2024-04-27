<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers;

use AntonioPrimera\FileSystem\File;
use Illuminate\Support\Arr;

class PackageJsonFile
{
	public File $file;
	protected array|null $jsonContents = null;
	protected bool $changed = false;
	
	public function __construct()
	{
		$this->file = File::instance(base_path('package.json'));
	}
	
	public static function instance(): static
	{
		return new static();
	}
	
	public function contents(): array
	{
		$this->loadJsonContentsIfNecessary();
		return $this->jsonContents;
	}
	
	public function exists(): bool
	{
		return $this->file->exists();
	}
	
	public function has(string $key): bool
	{
		return Arr::has($this->contents(), $key);
	}
	
	public function missing(string $key): bool
	{
		return !$this->has($key);
	}
	
	public function hasAny(...$keys): bool
	{
		foreach ($keys as $key)
			if ($this->has($key))
				return true;
		
		return false;
	}
	
	public function missingAll(...$keys): bool
	{
		return !$this->hasAny(...$keys);
	}
	
	public function add(string $key, string $value): static
	{
		$this->loadJsonContentsIfNecessary();
		Arr::set($this->jsonContents, $key, $value);
		$this->changed = true;
		return $this;
	}
	
	public function save(): static
	{
		if ($this->changed)
			$this->file->putContents(json_encode($this->contents(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		
		return $this;
	}
	
	protected function loadJsonContentsIfNecessary(): void
	{
		if ($this->jsonContents === null)
			$this->jsonContents = json_decode($this->file->getContents(), true);
	}
}