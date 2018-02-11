<?php


namespace IPS\vpdb\modules\front\releases;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/Parsedown.php');

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * Retrieves release details from VPDB and displays them.
 */
class _view extends \IPS\Content\Controller
{

	/**
	 * [Content\Controller]	Class
	 */
	protected static $contentModel = 'IPS\vpdb\Release';

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
	 * @var \IPS\vpdb\Vpdb\_Api
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
		$this->api = \IPS\vpdb\Vpdb\Api::getInstance();
		$this->Parsedown = new \Parsedown();

		try {
			$release = $this->api->getReleaseDetails(\IPS\Request::i()->releaseId, ['thumb_flavor' => 'orientation:fs', 'thumb_format' => 'medium']);
			$release->description = $this->Parsedown->text($release->description);

			// download link
			$release->download = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=download&releaseId=' . $release->id . '&gameId=' . $release->game->id);

			/* Display */
			\IPS\Output::i()->title = $release->game->title . ' - ' . $release->name;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->view($release, $release->item->renderComments());

		} catch (\IPS\vpdb\Vpdb\ApiException $e) {
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->apiError($e);
		}
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}