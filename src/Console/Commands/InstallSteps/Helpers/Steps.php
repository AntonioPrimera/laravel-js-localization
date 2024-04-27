<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers;

use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\InstallStep;

class Steps
{
	protected array $steps = [];
	
	public function __construct(array $steps = [])
	{
		//validate and add each step
		foreach ($steps as $stepClassName)
			$this->add($stepClassName);
	}
	
	public static function create(array $steps = []): static
	{
		return new static($steps);
	}
	
	public function add(string $stepClassName): static
	{
		if (class_exists($stepClassName) && is_subclass_of($stepClassName, InstallStep::class))
			$this->steps[$stepClassName] = null;
		
		return $this;
	}
	
	public function run(): void
	{
		$stepsCount = count($this->steps);
		$index = 1;
		
		foreach ($this->steps as $stepClassName => $stepInstance) {
			$this->runStep($stepClassName, $index, $stepsCount);
			$index++;
		}
	}
	
	public function runStep(string $stepClassName, int $stepNumber, int $totalSteps): static
	{
		if ($this->steps[$stepClassName] !== null)
			return $this;
		
		$this->steps[$stepClassName] = call_user_func([$stepClassName, 'run'], $stepNumber, $totalSteps, $this);
		return $this;
	}
	
	public function getStep(string $stepClassName): InstallStep|null
	{
		return $this->steps[$stepClassName] ?? null;
	}
}