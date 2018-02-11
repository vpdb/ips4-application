<?php

namespace IPS\vpdb\Vpdb;

/**
 * Thrown when the backend doesn't return what we expect it to return.
 */
class _ApiException extends \Exception
{
	public function __construct($code, $body)
	{
		parent::__construct('Error ' . $code . '.');
	}
}