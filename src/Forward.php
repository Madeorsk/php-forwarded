<?php

namespace Madeorsk\Forwarded;

use Madeorsk\Forwarded\Exceptions\EmptyNodeNameException;

/**
 * Class of an element in the forwarded elements list as defined in RFC7239.
 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5
 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-7.1
 */
class Forward
{
	/**
	 * Identifies the user-agent facing interface of the proxy.
	 * @var ForwardNode|null
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.1
	 */
	protected ?ForwardNode $by = null;

	/**
	 * Identifies the node making the request to the proxy.
	 * @var ForwardNode|null
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.2
	 */
	protected ?ForwardNode $for = null;

	/**
	 * The host request header field as received by the proxy.
	 * @var string|null
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.3
	 */
	protected ?string $host = null;

	/**
	 * Indicates what protocol was used to make the request.
	 * @var string|null
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.4
	 */
	protected ?string $protocol = null;

	/**
	 * Create a new Forward.
	 * @param array<string, string> $assoc - The associative array of token-value pairs in the forward.
	 * @throws EmptyNodeNameException
	 */
	public function __construct(array $assoc = [])
	{
		// Load the given associative array.
		$this->loadAssoc($assoc);
	}

	/**
	 * Load the forward data from its token-value pairs.
	 * @param array<string, string> $assoc - The associative array of token-value pairs in the forward.
	 * @return void
	 * @throws EmptyNodeNameException
	 */
	public function loadAssoc(array $assoc = []): void
	{
		// Read the interfaces, if they are specified.
		if (!empty($assoc["by"]))
			$this->by = new ForwardNode($assoc["by"]);
		if (!empty($assoc["for"]))
			$this->for = new ForwardNode($assoc["for"]);

		// Store the host, if it is specified.
		if (!empty($assoc["host"]))
			$this->host = $assoc["host"];
		// Store the protocol, if it is specified.
		if (!empty($assoc["proto"]))
			$this->protocol = $assoc["proto"];
	}


	/**
	 * The "by" parameter is used to disclose the interface where the
	 * request came in to the proxy server.
	 * This is primarily added by reverse proxies that wish to forward this
	 * information to the backend server.  It can also be interesting in a
	 * multihomed environment to signal to backend servers from which the
	 * request came.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.1
	 * @return ForwardNode|null - The node that identifies the interface, if there is one.
	 */
	public function by(): ?ForwardNode
	{
		return $this->by;
	}

	/**
	 * The "for" parameter is used to disclose information about the client
	 * that initiated the request and subsequent proxies in a chain of
	 * proxies.
	 * In a chain of proxy servers where this is fully utilized, the first
	 * "for" parameter will disclose the client where the request was first
	 * made, followed by any subsequent proxy identifiers.  The last proxy
	 * in the chain is not part of the list of "for" parameters.  The last
	 * proxy's IP address, and optionally a port number, are, however,
	 * readily available as the remote IP address at the transport layer.
	 * It can, however, be more relevant to read information about the last
	 * proxy from preceding "Forwarded" header field's "by" parameter, if
	 * present.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.2
	 * @return ForwardNode|null - The node that identifies the interface, if there is one.
	 */
	public function for(): ?ForwardNode
	{
		return $this->for;
	}

	/**
	 * The "host" parameter is used to forward the original value of the
	 * "Host" header field.  This can be used, for example, by the origin
	 * server if a reverse proxy is rewriting the "Host" header field to
	 * some internal host name.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.3
	 * @return string|null - The original host, if there is one.
	 */
	public function host(): ?string
	{
		return $this->host;
	}

	/**
	 * The "proto" parameter has the value of the used protocol type.
	 * For example, in an environment where a reverse proxy is also used as
	 * a crypto offloader, this allows the origin server to rewrite URLs in
	 * a document to match the type of connection as the user agent
	 * requested, even though all connections to the origin server are
	 * unencrypted HTTP.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-5.4
	 * @return string|null - The original protocol, if there is one.
	 */
	public function protocol(): ?string
	{
		return $this->protocol;
	}
}
