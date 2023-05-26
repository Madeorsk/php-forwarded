<?php

namespace Madeorsk\Forwarded;

use Madeorsk\Forwarded\Exceptions\EmptyNodeNameException;
use Madeorsk\Forwarded\Exceptions\NotIpAddressException;

/**
 * The class of an interface that emitted or received the request (by / for).
 * It is defined in section 6 of RFC 7239.
 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-6
 */
class ForwardNode
{
	/**
	 * The raw node name.
	 * @var string
	 */
	protected string $nodeName;

	/**
	 * The type of the node.
	 * @var ForwardNodeType
	 */
	protected ForwardNodeType $nodeType;

	/**
	 * Create a new forward interface class from a raw node name.
	 * @param string $nodeName - The raw node name.
	 * @throws EmptyNodeNameException
	 */
	public function __construct(string $nodeName)
	{
		$this->setNodeName($nodeName);
	}

	/**
	 * Set the node name.
	 * @param string $nodeName - The new node name.
	 * @return void
	 * @throws EmptyNodeNameException
	 */
	public function setNodeName(string $nodeName): void
	{
		$this->nodeName = $nodeName;
		// After setting the node name, we should update the current node type.
		$this->guessType();
	}

	/**
	 * Guess the node type based on the current node name.
	 * @return ForwardNodeType - The updated node type.
	 * @throws EmptyNodeNameException
	 */
	protected function guessType(): ForwardNodeType
	{
		if (empty($this->nodeName)) throw new EmptyNodeNameException();

		if ($this->nodeName[0] == "_")
			// If the node name starts with '_', it is an obfuscated identifier: https://datatracker.ietf.org/doc/html/rfc7239#section-6.3.
			$this->nodeType = ForwardNodeType::IDENTIFIER;
		elseif ($this->nodeName == "unknown")
			// If the node name is "unknown", it is a special node from an unknown interface: https://datatracker.ietf.org/doc/html/rfc7239#section-6.2.
			$this->nodeType = ForwardNodeType::UNKNOWN;
		elseif ($this->nodeName[0] == "[")
			// If the node starts with '[', it is an IPV6: https://datatracker.ietf.org/doc/html/rfc7239#section-6.1.
			$this->nodeType = ForwardNodeType::IPV6;
		else
			// If nothing was true, it can only be an IPV4.
			$this->nodeType = ForwardNodeType::IPV4;

		return $this->nodeType; // Returning the updated node type.
	}

	/**
	 * Determine if the current node interface is an IP address.
	 * @return bool - True if it is an IP address, false otherwise.
	 */
	public function isIP(): bool
	{
		return in_array($this->nodeType, [ForwardNodeType::IPV4, ForwardNodeType::IPV6]);
	}
	/**
	 * Determine if the current node interface is an IPV4 address.
	 * @return bool - True if it is an IPV4 address, false otherwise.
	 */
	public function isV4(): bool
	{
		return $this->nodeType == ForwardNodeType::IPV4;
	}
	/**
	 * Determine if the current node interface is an IPV6 address.
	 * @return bool - True if it is an IPV6 address, false otherwise.
	 */
	public function isV6(): bool
	{
		return $this->nodeType == ForwardNodeType::IPV6;
	}

	/**
	 * Get the IP address from the node name.
	 * @return string - The IP address.
	 * @throws NotIpAddressException
	 */
	public function getIp(): string
	{
		if ($this->isV4())
			return $this->getIpv4();
		if ($this->isV6())
			return $this->getIpv6();

		throw new NotIpAddressException($this->nodeName);
	}
	/**
	 * Get the IPV4 address from the node name.
	 * @return string - The IPV4 address.
	 */
	public function getIpv4(): string
	{
		// We try to find the port separator character.
		$portSeparator = strrpos($this->nodeName, ":");

		return $portSeparator !== false
			// If there is a port separator character, we cut the string to keep only the IP address.
			? substr($this->nodeName, 0, $portSeparator)
			// If there is no port separator character, we can return the whole node name that should contain only the IP address.
			: $this->nodeName;
	}
	/**
	 * Get the IPV6 address from the node name.
	 * @return string - The IPV6 address.
	 */
	public function getIpv6(): string
	{
		return substr($this->nodeName, 1,
			// Finding the end of the IPV6 address. It is always enclosed in square brackets, according RFC 7239: https://datatracker.ietf.org/doc/html/rfc7239#section-6.1.
			strrpos($this->nodeName, "]") - 1);
	}

	/**
	 * Get the used port from the node name.
	 * @return int|null - The used port, if there is one specified.
	 */
	public function getPort(): ?int
	{
		// We try to find the port separator character.
		$portSeparator = strrpos($this->nodeName, ":",
			// In an IPV6 address, the port separator can only be found after the end of the IP address (always enclosed in square brackets, according RFC 7239: https://datatracker.ietf.org/doc/html/rfc7239#section-6.1).
			($ipv6EndPos = strrpos($this->nodeName, "]")) !== false ? $ipv6EndPos : 0);

		// If we found a port separator, there is one and we can return it.
		return $portSeparator !== false ? intval(substr($this->nodeName, $portSeparator + 1)) : null;
	}

	/**
	 * Determine if the current node interface is unknown.
	 * @return bool - True if it is unknown, false otherwise.
	 */
	public function isUnknown(): bool
	{
		return $this->nodeType == ForwardNodeType::UNKNOWN;
	}

	/**
	 * Determine if the current node interface is an obfuscated identifier.
	 * @return bool - True if it is an obfuscated identifier, false otherwise.
	 */
	public function isIdentifier(): bool
	{
		return $this->nodeType == ForwardNodeType::IDENTIFIER;
	}

	/**
	 * Get the current obfuscated identifier without the leading '_'.
	 * @return string - The identifier, without the leading '_'.
	 */
	public function getIdentifier(): string
	{
		return substr($this->nodeName, 1);
	}
}
