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
	use \IPS\Content\Reactable;

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
	 * @brief    Icon
	 */
	public static $icon = 'database';

	/**
	 * @var array Release fetched from VPDB
	 */
	public $release;

	/**
	 * @var bool If false, only game and release IDs are set.
	 */
	protected $populated = false;

	/**
	 * @var \IPS\vpdb\Vpdb\_Api
	 */
	protected $api;

	/**
	 * Release constructor.
	 */
	public function __construct()
	{
		$this->api = \IPS\vpdb\Vpdb\Api::getInstance();
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

	/**
	 * Reaction Type
	 *
	 * @return    string
	 */
	public static function reactionType()
	{
		return 'release_id';
	}

	/**
	 * React.
	 *
	 * This is overridden because we need to give reputation and notify all authors,
	 * where per default there is only one author per content item.
	 *
	 * Creating only one reaction record works well because the receiver is not
	 * visible for content item reactions. We just need to override the reputation
	 * recalculation (see Members hook) and check that stream results are still
	 * working.
	 *
	 * @param \IPS\Content\Reaction $reaction
	 * @param    \IPS\Member                The member reacting, or NULL
	 * @return    void
	 * @throws \Exception
	 */
	public function react(\IPS\Content\Reaction $reaction, \IPS\Member $member = NULL)
	{
		/* Did we pass a member? */
		$member = $member ?: \IPS\Member::loggedIn();

		// Figure out the owner. Might need to refetch
		if (!$this->release) {
			try {
				$this->release = $this->api->getReleaseDetails($this->getReleaseId(), ['ignore_count' => '1']);
			} catch (\RestClientException $e) {
				return;
			}
		}

		// get authors known to IPS
		$authors = [];
		foreach ($this->release->authors as $author) {
			if ($author->user->member) {
				$authors[] = $author->user->member;
			}
		}

		// there must be at least one VPDB author known at IPS
		if (count($authors) == 0) {
			return;
		}
		// now, we only react once, but we attribute the reputation and notifications for all member authors.
		$owner = $authors[0];

		/* Can we react? */
		if (!$this->canView($member) or !$this->canReact($member) or !$reaction->enabled) {
			throw new \DomainException('cannot_react');
		}

		/* Have we hit our limit? Also, why 999 for unlimited? */
		if ($member->group['g_rep_max_positive'] !== -1) {
			$count = \IPS\Db::i()->select('COUNT(*)', 'core_reputation_index', array('member_id=? AND rep_date>?', $member->member_id, \IPS\DateTime::create()->sub(new \DateInterval('P1D'))->getTimestamp()))->first();
			if ($count >= $member->group['g_rep_max_positive']) {
				throw new \DomainException(\IPS\Member::loggedIn()->language()->addToStack('react_daily_exceeded', FALSE, array('sprintf' => array($member->group['g_rep_max_positive']))));
			}
		}

		/* Figure out our app - we do it this way as content items and nodes will always have a lowercase namespace for the app, so if the match below fails, then 'core' can be assumed */
		$app = explode('\\', get_class($this));
		if (\strtolower($app[1]) === $app[1]) {
			$app = $app[1];
		} else {
			$app = 'core';
		}

		/* If this is a comment, we need the parent items ID */
		$itemId = 0;
		if ($this instanceof \IPS\Content\Comment) {
			$item = $this->item();
			$itemIdColumn = $item::$databaseColumnId;
			$itemId = $item->$itemIdColumn;
		}

		/* Have we already reacted? */
		if ($this->reacted($member)) {
			$this->removeReaction($member);
		}

		/* Actually insert it */
		$idColumn = static::$databaseColumnId;
		\IPS\Db::i()->insert('core_reputation_index', array(
			'member_id' => $member->member_id,
			'app' => $app,
			'type' => static::reactionType(),
			'type_id' => $this->$idColumn,
			'rep_date' => \IPS\DateTime::create()->getTimestamp(),
			'rep_rating' => $reaction->value,
			'member_received' => $owner->member_id,
			'rep_class' => static::reactionClass(),
			'class_type_id_hash' => md5(static::reactionClass() . ':' . $this->$idColumn),
			'item_id' => $itemId,
			'reaction' => $reaction->id
		));

		// this we'll do for each known author.
		$notification = null;
		foreach ($authors as $owner) {

			// send the notification
			if ($this->author()->member_id AND $this->author() != \IPS\Member::loggedIn() AND $this->canView($owner)) {

				if (!$notification) {
					$notification = new \IPS\Notification(\IPS\Application::load('core'), 'new_likes', $this, array($this, $member));
				}
				$notification->recipients->attach($owner);
			}

			// add reputation
			if ($owner->member_id) {
				$owner->pp_reputation_points += $reaction->value;
				$owner->save();
			}
		}
		if ($notification) {
			$notification->send();
		}
	}

	/**
	 * Remove Reaction
	 *
	 * @param    \IPS\Member|NULL        The member, or NULL
	 * @return    void
	 */
	public function removeReaction(\IPS\Member $member = NULL)
	{
		$member = $member ?: \IPS\Member::loggedIn();

		try {
			try {
				$idColumn = static::$databaseColumnId;

				$where = $this->getReactionWhereClause(NULL, FALSE);
				$where[] = array('member_id=?', $member->member_id);
				$rep = \IPS\Db::i()->select('*', 'core_reputation_index', $where)->first();
			} catch (\UnderflowException $e) {
				throw new \OutOfRangeException;
			}

			$memberReceived = \IPS\Member::load($rep['member_received']);
			$reaction = \IPS\Content\Reaction::load($rep['reaction']);
		} catch (\OutOfRangeException $e) {
			throw new \DomainException;
		}

		// Figure out the receiving members. Might need to refetch.
		if (!$this->release) {
			try {
				$this->release = $this->api->getReleaseDetails($this->getReleaseId(), ['ignore_count' => '1']);
			} catch (\RestClientException $e) {
			}
		}

		// get authors known to IPS
		if ($this->release) {
			foreach ($this->release->authors as $author) {
				if ($author->user->member) {
					$author->user->member->pp_reputation_points = $author->user->member->pp_reputation_points - $reaction->value;
					$author->user->member->save();
				}
			}
		} else {
			// fallback when release fetch failed; use local data
			if ($memberReceived->member_id) {
				$memberReceived->pp_reputation_points = $memberReceived->pp_reputation_points - $reaction->value;
				$memberReceived->save();
			}
		}

		\IPS\Db::i()->delete('core_reputation_index', array("id=?", $rep['id']));
	}

	/**
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