<?php

namespace IPS\vpdb\Vpdb;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

/**
 * A singleton class that interfaces with the VPDB storage API.
 */
class _Storage
{
	/**
	 * @var _Storage
	 */
	protected static $instance;

	/**
	 * Rest client, authenticated with a provider token.
	 * @var \RestClient
	 */
	protected $client;

	/**
	 * Rest client for anonymous requests. Needs entire URLs incl host.
	 * @var \RestClient
	 */
	protected $anonClient;

	/**
	 * Api constructor.
	 */
	protected function __construct()
	{
		$this->client = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_storage,
			'format' => 'json',
			'headers' => ['Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key],
		]);

		$this->anonClient = new \RestClient();
	}

	/**
	 * Returns a storage authentication token
	 *
	 * @param $downloadUrl string Path to authenticate with
	 * @return mixed
	 * @throws \RestClientException
	 */
	public function authenticate($downloadUrl)
	{
		$result = $this->client->post('/v1/authenticate', json_encode(['paths' => $downloadUrl]), $this->getUserHeader(['Content-Type' => 'application/json']));
		if ($result->info->http_code != 200) {
			throw new \IPS\vpdb\Vpdb\ApiException($result);
		}
		return $result->decode_response();
	}

	/**
	 * Checks if a download URL is valid.
	 *
	 * @param $fullUrl string Full URL incl. domain name
	 * @return string|null Error message or null on success
	 */
	public function checkDownload($fullUrl) {
		$result = $this->anonClient->execute($fullUrl, 'HEAD');
		if ($result->info->http_code == 200) {
			return null;
		} else {
			return $result->headers->x_error;
		}
	}


	/**
	 * Returns the singleton instance of this class.
	 * @return _Storage
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new Storage();
		}
		return self::$instance;
	}

	protected function getUserHeader($headers = [])
	{
		$userHeader = \IPS\Member::loggedIn()->member_id ? ['X-User-Id' => \IPS\Member::loggedIn()->member_id] : [];
		return array_merge($headers, $userHeader);
	}

}