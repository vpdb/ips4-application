<?php
/**
 * @brief        Table builder for a list of VPDB releases
 * @author       freezy@vpdb.io
 * @copyright    (c) VPDB
 * @license      https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 * @package      VPDB
 * @since        Jan 2018
 */

namespace IPS\vpdb\Helpers;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * List Table Builder using an \IPS\Content\Item class datasource
 */
class _TableReleases extends Table
{
	/**
	 * Initializes sort options.
	 *
	 * @param \IPS\Http\Url $baseUrl Base URL of the page the list table is on
	 */
	public function __construct(\IPS\Http\Url $baseUrl)
	{
		parent::__construct($baseUrl);
		$this->rowsTemplate = array(\IPS\Theme::i()->getTemplate('releases'), 'releaseRows');
		$this->sortOptions = array(
			'release_name' => 'title',
			'release_date' => 'released_at',
			'release_rating' => 'rating',
			'release_popularity' => 'popularity');

		if (!$this->sortBy) {
			$this->sortBy = 'released_at';
		}
		if (!$this->sortDirection) {
			$this->sortDirection = 'asc';
		}
	}

	/**
	 * Loads all releases from VPDB
	 *
	 * @param    array $advancedSearchValues Values from the advanced search form
	 * @return    array
	 */
	public function getRows($advancedSearchValues)
	{

		if (!$this->sortBy) {
			$this->sortBy = 'released_at';
		}
		$sortPrefix = $this->sortDirection == 'asc' ? '' : '-';

		$result = $this->api->get("/v1/releases", ["per_page" => 25, "sort" => $sortPrefix . $this->sortBy, "thumb_format" => "square"]);
		if ($result->info->http_code == 200) {
			$releases = $result->decode_response();

			// table title
			$this->title = \IPS\Member::loggedIn()->language()->pluralize(\IPS\Member::loggedIn()->language()->get('vpdb_releases_count'), array($result->headers->x_list_count));

			// add IPS member data
			foreach ($releases as $release) {
				$release->url = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=viewRelease&id=' . $release->id . '&gameId=' . $release->game->id);
				foreach ($release->authors as $author) {
					if ($author->user->provider_id) {
						$author->user->member = \IPS\Member::load($author->user->provider_id);
					}
				}
			}
		} else {
			$releases = [];
		}
		return $releases;
	}


	/**
	 * Return the table headers
	 *
	 * @param    array|NULL $advancedSearchValues Advanced search values
	 * @return    array
	 */
	public function getHeaders($advancedSearchValues)
	{
		return array();
	}
}