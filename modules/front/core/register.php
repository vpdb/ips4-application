<?php


namespace IPS\vpdb\modules\front\core;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * register
 */
class _register extends \IPS\Dispatcher\Controller
{
	/**
	 * @var \IPS\vpdb\Vpdb\_Api
	 */
	protected $api;

	/**
	 * Constructor
	 *
	 * @param    \IPS\Http\Url|NULL $url The base URL for this controller or NULL to calculate automatically
	 * @return    void
	 */
	public function __construct($url = NULL)
	{
		parent::__construct($url);
		$this->api = \IPS\vpdb\Vpdb\Api::getInstance();
	}

	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return    void
	 */
	protected function manage()
	{
		\IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('front_core.js', 'vpdb'));

		$registerUrl = \IPS\Http\Url::internal('app=vpdb&module=core&controller=register&do=register');
		$vpdbUrl = \IPS\Settings::i()->vpdb_url_web;

		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->register($registerUrl, $vpdbUrl);
	}

	protected function register()
	{
		// enter ajax land
		if (!\IPS\Request::i()->isAjax() || !$_POST['confirmed']) {
			// todo do, uh, something else.
			return;
		}

		try {

			// register user at vpdb
			$this->api->registerUser(\IPS\Member::loggedIn());

			// enable oauth at ips
			$client = \IPS\Api\OAuthClient::load(\IPS\Settings::i()->vpdb_oauth_client);
			$client->generateAccessToken(\IPS\Member::loggedIn(), array('profile', 'email'), 'implicit', TRUE);

//			\IPS\Db::i()->insert('oauth2server_members', [
//				'client_id' => \IPS\Settings::i()->vpdb_oauth_client,
//				'member_id' => intval(\IPS\Member::loggedIn()->member_id),
//				'created_at' => date('Y-m-d H:i:s'),
//				'scope' => null
//			], true);
			\IPS\Output::i()->json(array('success' => true));

		} catch (\RestClientException $e) {
			\IPS\Output::i()->json(array('error' => 'Error connecting to VPDB.'));

		} catch (\IPS\vpdb\Vpdb\ApiException $e) {
			\IPS\Output::i()->json(array('error' => $e->getError()));
		}
	}


	// Create new methods with the same name as the 'do' parameter which should execute it
}