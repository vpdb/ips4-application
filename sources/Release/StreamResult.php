<?php

namespace IPS\vpdb\Release;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * Release search result from VPDB
 */
class _StreamResult extends \IPS\Content\Search\Result
{
	/**
	 * @brief    Release data
	 */
	protected $release;

	protected $author;

	public function __construct($release, $author)
	{
		$this->release = $release;
		$this->author = $author;
		$this->createdDate = \IPS\DateTime::ts(strtotime($release->created_at));
		$this->lastUpdatedDate = \IPS\DateTime::ts(strtotime($release->created_at));
	}

	/**
	 * HTML
	 *
	 * @param string $view Either "expanded" or "condensed".
	 * @return    string
	 */
	public function html($view = 'expanded')
	{
		$authors = \IPS\vpdb\Release::splitAuthors($this->release, $this->author);
		return \IPS\Theme::i()->getTemplate('releases', 'vpdb', 'front')->streamResult($this->release, $authors[0], $authors[1], $view);
	}
}