<?php declare(strict_types = 1);

namespace WebChemistry\Dotenv;

use Dotenv\Dotenv;
use LogicException;

final class DotenvLoader
{

	private bool $runtimeCheck = false;

	private function __construct(
		private string $path,
	)
	{
	}

	public function runtimeCheck(bool $value = true): self
	{
		$this->runtimeCheck = $value;

		return $this;
	}

	/**
	 * @return array<string, string>
	 */
	public function load(): array
	{
		/** @var array<string, string> $env */
		$env = Dotenv::createImmutable($this->path, ['.env', '.env.local'], false)
			->load();

		if ($this->runtimeCheck) {
			$this->checkValues($env);
		}

		return $env;
	}

	/**
	 * @param array<string, string> $env
	 */
	private function checkValues(array $env): void
	{
		$required = [];

		foreach ($env as $key => $value) {
			if ($value === 'REQUIRED') {
				$required[] = $key;
			}
		}

		if ($required) {
			throw new LogicException(sprintf('Missing required environment variables: %s', implode(', ', $required)));
		}
	}

	public static function create(string $path): self
	{
		return new self($path);
	}

	/**
	 * @param array<string, string> $env
	 * @return array<string, scalar|null>
	 */
	public static function parse(array $env): array
	{
		foreach ($env as $key => $value) {
			if ($value === 'true') {
				$env[$key] = true;
			} else if ($value === 'false') {
				$env[$key] = false;
			} else if (is_numeric($value)) {
				if (str_contains($value, '.')) {
					$env[$key] = (float) $value;
				} else {
					$env[$key] = (int) $value;
				}
			} else if ($value === 'null') {
				$env[$key] = null;
			}
		}

		return $env;
	}

}
