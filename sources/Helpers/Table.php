<?php

namespace IPS\vpdb\Helpers;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

abstract class _Table extends \IPS\Helpers\Table\Table
{
	/**
	 * VPDB client
	 * @var RestClient
	 */
	protected $api;

	/**
	 * _Table constructor.
	 */
	public function __construct(\IPS\Http\Url $baseUrl)
	{
		parent::__construct($baseUrl);
		$this->api = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => 'json',
			'headers' => ['Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key],
		]);
	}
}