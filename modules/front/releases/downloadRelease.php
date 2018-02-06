<?php

namespace IPS\vpdb\modules\front\releases;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * downloadRelease
 */
class _downloadRelease extends \IPS\Dispatcher\Controller
{
	protected $flavors;

	/**
	 * Constructor
	 *
	 * @param    \IPS\Http\Url|NULL $url The base URL for this controller or NULL to calculate automatically
	 * @return    void
	 */
	public function __construct($url = NULL)
	{
		parent::__construct($url);
		$this->flavors = array(
			'orientation' => array(
				'header' => 'Orientation',
				'name' => 'orientation',
				'values' => array(
					'ws' => array('name' => 'Desktop', 'other' => 'Landscape', 'value' => 'ws', 'short' => 'DT'),
					'fs' => array('name' => 'Cabinet', 'other' => 'Portrait', 'value' => 'fs', 'short' => 'FS'),
					'any' => array('name' => 'Universal', 'other' => 'Any Orientation', 'value' => 'any', 'short' => 'Universal')
				)
			),
			'lighting' => array(
				'header' => 'Lighting',
				'name' => 'lighting',
				'values' => array(
					'night' => array('name' => 'Night', 'other' => 'Dark Playfield', 'value' => 'night'),
					'day' => array('name' => 'Day', 'other' => 'Illuminated Playfield', 'value' => 'day'),
					'any' => array('name' => 'Universal', 'other' => 'Customizable', 'value' => 'any')
				)
			)
		);
	}


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
		$this->api = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => 'json',
			'headers' => ['Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key],
		]);

		$this->releaseId = \IPS\Request::i()->id;
		$this->gameId = \IPS\Request::i()->gameId;

		$rlsResult = $this->api->get("/v1/releases/" . $this->releaseId, ['thumb_flavor' => 'orientation:fs', 'thumb_format' => 'medium']);
		$gameResult = $this->api->get("/v1/games/" . $this->gameId);
		$romResult = $this->api->get("/v1/games/" . $this->gameId . '/roms');
		if ($rlsResult->info->http_code == 200 && $gameResult->info->http_code == 200 && $romResult->info->http_code == 200) {
			$release = $rlsResult->decode_response();
			$game = $gameResult->decode_response();
			$roms = $romResult->decode_response();

			// add IPS member data
			foreach ($release->authors as $author) {
				if ($author->user->provider_id) {
					$author->user->member = \IPS\Member::load($author->user->provider_id);
				}
			}

			/* Display */
			\IPS\Output::i()->title = $release->game->title . ' - ' . $release->name;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->download($release, $game, $roms, $this->flavors);
		}
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}