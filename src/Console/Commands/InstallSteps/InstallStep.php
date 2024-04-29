<?php
namespace AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps;

use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\Console;
use AntonioPrimera\LaravelJsLocalization\Console\Commands\InstallSteps\Helpers\Steps;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\pause;

abstract class InstallStep
{
	const STATUS_SUCCESS = 'success';
	const STATUS_ERROR = 'error';
	const STATUS_SKIPPED = 'skipped';
	const STATUS_SKIPPED_USER = 'skipped:user';
	const STATUS_SKIPPED_NOT_NEEDED = 'skipped:not-needed';
	
	public function __construct(public Steps $steps){}
	
	public static function run(int $stepNumber, int $totalSteps, Steps $steps): static
	{
		return (new static($steps))->handleRun($stepNumber, $totalSteps);
	}
	
	protected function handleRun(int $stepNumber, int $totalSteps): static
	{
		Console::instance()
			->newLine()
			->line("<options=bold;fg=bright-white>[Step {$stepNumber}/{$totalSteps}]: <options=underscore;fg=bright-white>{$this->stepDescription()}</></>");
		
		$this->outputResultInfo($this->handle());	//handle the step and output the result info
		
		return $this;
	}
	
	//--- Abstract methods --------------------------------------------------------------------------------------------
	
	protected abstract function handle(): array|string|null;
	protected abstract function stepDescription(): string;
	
	//--- Generic helpers ---------------------------------------------------------------------------------------------
	
	protected function packageRootPath(string $relativePath): string
	{
		return __DIR__ . "/../../../../$relativePath";
	}
	
	//--- IO helpers --------------------------------------------------------------------------------------------------
	
	protected function outputResultInfo(array|string|null $result): void
	{
		$message = is_array($result) ? ($result['message'] ?? null) : null;
		$status = is_array($result) ? ($result['status'] ?? '') : $result;
		$console = Console::instance();
		
		match ($status) {
			self::STATUS_SUCCESS => $console->info($message ?? 'Step completed successfully!'),
			self::STATUS_ERROR => $console->error($message ?? 'An error occurred during the step! Please check the output above.'),
			self::STATUS_SKIPPED => $console->line('<fg=gray>' . ($message ?? 'Step skipped. No changes were made.') . '</>'),
			self::STATUS_SKIPPED_USER => $console->line('<fg=gray>' . ($message ?? 'Step skipped by user. No changes were made.') . '</>'),
			self::STATUS_SKIPPED_NOT_NEEDED => $console->line( '<fg=gray>' . ($message ?? 'Step skipped because it was not needed. No changes were made.') . '</>'),
			default => $console->line($message ?? $result ?? ''),
		};
	}
	
	protected function confirm(string $question, bool $default, string $hint = ''): bool
	{
		return windows_os()
			? Console::instance()->confirm($question, $default)
			: confirm(label: $question, default: $default, hint: $hint);
	}
	
	protected function pause(string $message, string $confirmMessage = ''): void
	{
		windows_os()
			? Console::instance()->confirm($confirmMessage ?: $message, true)
			: pause($message);
	}
	
	//--- Status helpers ----------------------------------------------------------------------------------------------
	
	protected function success(string|null $message = null): array
	{
		return [
			'status' => self::STATUS_SUCCESS,
			'message' => $message,
		];
	}
	
	protected function failed(string|null $message = null): array
	{
		return [
			'status' => self::STATUS_ERROR,
			'message' => $message,
		];
	}
	
	protected function skipped(string|null $message = null): array
	{
		return [
			'status' => self::STATUS_SKIPPED,
			'message' => $message,
		];
	}
	
	protected function skippedByUser(string|null $message = null): array
	{
		return [
			'status' => self::STATUS_SKIPPED_USER,
			'message' => $message,
		];
	}
	
	protected function skippedNotNeeded(string|null $message = null): array
	{
		return [
			'status' => self::STATUS_SKIPPED_NOT_NEEDED,
			'message' => $message,
		];
	}
}