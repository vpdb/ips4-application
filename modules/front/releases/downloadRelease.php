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
	protected $releaseId;
	protected $gameId;

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
		$this->api = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => 'json',
			'headers' => [
				'X-User-Id' => \IPS\Member::loggedIn()->member_id,
				'Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key
			],
		]);
		$this->storageApi = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_storage,
			'format' => 'json',
			'headers' => [
				'X-User-Id' => \IPS\Member::loggedIn()->member_id,
				'Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key
			],
		]);
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
	 * Fetches release and game info to display which items to include in the download.
	 *
	 * @return    void
	 * @throws \RestClientException
	 */
	protected function manage()
	{
		$this->releaseId = \IPS\Request::i()->id;
		$this->gameId = \IPS\Request::i()->gameId;

		$rlsResult = $this->api->get('/v1/releases/' . $this->releaseId, ['thumb_flavor' => 'orientation:fs', 'thumb_format' => 'medium']);
		$gameResult = $this->api->get('/v1/games/' . $this->gameId);
		$romResult = $this->api->get('/v1/games/' . $this->gameId . '/roms');
		if ($rlsResult->info->http_code == 200 && $gameResult->info->http_code == 200 && $romResult->info->http_code == 200) {
			$release = $rlsResult->decode_response();
			$game = $gameResult->decode_response();
			$roms = $romResult->decode_response();
			$action = $release->url = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=downloadRelease&do=prepareDownload&id=' . $release->id . '&gameId=' . $game->id);

			// retrieve game media
			$addedCategories = [];
			$gameMedia = [];
			foreach ($game->media as $medium) {
				if (!in_array($medium->category, $addedCategories)) {
					$gameMedia[] = $medium->id;
				}
				$addedCategories[] = $medium->category;
			};

			// add IPS member data
			foreach ($release->authors as $author) {
				if ($author->user->provider_id) {
					$author->user->member = \IPS\Member::load($author->user->provider_id);
				}
			}

			/* Display */
			\IPS\Output::i()->title = $release->game->title . ' - ' . $release->name;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->download($release, $game, $roms, $gameMedia, $this->flavors, $action);
		}
	}

	protected function prepareDownload()
	{
		$this->releaseId = \IPS\Request::i()->id;
		$this->gameId = \IPS\Request::i()->gameId;
		$releaseUrl = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=viewRelease&id=' . $this->releaseId . '&gameId=' . $this->gameId);

		// check if the user is logged, if not, redirect to login screen

		// check if the user is on vpdb, if not, redirect to vpdb register screen
		$checkUser = $this->api->get('/v1/user');
		if ($checkUser->info->http_code == 200) {

			$downloadUrl = \IPS\Settings::i()->vpdb_url_storage . '/v1/releases/' . $this->releaseId;
			$authenticate = $this->storageApi->post('/v1/authenticate', json_encode(['paths' => $downloadUrl]), ['Content-Type' => 'application/json']);
			if ($authenticate->info->http_code == 200) {
				$download = [
					'files' => $_POST['tableFile'],
					'media' => [
						'playfield_image' => $_POST['includePlayfieldImage'],
						'playfield_video' => $_POST['includePlayfieldVideo'],
					],
					'roms' => $_POST['rom']
				];
				if ($_POST['includeGameMedia']) {
					$download['game_media'] = $_POST['media'];
				}
				$authBody = $authenticate->decode_response();

				$fullUrl = $downloadUrl .
					'?body=' . urlencode(json_encode($download)) .
					'&token=' . $authBody->$downloadUrl .
					'&save_as=1';

				$anon = new \RestClient(['base_url' => \IPS\Settings::i()->vpdb_url_storage]);
				$downloadCheck = $anon->execute($fullUrl, 'HEAD');
				if ($downloadCheck->info->http_code == 200) {
					\IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('front_download.js', 'vpdb'));
					\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->download($downloadUrl, json_encode($download), $authBody->$downloadUrl, '1', $releaseUrl);

				} else {
					\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->downloadError($downloadCheck->headers->x_error, $releaseUrl);
				}

			} else {
				\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->apiError($authenticate->response);
			}

		} else {
			// otherwise, redirect to vpdb backend with download link

			$continue = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=downloadRelease&do=register&id=' . $this->releaseId . '&gameId=' . $this->gameId);
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->register($releaseUrl, $continue);
		}
	}

	protected function register()
	{

	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}