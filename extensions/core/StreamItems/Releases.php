<?php
/**
 * @brief        Activity stream items extension: Releases
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    VPDB
 * @since        12 Feb 2018
 */

namespace IPS\vpdb\extensions\core\StreamItems;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * @brief    Activity stream items extension: Releases
 */
class _Releases
{
	/**
	 * @var \Ips\vpdb\Vpdb\_Api
	 */
	protected $api;

	/**
	 * _Releases constructor.
	 */
	public function __construct()
	{
		$this->api = \IPS\vpdb\Vpdb\Api::getInstance();
	}


	/**
	 * Is there content to display?
	 *
	 * @param    \IPS\Member|NULL $author The author to limit extra items to
	 * @param    Timestamp|NULL $lastTime If provided, only items since this date are included. If NULL, it works out which to include based on what results are being shown
	 * @param    Timestamp|NULL $firstTime If provided, only items before this date are included. If NULL, it works out which to include based on what results are being shown
	 * @return    array Array of \IPS\Content\Search\Result\Custom objects
	 */
	public function extraItems($author = NULL, $lastTime = NULL, $firstTime = NULL)
	{
		$extraItems = [];
		try {
			if ($author && $author->member_id) {
				$releases = $this->api->getReleases(['sort' => '-created_at', 'thumb_format' => 'square', 'provider_user' => $author->member_id], true)[0];

				foreach ($releases as $release) {
					$extraItems[] = new \IPS\vpdb\Release\StreamResult($release, $author);
				}
			}

		} catch (\IPS\vpdb\Vpdb\ApiException $e) {
		} catch (\RestClientException $e) {
		}

		return $extraItems;
	}

}