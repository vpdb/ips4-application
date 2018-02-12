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
	 * @brief    Application
	 */
	public static $application = 'vpdb';

	/**
	 * @brief    Module
	 */
	public static $module = 'releases';

	/**
	 * @brief    Database Table
	 */
	public static $databaseTable = 'vpdb_releases';

	/**
	 * @brief    Database Prefix
	 */
	public static $databasePrefix = 'release_';

	/**
	 * @brief    Comment Class
	 */
	public static $commentClass = 'IPS\vpdb\Release\Comment';

	/**
	 * Added what I thought would be useful, though the comments fields don't
	 * seem to be updated (TODO)
	 *
	 * TODO add views, pinned, featured, locked
	 * @brief    Database Column Map
	 */
	public static $databaseColumnMap = array(
		'title' => 'caption',
		'author' => 'member_id',
		'num_comments' => 'comments',
		'unapproved_comments' => 'unapproved_comments',
		'hidden_comments' => 'hidden_comments',
		'last_comment' => 'last_comment'
	);

	protected static $databaseIdFields = array('release_id', 'release_id_vpdb');

	/**
	 * Used in moderator log
	 * @brief    Title
	 */
	public static $title = 'vpdb_release';

	/**
	 * @var array Release fetched from VPDB
	 */
	public $release;

	/**
	 * @var bool If false, only game and release IDs are set.
	 */
	protected $populated = false;

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
	 * @param    string|NULL $action Action
	 * @return    \IPS\Http\Url
	 */
	public function url($action = NULL)
	{
		if ($action) {
			return \IPS\Http\Url::internal('app=vpdb&module=releases&controller=view&releaseId=' . $this->getReleaseId() . '&gameId=' . $this->getGameId() . '&do=' . $action);
		} else {
			return \IPS\Http\Url::internal('app=vpdb&module=releases&controller=view&releaseId=' . $this->getReleaseId() . '&gameId=' . $this->getGameId());
		}
	}

	/**
	 * Get URL from index data
	 *
	 * @param    array $indexData Data from the search index
	 * @param    array $itemData Basic data about the item. Only includes columns returned by item::basicDataColumns()
	 * @return    \IPS\Http\Url
	 */
	public static function urlFromIndexData($indexData, $itemData)
	{
		return \IPS\Http\Url::internal('app=vpdb&module=releases&controller=view&releaseId=' . $itemData['release_id_vpdb'] . '&gameId=' . $itemData['release_game_id_vpdb'], 'front');
	}

	/**
	 * Columns needed to query for search result / stream view
	 *
	 * @return    array
	 */
	public static function basicDataColumns()
	{
		$return = parent::basicDataColumns();
		$return[] = 'release_id_vpdb';
		$return[] = 'release_game_id_vpdb';
		return $return;
	}

	/**f
	 * Fetch Meta Data
	 *
	 * @return    array
	 * @throws    \BadMethodCallException
	 */
	public function getMeta()
	{
		// TODO check wtf this is
		return array();
	}

	/**
	 * This is overridden because the controller hard-codes where the parameter
	 * is retrieved ("id"), while here we need a) another parameter ("releaseId")
	 * and b) another ID field ("release_id_vpdb").
	 *
	 * God knows where else this is used, so we check if passed ID is null first.
	 *
	 * @see \IPS\Content\_Controller::__call
	 * @return    static
	 * @throws    \OutOfRangeException
	 */
	public static function loadAndCheckPerms($id)
	{
		if (!$id) {
			$obj = static::load(\IPS\Request::i()->releaseId, 'release_id_vpdb');
		} else {
			$obj = static::load($id);
		}

		if (!$obj->canView(\IPS\Member::loggedIn())) {
			throw new \OutOfRangeException;
		}

		return $obj;
	}

	/**
	 * Supported Meta Data Types
	 *
	 * @return    array
	 */
	public static function supportedMetaDataTypes()
	{
		return array();
	}

	public function getReleaseId()
	{
		return $this->id_vpdb;
	}

	public function getGameId()
	{
		return $this->game_id_vpdb;
	}

}