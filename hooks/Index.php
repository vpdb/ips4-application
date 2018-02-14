//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	exit;
}

class vpdb_hook_Index extends _HOOK_CLASS_
{
	/**
	 * Retrieve the search ID for an item
	 *
	 * @param    \IPS\Content\Searchable $object Item to add
	 * @return    void
	 */
	public function getIndexId(\IPS\Content\Searchable $object)
	{
		// don't index releases
		if ($object instanceof \IPS\vpdb\Release) {
			return NULL;
		}
		return parent::getIndexId($object);
	}

	/**
	 * Index an item
	 *
	 * @param	\IPS\Content\Searchable	$object	Item to add
	 * @return	void
	 */
	public function index( \IPS\Content\Searchable $object )
	{
		// don't index releases
		if ($object instanceof \IPS\vpdb\Release) {
			return;
		}
		return parent::index($object);
	}

}
