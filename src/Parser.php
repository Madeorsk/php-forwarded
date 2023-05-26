<?php

namespace Madeorsk\Forwarded;

use Madeorsk\Forwarded\Exceptions\EmptyNodeNameException;

/**
 * The Forwarded header parser, following the section 4 of RFC 7239.
 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-4
 */
class Parser
{
	/**
	 * The currently reading forwards list.
	 * @var array
	 */
	protected array $forwards = [];
	/**
	 * An associative array (token => value) of the currently reading forward.
	 * @var array<string, string>
	 */
	protected array $pairs = [];
	/**
	 * The currently reading token.
	 * @var string
	 */
	protected string $token = "";
	/**
	 * The currently reading value.
	 * @var string
	 */
	protected string $value = "";
	/**
	 * The currently reading quoted string.
	 * @var string
	 */
	protected string $quotedString = "";
	/**
	 * The name of the currently reading value, which determine the variable to alter and the state function to use.
	 * @var string
	 */
	protected string $currentState = self::TOKEN_STATE;

	/**
	 * Parse the header content in an array of associative arrays with token => value.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-4 for token / value meaning.
	 * @param string $headerContent - The header content string.
	 * @return array<array<string, string>> - The parsed array of forwards [token => value] arrays.
	 */
	public function parseAssoc(string $headerContent): array
	{
		// Full reinitialization.
		$this->forwards = [];
		$this->pairs = [];
		$this->reinitPairParsing();

		foreach (mb_str_split($headerContent) as $char)
		{ // For each character, we first execute the state function.
			// The state function can change the state variable to the next state, so we store the current state in a local variable.
			$currentState = $this->currentState;
			if ($this->{"state".ucfirst($currentState)}($char))
				// If the state function returned true, then we add the current char to the current state.
				$this->{$currentState} .= $char;
		}

		if (!empty($this->token))
			// If the token is not empty, we save the current pair.
			$this->savePair();
		if (!empty($this->pairs))
			// If there are pairs, we save the current forward.
			$this->saveForward();

		return $this->forwards; // The parsing is finished, we can return the parsed forwards.
	}

	/**
	 * Parse the header content in a Forwarded header object.
	 * @param string $headerContent - The header content string.
	 * @return Forwarded - The parsed Forwarded header object.
	 * @throws EmptyNodeNameException
	 */
	public function parse(string $headerContent): Forwarded
	{
		return new Forwarded($this->parseAssoc($headerContent));
	}

	/**
	 * Parse the HTTP header found in `$_SERVER["HTTP_FORWARDED"]`.
	 * @return Forwarded - The parsed Forwarded header.
	 * @throws EmptyNodeNameException
	 */
	public function parseHttpHeader(): Forwarded
	{
		return $this->parse($_SERVER["HTTP_FORWARDED"]);
	}

	/**
	 * Reinitialize the parsing of a pair.
	 * @return void
	 */
	protected function reinitPairParsing(): void
	{
		$this->token = "";
		$this->value = "";
		$this->quotedString = "";
		$this->currentState = self::TOKEN_STATE;
	}

	/**
	 * Save the currently parsing pair and reinitialize parsing to read another one.
	 * @return void
	 */
	protected function savePair(): void
	{
		// There should be at least one filled value between `value` and `quotedString`.
		$this->pairs[trim($this->token)] = trim($this->value.$this->quotedString);
		$this->reinitPairParsing();
	}
	/**
	 * Save the currently parsing forward and reinitialize parsing to read another one.
	 * @return void
	 */
	protected function saveForward(): void
	{
		if (!empty($this->token))
			// If the token is not empty, we save the current pair.
			$this->savePair();

		$this->forwards[] = $this->pairs;
		$this->pairs = [];
		$this->reinitPairParsing();
	}


	/*
	 *
	 *  =*= STATES CONSTANTS AND FUNCTIONS =*=
	 *
	 */


	const TOKEN_STATE = "token";
	const VALUE_STATE = "value";
	const QUOTED_STRING_STATE = "quotedString";
	const QUOTED_STRING_ESCAPING_STATE = "quotedStringEscaping";

	/**
	 * The state function of token parsing.
	 * @param string $char - The currently reading character.
	 * @return bool - True if the current character should be added to the token, false otherwise.
	 */
	protected function stateToken(string $char): bool
	{
		switch ($char)
		{
			case "=":
				$this->currentState = self::VALUE_STATE;
				return false;

			default:
				return true;
		}
	}
	/**
	 * The state function of value parsing.
	 * @param string $char - The currently reading character.
	 * @return bool - True if the current character should be added to the value, false otherwise.
	 */
	protected function stateValue(string $char): bool
	{
		if (empty($this->value))
		{ // If there is an empty value, we check if this value can be a quoted string.
			if ($char == "\"")
			{ // The value starts with a quote, it is a quoted string.
				$this->currentState = self::QUOTED_STRING_STATE;
				return false;
			}
		}

		switch ($char)
		{
			case ";":
				$this->savePair();
				return false;
			case ",":
				$this->saveForward();
				return false;

			default:
				return true;
		}
	}
	/**
	 * The state function of quoted string parsing.
	 * @param string $char - The currently reading character.
	 * @return bool - True if the current character should be added to the value, false otherwise.
	 */
	protected function stateQuotedString(string $char): bool
	{
		switch ($char)
		{
			case "\"":
				$this->currentState = self::VALUE_STATE;
				return false;
			case "\\":
				$this->currentState = self::QUOTED_STRING_ESCAPING_STATE;
				return false;

			default:
				return true;
		}
	}
	/**
	 * The state function of "quoted string while escaping" parsing.
	 * @param string $char - The currently reading character.
	 * @return bool - True if the current character should be added to the value, false otherwise.
	 */
	protected function stateQuotedStringEscaping(string $char): bool
	{
		$this->currentState = self::QUOTED_STRING_STATE;
		return true;
	}
}
