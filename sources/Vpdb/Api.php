<?php

namespace IPS\vpdb\Vpdb;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

/**
 * A singleton class that interfaces with the VPDB backend.
 */
class _Api
{
	/**
	 * @var _Api
	 */
	protected static $instance;

	/**
	 * Rest client, authenticated with a provider token.
	 * @var \RestClient
	 */
	protected $client;

	/**
	 * Api constructor.
	 */
	protected function __construct()
	{
		$this->client = new \RestClient([
			'base_url' => \IPS\Settings::i()->vpdb_url_api,
			'format' => 'json',
			'headers' => ['Authorization' => 'Bearer ' . \IPS\Settings::i()->vpdb_app_key],
		]);
	}

	/**
	 * Returns the VPDB profile of currently loged IPS user.
	 *
	 * @return mixed|null Profile or null if user not registered at VPDB. All other errors throw an exception.
	 * @throws \RestClientException
	 */
	public function getUserProfile() {
		$result = $this->client->get("/v1/user", [], $this->getUserHeader());
		if ($result->info->http_code == 400 && preg_match('/no user with id "\d+" for provider/i', $result->decode_response()->error)) {
			return null;
		}
		if ($result->info->http_code != 200) {
			throw new \IPS\vpdb\Vpdb\ApiException($result);
		}
		return $result->decode_response();
	}

	/**
	 * Lists releases at VPDB.
	 *
	 * Also updates internal reference to releases.
	 *
	 * @param array $sortOptions
	 * @return array First element is the list of releases, second element the total number of releases read from the header
	 * @throws \IPS\vpdb\Vpdb\ApiException When server returned something we don't expect
	 * @throws \RestClientException When server communication failed
	 */
	public function getReleases(array $sortOptions)
	{
		$result = $this->client->get("/v1/releases", $sortOptions, $this->getUserHeader());
		if ($result->info->http_code != 200) {
			throw new \IPS\vpdb\Vpdb\ApiException($result);
		}

		$dbData = array();
		$releases = $result->decode_response();
		foreach ($releases as $release) {
			$dbData[] = $this->releaseToQuery($release);
			$release->url = $this->getUrl($release);
			foreach ($release->authors as $author) {
				if ($author->user->provider_id) {
					$author->user->member = \IPS\Member::load($author->user->provider_id);
				}
			}
		}
		// update database references TODO cache this
		\IPS\Db::i()->insert('vpdb_releases', $dbData, true);

		return [$releases, $result->headers->x_list_count];
	}

	/**
	 * Returns release details and game details
	 * @param $releaseId
	 * @param $query
	 * @return mixed
	 * @throws \RestClientException
	 */
	public function getReleaseDetails($releaseId, $query)
	{
		// fetch release
		$result = $this->client->get("/v1/releases/" . $releaseId, $query, $this->getUserHeader());
		if ($result->info->http_code != 200) {
			throw new \IPS\vpdb\Vpdb\ApiException($result);
		}
		$release = $result->decode_response();

		// fetch game
		$result = $this->client->get("/v1/games/" . $release->game->id, [], $this->getUserHeader());
		if ($result->info->http_code != 200) {
			throw new \IPS\vpdb\Vpdb\ApiException($result);
		}
		$release->game = $result->decode_response();

		// insert / update item
		\IPS\Db::i()->insert('vpdb_releases', $this->releaseToQuery($release), true);

		// load local content item
		$itemId = \IPS\Db::i()->select('release_id', 'vpdb_releases', array('release_id_vpdb=?', $release->id))->first();
		$release->item = \IPS\vpdb\Release::load($itemId);

		// load local member data
		foreach ($release->authors as $author) {
			if ($author->user->provider_id) {
				$author->user->member = \IPS\Member::load($author->user->provider_id);
			}
		}
		return $release;
	}

	/**
	 * Returns all ROMs of a given game
	 * @param $gameId string Game ID
	 * @return mixed
	 * @throws \RestClientException
	 */
	public function getRoms($gameId) {
		$result = $this->client->get('/v1/games/' . $gameId . '/roms', [], $this->getUserHeader());
		if ($result->info->http_code != 200) {
			throw new \IPS\vpdb\Vpdb\ApiException($result);
		}
		return $result->decode_response();
	}

	/**
	 * Returns the singleton instance of this class.
	 * @return _Api
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new Api();
		}
		return self::$instance;
	}

	protected function getUserHeader()
	{
		return \IPS\Member::loggedIn()->member_id ? ['X-User-Id' => \IPS\Member::loggedIn()->member_id] : [];
	}

	protected function getUrl($release)
	{
		return \IPS\Http\Url::internal('app=vpdb&module=releases&controller=view&releaseId=' . $release->id . '&gameId=' . $release->game->id);
	}

	protected function releaseToQuery($release)
	{
		if ($release->game->manufacturer && $release->game->year) {
			$caption = $release->game->title . ' (' . $release->game->manufacturer . ' ' . $release->game->year . ')';
		} else {
			$caption = $release->game->title;
		}
		return array(
			'release_id_vpdb' => $release->id,
			'release_game_id_vpdb' => $release->game->id,
			'release_game_title' => $release->game->title,
			'release_game_manufacturer' => $release->game->title,
			'release_game_year' => $release->game->year,
			'release_caption' => $caption,
		);
	}

	public static $flavors = array(
		'orientation' => array(
			'header' => 'Orientation',
			'name' => 'orientation',
			'values' => array(
				'ws' => array('name' => 'Desktop', 'other' => 'Landscape', 'value' => 'ws', 'short' => 'DT'),
				'fs' => array('name' => 'Cabinet', 'other' => 'Portrait', 'value' => 'fs', 'short' => 'FS'),
				'any' => array('name' => 'Universal', 'other' => 'Any Orientation', 'value' => 'any', 'short' => 'Universal')
			)
		),
		'lighting' => array(
			'header' => 'Lighting',
			'name' => 'lighting',
			'values' => array(
				'night' => array('name' => 'Night', 'other' => 'Dark Playfield', 'value' => 'night'),
				'day' => array('name' => 'Day', 'other' => 'Illuminated Playfield', 'value' => 'day'),
				'any' => array('name' => 'Universal', 'other' => 'Customizable', 'value' => 'any')
			)
		)
	);
}