<?php

namespace IPS\vpdb\Vpdb;

/**
 * Thrown when the backend doesn't return what we expect it to return.
 */
class _ApiException extends \Exception
{
	protected $body;

	public function __construct(\RestClient $result)
	{
		parent::__construct('Error ' . $result->code . '.');
		try {
			$this->body = $result->decode_response();
		} catch (\RestClientException $e) {
			$this->body = new \stdClass();
		}
	}

	public function getError() {
		return $this->body->error;
	}

}