<?php

namespace Madeorsk\Forwarded\Exceptions;

/**
 * Exception thrown when trying to guess the type on an empty node name.
 */
class EmptyNodeNameException extends \Exception
{
	public function __construct()
	{
		parent::__construct("Empty node name while guessing type of the ForwardNode.");
	}
}
