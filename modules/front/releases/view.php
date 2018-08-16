<?php


namespace IPS\vpdb\modules\front\releases;

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

		try {
			$release = $this->api->getReleaseDetails(\IPS\Request::i()->releaseId, ['thumb_flavor' => 'orientation:fs', 'thumb_format' => 'medium']);

			// rating
			if (\IPS\Member::loggedIn()->member_id) {
				$rating = $this->api->getReleaseRating(\IPS\Request::i()->releaseId);
			} else {
				$rating = null;
			}

			// download link
			$release->download = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=download&releaseId=' . $release->id . '&gameId=' . $release->game->id);

			// flavor grid
			$flavors = [];
			foreach ($release->versions as $version) {
				foreach ($version->files as $versionFile) {
					if ($versionFile->flavor) {
						$versionFile->version = $version;
						$flavors[] = $versionFile;
					}
				}
			}
			$flavorGrid = [];
			foreach ($flavors as $file) {
				$compat = array_column($file->compatibility, 'id');
				sort($compat);
				$flavor = '';
				$flavorKeys = get_object_vars($file->flavor);
				sort($flavorKeys);
				foreach($flavorKeys as $key) {
					$flavor .= $key . ':' . $file->flavor->$key . ',';
				}
				$key = implode('/', $compat) . '-' . $flavor;
				$short = $file->flavor->orientation === 'any' && $file->flavor->lighting === 'any'
					? 'Universal'
					: \IPS\vpdb\Vpdb\Api::$flavors['orientation']['values'][$file->flavor->orientation]['short'] . ' / ' . \IPS\vpdb\Vpdb\Api::$flavors['lighting']['values'][$file->flavor->lighting]['name'];

				$flavorGrid[$key] = [
					'file' => $file,
					'orientation' => \IPS\vpdb\Vpdb\Api::$flavors['orientation']['values'][$file->flavor->orientation],
					'lighting' => \IPS\vpdb\Vpdb\Api::$flavors['lighting']['values'][$file->flavor->lighting],
					'version' => $file->version,
					'short' => $short
				];
			}

			/* Display */
			\IPS\Output::i()->title = $release->game->title . ' - ' . $release->name;
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->view($release, $rating, $flavorGrid, $release->item->renderComments(), !!\IPS\Member::loggedIn()->member_id);

		} catch (\IPS\vpdb\Vpdb\ApiException $e) {
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->apiError($e);
		}
	}

	protected function rate() {

	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}