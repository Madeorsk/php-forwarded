<?php

namespace Madeorsk\Forwarded\Exceptions;

/**
 * Exception thrown when the forward node interface is not an IP address and we are trying to get an IP address.
 */
class NotIpAddressException extends \Exception
{
	/**
	 * @param string $nodeName The node name of the forward node.
	 */
	public function __construct(public string $nodeName)
	{
		parent::__construct("This forward node with the node name \"$this->nodeName\" is not an IP address.");
	}
}
