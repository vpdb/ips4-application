//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	exit;
}

class vpdb_hook_Query extends _HOOK_CLASS_
{
	/**
	 * Search
	 *
	 * @param    string|null $term The term to search for
	 * @param    array|null $tags The tags to search for
	 * @param    int $method See \IPS\Content\Search\Query::TERM_* contants - controls where to search
	 * @param    string|null $operator If $term contains more than one word, determines if searching for both ("and") or any ("or") of those terms. NULL will go to admin-defined setting
	 * @return    \IPS\Content\Search\Results
	 */
	public function search($term = NULL, $tags = NULL, $method = 1, $operator = NULL)
	{
		// let's just search at vpdb and add the result to the index, THEN search locally.
		\IPS\vpdb\Vpdb\Api::getInstance()->getReleases(['q' => $term], true);;
		return parent::search($term, $tags, $method, $operator);
	}
}
