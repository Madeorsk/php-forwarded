<?php

namespace Madeorsk\Forwarded;

use Madeorsk\Forwarded\Exceptions\EmptyNodeNameException;

/**
 * The parsed Forwarded header.
 */
class Forwarded
{
	/**
	 * @var Forward[]
	 */
	protected array $forwards;

	/**
	 * Create a new Forwarded parsed header.
	 * @param array<(array<string, string>|Forward)> $forwards - An array of forwards.
	 * @throws EmptyNodeNameException
	 */
	public function __construct(array $forwards = [])
	{
		$this->loadForwards($forwards);
	}

	/**
	 * Load the given forwards array.
	 * If the forwards array is an array of associative arrays, converting them as Forward objects.
	 * @param array<(array<string, string>|Forward)> $forwards - An array of forwards.
	 * @return void
	 * @throws EmptyNodeNameException
	 */
	public function loadForwards(array $forwards): void
	{
		if (!empty($forwards))
		{ // If some forwards have been given, we read them and save them.
			$forwards = array_values($forwards); // Getting a simple forwards array with no index.
			if ($forwards[0] instanceof Forward)
				// If we have an array of Forward objects, we can simply save it.
				$this->forwards = $forwards;
			else
				// If we have an array of associative arrays for each forward,
				// we convert each forward associative array to a Forward object before to save the array.
				$this->forwards = array_map(function (array $assoc) {
					// Converting the current associative array as a Forward object.
					return new Forward($assoc);
				}, $forwards);
		}
		// If there are no forwards, reset to an empty array.
		else $this->forwards = [];
	}

	/**
	 * Get the first forward, if there is one.
	 * @return Forward|null - If NULL, there are no forward.
	 */
	public function first(): ?Forward
	{
		return !empty($this->forwards) ? $this->forwards[0] : null;
	}

	/**
	 * Get the forwards list.
	 * @return Forward[] - The forwards list.
	 */
	public function getForwards(): array
	{
		return $this->forwards;
	}
}
