<?php


namespace IPS\vpdb\modules\front\releases;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');
include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/Parsedown.php');

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * Retrieves release details from VPDB and displays them.
 */
class _viewRelease extends \IPS\Dispatcher\Controller
{
	/**
	 * ID of the release at VPDB
	 * @var string
	 */
	protected $releaseId;

	/**
	 * ID of the release's game at VPDB
	 * @var string
	 */
	protected $gameId;

	/**
	 * The VPDB Client
	 * @var RestClient
	 */
	protected $api;

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
	 * Default action
	 *
	 * @return    void
	 * @throws \RestClientException
	 */
	protected function manage()
	{
		$this->Parsedown = new \Parsedown();
		$this->api = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => 'json',
			'headers' => ['Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key],
		]);

		$this->releaseId = \IPS\Request::i()->id;
		$this->gameId = \IPS\Request::i()->gameId;

		$rlsResult = $this->api->get("/v1/releases/" . $this->releaseId, ['thumb_flavor' => 'orientation:fs', 'thumb_format' => 'medium']);
		$gameResult = $this->api->get("/v1/games/" . $this->gameId);
		if ($rlsResult->info->http_code == 200 && $gameResult->info->http_code == 200) {

			$release = $rlsResult->decode_response();
			$game = $gameResult->decode_response();

			$releaseObj = new \IPS\vpdb\Release($release);

			// description as markdown
			$release->description = $this->Parsedown->text($release->description);

			// download link
			$release->download = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=downloadRelease&id=' . $release->id . '&gameId=' . $release->game->id);

			// add IPS member data
			foreach ($release->authors as $author) {
				if ($author->user->provider_id) {
					$author->user->member = \IPS\Member::load($author->user->provider_id);
				}
			}
			/* Display */
			\IPS\Output::i()->title = $release->game->title . ' - ' . $release->name;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->view($release, $game, $releaseObj->renderComments());
		}


	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}