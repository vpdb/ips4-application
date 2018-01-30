<?php

namespace IPS\vpdb\modules\admin\vpdb;

include_once(\IPS\ROOT_PATH . '/applications/vpdb/sources/RestClient.php');

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return    void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission('vpdb_manage', 'blog');
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return    void
	 */
	protected function manage()
	{
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');

		$oauth_clients = [];
		try {
			foreach (\IPS\Db::i()->select('*', 'oauth2server_clients') as $client) {
				$oauth_clients[$client['client_id']] = $client['client_name'];
			}
		} catch (\IPS\Db\Exception $e) {

			// this goes into "{app}/dev/html/admin/settings" and loads "oauthError.phtml". like, why not name methods after files!
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('settings')->oauthError();
			return;
		}


		$form = new \IPS\Helpers\Form;

		$form->addHeader('vpdb_settings_authentication');
		$form->add(new \IPS\Helpers\Form\Text('vpdb_app_key', \IPS\Settings::i()->vpdb_app_key));
		$form->add(new \IPS\Helpers\Form\Select('vpdb_oauth_client', \IPS\Settings::i()->vpdb_oauth_client, TRUE, array('options' => $oauth_clients)));

		$form->addHeader('vpdb_settings_endpoints');
		$form->add(new \IPS\Helpers\Form\Url('vpdb_url_web', \IPS\Settings::i()->vpdb_url_web, TRUE));
		$form->add(new \IPS\Helpers\Form\Url('vpdb_url_api', \IPS\Settings::i()->vpdb_url_api, TRUE));
		$form->add(new \IPS\Helpers\Form\Url('vpdb_url_storage', \IPS\Settings::i()->vpdb_url_storage, TRUE));

		if ($values = $form->values()) {

			$api = new \RestClient([ 'base_url' => $values['vpdb_url_api'], 'format' => "json" ]);
			$result = $api->get("/v1");

			if (!$result['app_name']) {
				$form->error	= \IPS\Member::loggedIn()->language()->addToStack('vpdb_settings_invalid_api');
				\IPS\Output::i()->output = $form;
				return;
			}

			$form->saveAsSettings();

			/* Clear guest page caches */
			\IPS\Data\Cache::i()->clearAll();

			\IPS\Session::i()->log( 'acplogs__vpdb_settings' );
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->output = $form;
	}
}