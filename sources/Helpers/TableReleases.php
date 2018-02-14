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

		try {
			list($releases, $numReleases) = $this->api->getReleases(["per_page" => 25, "sort" => $sortPrefix . $this->sortBy, "thumb_format" => "square"], true);

			// table title
			$this->title = \IPS\Member::loggedIn()->language()->pluralize(\IPS\Member::loggedIn()->language()->get('vpdb_releases_count'), array($numReleases));
			return $releases;

		} catch (\ApiException $e) {
			return [];
		}
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