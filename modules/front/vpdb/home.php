<?php


namespace IPS\vpdb\modules\front\vpdb;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * home
 */
class _home extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return    void
	 */
	protected function manage()
	{
		$api = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => "json",
			//'headers' => ['Authorization' => 'Bearer '.\IPS\Settings::i()->vpdb_app_key],
		]);

		$result = $api->get("/v1/releases", ["per_page" => 6, "sort" => "released_at", "thumb_format" => "square"]);

		$releases = $result->info->http_code == 200 ? $result->decode_response() : $result;

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('vpdb_page_title');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('home')->index($releases);
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}