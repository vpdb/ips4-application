<?php
/**
 * @brief        Content Router extension: vpdb
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    VPDB
 * @since        12 Feb 2018
 */

namespace IPS\vpdb\extensions\core\ContentRouter;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * @brief    Content Router extension: vpdb
 */
class _Vpdb
{
	/**
	 * @brief    Content Item Classes
	 */
	public $classes = array();

	/**
	 * Constructor
	 *
	 * @param    \IPS\Member|IPS\Member\Group|NULL $memberOrGroup If checking access, the member/group to check for, or NULL to not check access
	 * @return    void
	 */
	public function __construct($memberOrGroup = NULL)
	{
		if ($memberOrGroup === NULL or $memberOrGroup->canAccessModule(\IPS\Application\Module::get('vpdb', 'releases', 'front'))) {
			$this->classes[] = 'IPS\vpdb\Release';
		}
	}
}