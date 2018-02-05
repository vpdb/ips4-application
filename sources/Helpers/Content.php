<?php
/**
 * @brief        Table Builder using an \IPS\Content\Item class datasource
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @since        16 Jul 2013
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
class _Content extends \IPS\Helpers\Table\Table
{
	/**
	 * _Content constructor.
	 */
	public function __construct(\IPS\Http\Url $baseUrl)
	{
		parent::__construct($baseUrl);
		$this->rowsTemplate = array(\IPS\Theme::i()->getTemplate('home'), 'releaseRows');

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
	 * Get rows
	 *
	 * @param    array $advancedSearchValues Values from the advanced search form
	 * @return    array
	 * @throws \RestClientException
	 */
	public function getRows($advancedSearchValues)
	{
		error_log(print_r($advancedSearchValues, TRUE));
		$api = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => 'json',
			'headers' => ['Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key],
		]);


		if (!$this->sortBy) {
			$this->sortBy = 'released_at';
		}
		$sortPrefix = $this->sortDirection == 'asc' ? '' : '-';

		$result = $api->get("/v1/releases", ["per_page" => 6, "sort" => $sortPrefix . $this->sortBy, "thumb_format" => "square"]);
		if ($result->info->http_code == 200) {
			$releases = $result->decode_response();
			foreach ($releases as $release) {
				foreach ($release->authors as $author) {
					if ($author->user->provider_id) {
						$author->user->member = \IPS\Member::load($author->user->provider_id);
						$author->user->member_raw = print_r(\IPS\Member::load($author->user->provider_id), true);
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