//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	exit;
}

class vpdb_hook_Member extends _HOOK_CLASS_
{

	public function isOnVpdb()
	{
		return \IPS\Db::i()->select('COUNT(*)', 'oauth2server_members', array('client_id=? AND member_id=?', \IPS\Settings::i()->vpdb_oauth_client, $this->member_id))->first() > 0;
	}


	/**
	 * Recounts reputation for this member
	 *
	 * @return void
	 */
	public function recountReputation()
	{
		// if user's not on vpdb, just get local reputation
		if (!$this->isOnVpdb()) {
			parent::recountReputation();
			return;
		}

		// otherwise, get user's releases
		$api = \IPS\vpdb\Vpdb\Api::getInstance();
		$releases = $api->getReleases(['provider_user' => $this->member_id], false, false);
		$releaseIds = [];
		foreach ($releases[0] as $release) {
			$releaseIds[] = $release->id;
		}

		// fetch points without releases
		$this->pp_reputation_points = \IPS\Db::i()->select('SUM(rep_rating)', 'core_reputation_index', array('member_received=? AND type<>?', $this->member_id, 'release_id'));

		// fetch points from user's releases
		$this->pp_reputation_points += \IPS\Db::i()
			->select('SUM(rep_rating)', 'core_reputation_index', array('type=? AND release_id_vpdb IN (?)', 'release_id', $releaseIds))
			->join('vpdb_releases', 'release_id=type_id');

		// save and we're done!
		$this->save();
	}

}
