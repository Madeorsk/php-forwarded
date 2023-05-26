<?php

namespace Madeorsk\Forwarded;

/**
 * An enumeration of the possible types of nodes as defined in section 6 of RFC7239.
 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-6
 */
enum ForwardNodeType: string
{
	/**
	 * In the case of a node that is an IPV4, as defined in section 6.1.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-6.1
	 */
	case IPV4 = "ipv4";
	/**
	 * In the case of a node that is an IPV6, as defined in section 6.1.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-6.1
	 */
	case IPV6 = "ipv6";
	/**
	 * In the case of a node that is "unknown", as defined in section 6.2.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-6.2
	 */
	case UNKNOWN = "unknown";
	/**
	 * In the case of a node that is an obfuscated identifier, as defined in section 6.3.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-6.3
	 */
	case IDENTIFIER = "identifier";
}
