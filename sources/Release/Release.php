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
	 * React
	 *
	 * @param    \IPS\core\Reaction $reaction The reaction
	 * @param    \IPS\Member $member The member reacting, or NULL
	 * @return    void
	 * @throws    \DomainException
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

		foreach ($this->release->authors as $author) {
			$this->reactWithOwner($reaction, $member, $author, count($this->release->authors));
		}
	}

	protected function reactWithOwner(\IPS\Content\Reaction $reaction, \IPS\Member $member, $author, $numAuthors)
	{
		// can't react to members that aren't on IPS
		if (!$author->user->member) {
			return;
		}

		$owner = $author->user->member;

		/* Can we react? */
		if (!$this->canView($member) or !$this->canReact($member) or !$reaction->enabled) {
			throw new \DomainException('cannot_react');
		}

		/* Have we hit our limit? Also, why 999 for unlimited? */
		if ($member->group['g_rep_max_positive'] !== -1) {
			$count = \IPS\Db::i()->select('COUNT(*)', 'core_reputation_index', array('member_id=? AND rep_date>?', $member->member_id, \IPS\DateTime::create()->sub(new \DateInterval('P1D'))->getTimestamp()))->first();
			if (($count + $numAuthors) > $member->group['g_rep_max_positive']) {
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
//		if ($this->reacted($member)) {
//			$this->removeReaction($member);
//		}

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

		/* Send the notification */
		if ($this->author()->member_id AND $this->author() != \IPS\Member::loggedIn() AND $this->canView($owner)) {
			$notification = new \IPS\Notification(\IPS\Application::load('core'), 'new_likes', $this, array($this, $member), array(), TRUE, \IPS\Content\Reaction::isLikeMode() ? NULL : 'notification_new_react');
			$notification->recipients->attach($owner);
			$notification->send();
		}

		if ($owner->member_id) {
			$owner->pp_reputation_points += $reaction->value;
			$owner->save();
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
				$reps = \IPS\Db::i()->select('*', 'core_reputation_index', $where);
			} catch (\UnderflowException $e) {
				throw new \OutOfRangeException;
			}

			foreach ($reps as $rep) {
				$memberReceived = \IPS\Member::load($rep['member_received']);
				$reaction = \IPS\Content\Reaction::load($rep['reaction']);

				if ($memberReceived->member_id) {
					$memberReceived->pp_reputation_points = $memberReceived->pp_reputation_points - $reaction->value;
					$memberReceived->save();
				}
				\IPS\Db::i()->delete('core_reputation_index', array("id=?", $rep['id']));
			}
		} catch (\OutOfRangeException $e) {
			throw new \DomainException;
		}
	}

	/**
	 * Reactions
	 *
	 * @param    array|NULL $mixInData If the data is already know, it can be passed here to be manually set
	 * @return    array
	 */
	public function reactions()
	{
		if ($this->_reactionCount === NULL) {
			$this->_reactionCount = 0;
		}

		if ($this->_reactions === NULL) {
			$idColumn = static::$databaseColumnId;
			$this->_reactions = array();

			if (is_array($this->reputation)) {
				foreach ($this->reputation AS $memberId => $reactionId) {
					try {
						$this->_reactionCount += \IPS\Content\Reaction::load($reactionId)->value;
						$this->_reactions[$memberId][] = $reactionId;
					} catch (\OutOfRangeException $e) {
					}
				}
			} else {
				foreach (\IPS\Db::i()->select('member_id, rep_rating', 'core_reputation_index', $this->getReactionWhereClause(), null, null, array('member_id', 'rep_rating'))->join('core_reactions', 'reaction=reaction_id') AS $reaction) {
					$this->_reactions[$reaction['member_id']][] = $reaction['reaction'];
					$this->_reactionCount += $reaction['rep_rating'];
				}
			}
		}

		return $this->_reactions;
	}

	/**
	 * Reaction Table
	 *
	 * @return    \IPS\Helpers\Table\Db
	 */
	public function reactionTable($reaction = NULL)
	{
		if (!\IPS\Member::loggedIn()->group['gbw_view_reps'] or !$this->canView()) {
			throw new \DomainException;
		}
		$idColumn = static::$databaseColumnId;

		//---- CHANGE START
		$group = array('member_id', 'reaction');
		$table = new \IPS\Helpers\Table\Db('core_reputation_index', $this->url('showReactions'), $this->getReactionWhereClause($reaction), $group);
		$table->onlySelected = array('member_id', 'reaction', 'rep_date');
		//---- CHANGE END
		$table->sortBy = 'rep_date';
		$table->sortDirection = 'desc';
		$table->tableTemplate = array(\IPS\Theme::i()->getTemplate('global', 'core', 'front'), 'reactionLogTable');
		$table->rowsTemplate = array(\IPS\Theme::i()->getTemplate('global', 'core', 'front'), 'reactionLog');
		$table->joins = array(array('from' => 'core_reactions', 'where' => 'reaction=reaction_id'));

		$table->rowButtons = function ($row) {
			return array(
				'delete' => array(
					'icon' => 'times-circle',
					'title' => 'delete',
					'link' => $this->url('unreact')->csrf()->setQueryString(array('member' => $row['member_id'])),
					'data' => array('confirm' => TRUE)
				)
			);
		};

		return $table;
	}

	/**
	 * React Blurb
	 *
	 * @return    string
	 */
	public function reactBlurb()
	{
		if ($this->reactBlurb === NULL) {
			$this->reactBlurb = array();

			if (count($this->reactions())) {
				$idColumn = static::$databaseColumnId;
				foreach (\IPS\Db::i()->select('member_id, reaction', 'core_reputation_index', $this->getReactionWhereClause(), null, null, array('member_id', 'reaction'))->join('core_reactions', 'reaction=reaction_id') AS $rep) {
					if (!isset($this->reactBlurb[$rep['reaction']])) {
						$this->reactBlurb[$rep['reaction']] = 0;
					}

					$this->reactBlurb[$rep['reaction']]++;
				}

				/* Error suppressor for https://bugs.php.net/bug.php?id=50688 */
				@uksort($this->reactBlurb, function ($a, $b) {
					try {
						$a = \IPS\Content\Reaction::load($a);
						$b = \IPS\Content\Reaction::load($b);
					} /* One of the reactions does not exist */
					catch (\OutOfRangeException $e) {
						return 1;
					}

					if ($a->position < $b->position) {
						return -1;
					} elseif ($a->position == $b->position) {
						return 0;
					} else {
						return 1;
					}
				});
			} else {
				$this->reactBlurb = array();
			}
		}
		return $this->reactBlurb;
	}

	/**
	 * Who Reacted
	 *
	 * @param    bool|NULL    Use like text instead? NULL to automatically determine
	 * @return    string
	 */
	public function whoReacted($isLike = NULL)
	{
		if ($isLike === NULL) {
			$isLike = \IPS\Content\Reaction::isLikeMode();
		}

		if ($this->likeBlurb === NULL) {
			$langPrefix = 'react_';
			if ($isLike) {
				$langPrefix = 'like_';
			}

			/* Did anyone like it? */
			$numberOfLikes = $this->reactionCount(); # int
			if ($numberOfLikes) {
				/* Is it just us? */
				$userLiked = ($this->reacted());
				if ($userLiked and $numberOfLikes < 2) {
					$this->likeBlurb = \IPS\Member::loggedIn()->language()->addToStack("{$langPrefix}blurb_just_you");
				} /* Nope, we need to display a number... */
				else {
					$peopleToDisplayInMainView = array();
					$andXOthers = $numberOfLikes;

					/* If the user liked, we always show "You" first */
					if ($userLiked) {
						$peopleToDisplayInMainView[] = \IPS\Member::loggedIn()->language()->addToStack("{$langPrefix}blurb_you_and_others");
						$andXOthers--;
					}

					$peopleToDisplayInSecondaryView = array();

					/* Some random names */
					$idColumn = static::$databaseColumnId;
					$i = 0;
					$peopleToDisplayInSecondaryView = array();
					/* Figure out our app - we do it this way as content items and nodes will always have a lowercase namespace for the app, so if the match below fails, then 'core' can be assumed */
					$app = explode('\\', static::reactionClass());
					if (\strtolower($app[1]) === $app[1]) {
						$app = $app[1];
					} else {
						$app = 'core';
					}
					$where = $this->getReactionWhereClause();
					$where[] = array('member_id!=?', \IPS\Member::loggedIn()->member_id ?: 0);

					// since we can have multiple entries per entity (>1 author per
					// release), group by member ID when returning people to display.
					// we unfortunately can't override this more concisely, so here are
					// three lines changed.
					//---- CHANGE START
					$group = array('member_id', 'app');
					$reps = \IPS\Db::i()->select('member_id, app', 'core_reputation_index', $where, 'RAND()', $userLiked ? 17 : 18, $group)->join('core_reactions', 'reaction=reaction_id');
					foreach ($reps as $rep) //---- CHANGE END
					{
						if ($i < ($userLiked ? 2 : 3)) {
							$peopleToDisplayInMainView[] = htmlspecialchars(\IPS\Member::load($rep['member_id'])->name, ENT_QUOTES | \IPS\HTMLENTITIES, 'UTF-8', FALSE);
							$andXOthers--;
						} else {
							$peopleToDisplayInSecondaryView[] = htmlspecialchars(\IPS\Member::load($rep['member_id'])->name, ENT_QUOTES | \IPS\HTMLENTITIES, 'UTF-8', FALSE);
						}
						$i++;
					}

					/* If there's people to display in the secondary view, add that */
					if ($peopleToDisplayInSecondaryView) {
						if (count($peopleToDisplayInSecondaryView) < $andXOthers) {
							$peopleToDisplayInSecondaryView[] = \IPS\Member::loggedIn()->language()->addToStack("{$langPrefix}blurb_others_secondary", FALSE, array('pluralize' => array($andXOthers - count($peopleToDisplayInSecondaryView))));
						}
						$peopleToDisplayInMainView[] = \IPS\Theme::i()->getTemplate('global', 'core', 'front')->reputationOthers($this->url('showReactions'), \IPS\Member::loggedIn()->language()->addToStack("{$langPrefix}blurb_others", FALSE, array('pluralize' => array($andXOthers))), json_encode($peopleToDisplayInSecondaryView));
					}

					/* Put it all together */
					$this->likeBlurb = \IPS\Member::loggedIn()->language()->addToStack("{$langPrefix}blurb", FALSE, array('pluralize' => array($numberOfLikes), 'htmlsprintf' => array(\IPS\Member::loggedIn()->language()->formatList($peopleToDisplayInMainView))));
				}

			} /* Nobody liked it - show nothing */
			else {
				$this->likeBlurb = '';
			}
		}
		return $this->likeBlurb;
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