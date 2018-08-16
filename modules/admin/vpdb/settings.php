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

		foreach (\IPS\Db::i()->select('*', 'core_oauth_clients') as $client) {
			$client_title_key = 'core_oauth_client_' . $client['oauth_client_id'];
			$oauth_clients[$client['oauth_client_id']] = \IPS\Member::loggedIn()->language()->addToStack($client_title_key);
		}

		$form = new \IPS\Helpers\Form;

		$form->addHeader('vpdb_settings_authentication');
		$form->add(new \IPS\Helpers\Form\Text('vpdb_app_key', \IPS\Settings::i()->vpdb_app_key));
		$form->add(new \IPS\Helpers\Form\Select('vpdb_oauth_client', \IPS\Settings::i()->vpdb_oauth_client, TRUE, array('options' => $oauth_clients)));
		$form->add(new \IPS\Helpers\Form\Text('vpdb_authorization_header', \IPS\Settings::i()->vpdb_authorization_header, TRUE));

		$form->addHeader('vpdb_settings_endpoints');
		$form->add(new \IPS\Helpers\Form\Url('vpdb_url_web', \IPS\Settings::i()->vpdb_url_web, TRUE));
		$form->add(new \IPS\Helpers\Form\Url('vpdb_url_api', \IPS\Settings::i()->vpdb_url_api, TRUE));
		$form->add(new \IPS\Helpers\Form\Url('vpdb_url_storage', \IPS\Settings::i()->vpdb_url_storage, TRUE));

		$form->addHeader('vpdb_settings_content');
		$form->add(new \IPS\Helpers\Form\YesNo('all_activity_vpdb_releases', \IPS\Settings::i()->all_activity_vpdb_releases, TRUE));

		if ($values = $form->values()) {

			$api = new \RestClient(['base_url' => $values['vpdb_url_api'], 'format' => 'json']);
			$result = $api->get("/v1/tokens/" . $values['vpdb_app_key']);

			if ($result->info->http_code != 200) {
				$form->error = \IPS\Member::loggedIn()->language()->addToStack('vpdb_settings_invalid_api_url');
				\IPS\Output::i()->output = $form;
				return;
			}
			$token = $result->decode_response();
			if ($token->type != 'application') {
				$form->error = \IPS\Member::loggedIn()->language()->addToStack('vpdb_settings_not_provider_key');
				\IPS\Output::i()->output = $form;
				return;
			}
			if (!$token->is_active) {
				$form->error = \IPS\Member::loggedIn()->language()->addToStack('vpdb_settings_inactive_key');
				\IPS\Output::i()->output = $form;
				return;
			}

			$form->saveAsSettings();

			/* Clear guest page caches */
			\IPS\Data\Cache::i()->clearAll();

			\IPS\Session::i()->log('acplogs__vpdb_settings');
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->output = $form;
	}
}