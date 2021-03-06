<?php
/**
 * @brief        Image Comment Model
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    Gallery
 * @since        04 Mar 2014
 */

namespace IPS\vpdb\Release;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * Image Comment Model
 */
class _Comment extends \IPS\Content\Comment implements \IPS\Content\EditHistory, \IPS\Content\Hideable, \IPS\Content\Searchable, \IPS\Content\Embeddable
{
	use \IPS\Content\Reactable;

	/**
	 * @brief    [ActiveRecord] Multiton Store
	 */
	protected static $multitons;

	/**
	 * @brief    [Content\Comment]    Item Class
	 */
	public static $itemClass = 'IPS\vpdb\Release';

	/**
	 * @brief    [ActiveRecord] Database Table
	 */
	public static $databaseTable = 'vpdb_release_comments';

	/**
	 * @brief    [ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'comment_';

	/**
	 * @brief    Database Column Map
	 */
	public static $databaseColumnMap = array(
		'item' => 'release_id',
		'author' => 'author_id',
		'author_name' => 'author_name',
		'content' => 'text',
		'date' => 'post_date',
		'ip_address' => 'ip_address',
		'edit_time' => 'edit_time',
		'edit_member_name' => 'edit_name',
		'edit_show' => 'append_edit',
		'approved' => 'approved'
	);

	/**
	 * @brief    Application
	 */
	public static $application = 'vpdb';

	/**
	 * @brief    Title
	 */
	public static $title = 'vpdb_release_comment';

	/**
	 * @brief    Icon
	 */
	public static $icon = 'camera';

	/**
	 * @brief    [Content]    Key for hide reasons
	 */
	public static $hideLogKey = 'vpdb-releases';

	/**
	 * Release reference.
	 * @var \IPS\vpdb\Release
	 */
	public $item;

	/**
	 * Get snippet HTML for search result display
	 *
	 * @param    array $indexData Data from the search index
	 * @param    array $authorData Basic data about the author. Only includes columns returned by \IPS\Member::columnsForPhoto()
	 * @param    array $itemData Basic data about the item. Only includes columns returned by item::basicDataColumns()
	 * @param    array|NULL $containerData Basic data about the container. Only includes columns returned by container::basicDataColumns()
	 * @param    array $reputationData Array of people who have given reputation and the reputation they gave
	 * @param    int|NULL $reviewRating If this is a review, the rating
	 * @param    string $view 'expanded' or 'condensed'
	 * @return    callable
	 */
	public static function searchResultSnippet(array $indexData, array $authorData, array $itemData, array $containerData = NULL, array $reputationData, $reviewRating, $view)
	{
		$image = \IPS\vpdb\Vpdb\Storage::thumb($itemData['release_id_vpdb'], 'square');
		$url = \IPS\Http\Url::internal('app=vpdb&module=releases&controller=view&releaseId=' . $itemData['release_id_vpdb'] . '&gameId=' . $itemData['game_id_vpdb']);

		return \IPS\Theme::i()->getTemplate('releases', 'vpdb', 'front')->searchResultCommentSnippet($indexData, $itemData, $image, $url, $reviewRating, $view == 'condensed');
	}

	/**
	 * Reaction Type
	 *
	 * @return    string
	 */
	public static function reactionType()
	{
		return 'comment_id';
	}

	/**
	 * Get content for embed
	 *
	 * @param    array $params Additional parameters to add to URL
	 * @return    string
	 */
	public function embedContent($params)
	{
		\IPS\Output::i()->cssFiles = array_merge(\IPS\Output::i()->cssFiles, \IPS\Theme::i()->css('embed.css', 'gallery', 'front'));
		return \IPS\Theme::i()->getTemplate('global', 'gallery')->embedImageComment($this, $this->item(), $this->url()->setQueryString($params));
	}
}