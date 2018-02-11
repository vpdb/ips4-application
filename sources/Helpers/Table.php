<?php

namespace IPS\vpdb\Helpers;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

abstract class _Table extends \IPS\Helpers\Table\Table
{
	/**
	 * VPDB client
	 * @var \IPS\vpdb\Vpdb\_Api
	 */
	protected $api;

	/**
	 * _Table constructor.
	 */
	public function __construct(\IPS\Http\Url $baseUrl)
	{
		parent::__construct($baseUrl);
		$this->api = \IPS\vpdb\Vpdb\Api::getInstance();
	}
}