<?php
namespace Foodo;

/* Restaurant row is already exists on DB
** @GET
** @int $rest_id - given rest data ID on website
*/
class restAlreadyExists extends \Exception
{

	public $rest_id;

	public function __construct($rest_id)
	{
		$this->rest_id = $rest_id;
	}

	public function getError()
	{
		echo sprintf('This rest is already exists on db (ID == %s)',$this->rest_id);
	}

}

/* Scraping url failure
** @GET
** @string $url - url which cannot be opened
*/
class cantScrapeUrl extends \Exception
{

	public function __construct($url)
	{
		$this->message = sprintf("Cannot scrape '%s', validate the url and try again.",$url);
	}

}

/* Bad inputs exception
** @GET
** @string $input - input value
** @string $name - input name
*/
class badInputException extends \Exception
{

	public function __construct($input,$name)
	{
		$this->message = sprintf("invalid value for %s ('%s')",$name,$input);
	}

}
?>
