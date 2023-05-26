<?php

namespace Madeorsk\Forwarded\Tests;

use Madeorsk\Forwarded\Parser;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
	/**
	 * The first example of Section 7.5 of RFC 7239.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-7.5
	 * @return void
	 */
	public function testSimpleForward(): void
	{
		$forwarded = (new Parser())->parse("for=192.0.2.43");

		$this->assertSame(1, count($forwarded->getForwards()));
		$this->assertSame("192.0.2.43", $forwarded->first()->for()->getIp());
		$this->assertNull($forwarded->first()->by());
		$this->assertNull($forwarded->first()->host());
		$this->assertNull($forwarded->first()->protocol());
	}

	/**
	 * The example of Section 7.1 of RFC 7239.
	 * @see https://datatracker.ietf.org/doc/html/rfc7239#section-7.1
	 * @return void
	 */
	public function testExample71(): void
	{
		$forwarded = (new Parser())->parse("for=192.0.2.43,for=\"[2001:db8:cafe::17]\",for=unknown");

		$this->assertSame(3, count($forwarded->getForwards()));
		$this->assertSame("192.0.2.43", $forwarded->first()->for()->getIp());
		$this->assertSame("2001:db8:cafe::17", $forwarded->getForwards()[1]->for()->getIp());
		$this->assertTrue($forwarded->getForwards()[2]->for()->isUnknown());
	}

	public function testEverything(): void
	{
		$forwarded = (new Parser())->parse("for=192.0.2.43:55423;proto=http;host=test.dev;by=unknown,for=_something; by=unknown, for=\"[2001:db8:cafe::17]:22\";host=another.test;by=172.55.10.10,for=unknown");

		// Testing that we read enough forwards.
		$this->assertSame(4, count($forwarded->getForwards()));

		// Testing the first forward.
		$forward = $forwarded->getForwards()[0];
		$this->assertTrue($forward->for()->isIP());
		$this->assertTrue($forward->for()->isV4());
		$this->assertSame("192.0.2.43", $forward->for()->getIp());
		$this->assertSame(55423, $forward->for()->getPort());
		$this->assertTrue($forward->by()->isUnknown());
		$this->assertSame("test.dev", $forward->host());
		$this->assertSame("http", $forward->protocol());

		// Testing the second forward.
		$forward = $forwarded->getForwards()[1];
		$this->assertTrue($forward->for()->isIdentifier());
		$this->assertSame("something", $forward->for()->getIdentifier());
		$this->assertTrue($forward->by()->isUnknown());
		$this->assertNull($forward->host());
		$this->assertNull($forward->protocol());

		// Testing the third forward.
		$forward = $forwarded->getForwards()[2];
		$this->assertTrue($forward->for()->isIP());
		$this->assertTrue($forward->for()->isV6());
		$this->assertSame("2001:db8:cafe::17", $forward->for()->getIp());
		$this->assertSame(22, $forward->for()->getPort());
		$this->assertTrue($forward->by()->isIP());
		$this->assertTrue($forward->by()->isV4());
		$this->assertSame("172.55.10.10", $forward->by()->getIp());
		$this->assertNull($forward->by()->getPort());
		$this->assertSame("another.test", $forward->host());
		$this->assertNull($forward->protocol());

		// Testing the fourth forward.
		$forward = $forwarded->getForwards()[3];
		$this->assertTrue($forward->for()->isUnknown());
		$this->assertNull($forward->by());
		$this->assertNull($forward->host());
		$this->assertNull($forward->protocol());
	}
}
