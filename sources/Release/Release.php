<?php
/**
 * @brief        Image Model
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    Gallery
 * @since        04 Mar 2014
 */

namespace IPS\vpdb;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * Image Model
 */
class _Release extends \IPS\Content\Item implements
	\IPS\Content\Searchable,
	\IPS\Content\ReadMarkers,
	\IPS\Content\MetaData,
	\IPS\Content\Lockable
{
	/**
	 * @brief	Application
	 */
	public static $application = 'vpdb';

	/**
	 * @brief	Module
	 */
	public static $module = 'releases';

	/**
	 * @brief    Comment Class
	 */
	public static $commentClass = 'IPS\vpdb\Release\Comment';

	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array();

	/**
	 * @var array Release fetched from VPDB
	 */
	protected $release;

	/**
	 * Construct with data from VPDB
	 * @param $release array Release details from VPDB
	 */
	public function __construct($release)
	{
		$this->release = $release;
	}

	/**
	 * Get comments output
	 * @return    string
	 */
	public function renderComments()
	{
		return \IPS\Theme::i()->getTemplate('releases')->comments($this);
	}

	/**
	 * Get URL
	 *
	 * @param	string|NULL		$action		Action
	 * @return	\IPS\Http\Url
	 */
	public function url( $action=NULL ) {
		if ($action) {
			return \IPS\Http\Url::internal('app=vpdb&module=releases&controller=viewRelease&id=' . $this->release->id . '&gameId=' . $this->release->game->id . '&do=' . $action);
		} else {
			return \IPS\Http\Url::internal('app=vpdb&module=releases&controller=viewRelease&id=' . $this->release->id . '&gameId=' . $this->release->game->id);
		}
	}

	/**
	 * Fetch Meta Data
	 *
	 * @return	array
	 * @throws	\BadMethodCallException
	 */
	public function getMeta()
	{
		// TODO check wtf this is
		return array();
	}

	public function get_id() {
		return $this->release->id;
	}

	/**
	 * Check if this content has meta data
	 *
	 * @return	bool
	 * @throws	\BadMethodCallException
	 */
//	public function hasMetaData()
//	{
//		return false;
//	}
}