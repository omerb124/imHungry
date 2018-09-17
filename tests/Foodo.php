<?php

namespace Foodo\Tests;

include_once '../vendor/autoload.php';

use Foodo\ZapRest;
use Foodo\cantScrapeUrl;

class Tests{

	public static function testAddCorrectRest()
	{
		$m = new ZapRest("8542");
		var_dump($m);
	}

	public static function testUnexistRest()
	{
		$m = new ZapRest("203423");
	}

	public static function testBadInputs()
	{
		$m = new ZapRest();
		$m = new ZapRest(true);
	}
}

Tests::testBadInputs();



