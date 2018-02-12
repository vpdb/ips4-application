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
	 * @return    string
	 */
	public function html($view = 'expanded')
	{
		// if $author is set, it's the main author we're listing, otherwise just take the first.
		if ($this->author) {
			$mainAuthor = null;
			$otherAuthors = [];
			foreach ($this->release->authors as $a) {
				if ($a->user->member && $a->user->member->member_id == $this->author->member_id) {
					$mainAuthor = $a;
				} else {
					$otherAuthors[] = $a;
				}
			}

			// remove once we search correctly
			if (!$mainAuthor) {
				$mainAuthor = $this->release->authors[0];
				$otherAuthors = array_slice($this->release->authors, 1);
			}
		} else {
			$mainAuthor = $this->release->authors[0];
			$otherAuthors = array_slice($this->release->authors, 1);
		}


		return \IPS\Theme::i()->getTemplate('releases', 'vpdb', 'front')->streamResult($this->release, $mainAuthor, $otherAuthors, $view);
	}
}