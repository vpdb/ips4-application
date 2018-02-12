<?php

namespace IPS\vpdb\modules\front\releases;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * downloadRelease
 */
class _download extends \IPS\Dispatcher\Controller
{
	protected $flavors;

	/**
	 * @var \IPS\vpdb\Vpdb\_Api
	 */
	protected $api;

	/**
	 * @var \IPS\vpdb\Vpdb\_Storage
	 */
	protected $storage;

	/**
	 * Constructor
	 *
	 * @param    \IPS\Http\Url|NULL $url The base URL for this controller or NULL to calculate automatically
	 * @return    void
	 */
	public function __construct($url = NULL)
	{
		parent::__construct($url);
		$this->api = \IPS\vpdb\Vpdb\Api::getInstance();
		$this->storage = \IPS\vpdb\Vpdb\Storage::getInstance();
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
		$releaseId = \IPS\Request::i()->releaseId;
		$gameId = \IPS\Request::i()->gameId;

		try {
			$release = $this->api->getReleaseDetails($releaseId, ['thumb_flavor' => 'orientation:fs', 'thumb_format' => 'medium']);
			$roms = $this->api->getRoms($gameId);
			$action = $release->url = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=download&do=prepareDownload&releaseId=' . $releaseId . '&gameId=' . $gameId);

			// retrieve game media
			$addedCategories = [];
			$gameMedia = [];
			foreach ($release->game->media as $medium) {
				if (!in_array($medium->category, $addedCategories)) {
					$gameMedia[] = $medium->id;
				}
				$addedCategories[] = $medium->category;
			};

			// render
			\IPS\Output::i()->title = $release->game->title . ' - ' . $release->name;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->download($release, $roms, $gameMedia, \IPS\vpdb\Vpdb\Api::$flavors, $action);

		} catch (\IPS\vpdb\Vpdb\ApiException $e) {
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->apiError($e);
		}
	}

	protected function prepareDownload()
	{
		$releaseId = \IPS\Request::i()->releaseId;
		$gameId = \IPS\Request::i()->gameId;
		$releaseUrl = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=view&releaseId=' . $releaseId . '&gameId=' . $gameId);

		// check if the user is logged, if not, redirect to login screen


		try {
			// check if the user is on vpdb, if not, redirect to vpdb register screen
			$user = $this->api->getUserProfile();
			if ($user) {
				$downloadUrl = \IPS\Settings::i()->vpdb_url_storage . '/v1/releases/' . $releaseId;
				$auth = $this->storage->authenticate($downloadUrl);

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

				$fullUrl = $downloadUrl .
					'?body=' . urlencode(json_encode($download)) .
					'&token=' . $auth->$downloadUrl .
					'&save_as=1';

				$err = $this->storage->checkDownload($fullUrl);
				if (!$err) {
					\IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('front_download.js', 'vpdb'));
					\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->download($downloadUrl, json_encode($download), $auth->$downloadUrl, '1', $releaseUrl);

				} else {
					\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->downloadError($err, $releaseUrl);
				}

			} else {
				// otherwise, redirect to vpdb backend with download link
				$continue = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=download&do=register&releaseId=' . $releaseId . '&gameId=' . $gameId);
				\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->register($releaseUrl, $continue);
			}


		} catch (\IPS\vpdb\Vpdb\ApiException $e) {
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->apiError($e);
		}
	}

	protected function register()
	{

	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}