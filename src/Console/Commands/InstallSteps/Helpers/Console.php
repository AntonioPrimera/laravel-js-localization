<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers;

use Illuminate\Console\Concerns\InteractsWithIO;

class Console
{
	use InteractsWithIO;
	
	protected static Console $instance;
	
	protected function __construct($input, $output)
	{
		$this->setInput($input);
		$this->setOutput($output);
	}
	
	public static function instantiate($input, $output): static
	{
		static::$instance = new static($input, $output);
		return static::$instance;
	}
	
	public static function instance(): static
	{
		return static::$instance;
	}
	
	//forward all method calls to the instance
	public static function __callStatic(string $name, array $arguments)
	{
		return static::instance()->$name(...$arguments);
	}
	
	//--- Helpers -----------------------------------------------------------------------------------------------------
	
	//public function separator(): static
	//{
	//	$this->line('-----------------------------------------------------');
	//	return $this;
	//}
}