<?php
/**
 * @brief        Front Navigation Extension: Releases
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    VPDB Application
 * @since        06 Feb 2018
 */

namespace IPS\vpdb\extensions\core\FrontNavigation;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * Front Navigation Extension: Releases
 */
class _Releases extends \IPS\core\FrontNavigation\FrontNavigationAbstract
{
	/**
	 * Get Type Title which will display in the AdminCP Menu Manager
	 *
	 * @return    string
	 */
	public static function typeTitle()
	{
		return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_releases_admin');
	}

	/**
	 * Can this item be used at all?
	 * For example, if this will link to a particular feature which has been diabled, it should
	 * not be available, even if the user has permission
	 *
	 * @return    bool
	 */
	public static function isEnabled()
	{
		return TRUE;
	}

	/**
	 * Can the currently logged in user access the content this item links to?
	 *
	 * @return    bool
	 */
	public function canAccessContent()
	{
		return TRUE;
	}

	/**
	 * Get Title
	 *
	 * @return    string
	 */
	public function title()
	{
		return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_releases');
	}

	/**
	 * Get Link
	 *
	 * @return    \IPS\Http\Url
	 */
	public function link()
	{
		return \IPS\Http\Url::internal("app=vpdb&module=releases&controller=listReleases");
	}

	/**
	 * Is Active?
	 *
	 * @return    bool
	 */
	public function active()
	{
		return \IPS\Dispatcher::i()->application->directory === 'vpdb'
			and \IPS\Dispatcher::i()->module
			and \IPS\Dispatcher::i()->module->key === 'releases';
	}

	/**
	 * Children
	 *
	 * @param    bool $noStore If true, will skip datastore and get from DB (used for ACP preview)
	 * @return    array
	 */
	public function children($noStore = FALSE)
	{
		return NULL;
	}
}