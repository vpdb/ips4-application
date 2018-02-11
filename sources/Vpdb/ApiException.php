<?php

namespace IPS\vpdb\Vpdb;

/**
 * Thrown when the backend doesn't return what we expect it to return.
 */
class _ApiException extends \Exception
{
	public function __construct(\RestClient $result)
	{
		parent::__construct('Error ' . $result->code . '.');
	}
}