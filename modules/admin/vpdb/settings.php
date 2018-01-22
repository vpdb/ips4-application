<?php


namespace IPS\vpdb\modules\admin\vpdb;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
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
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'vpdb_manage', 'blog' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = new \IPS\Helpers\Form;

		$form->addHeader('vpdb_settings_authentication');
		$form->add( new \IPS\Helpers\Form\Text( 'vpdb_app_key', \IPS\Settings::i()->vpdb_app_key ) );

		$form->addHeader('vpdb_settings_endpoints');
		$form->add( new \IPS\Helpers\Form\Url( 'vpdb_url_web', \IPS\Settings::i()->vpdb_url_web ) );
		$form->add( new \IPS\Helpers\Form\Url( 'vpdb_url_api', \IPS\Settings::i()->vpdb_url_api ) );
		$form->add( new \IPS\Helpers\Form\Url( 'vpdb_url_storage', \IPS\Settings::i()->vpdb_url_storage ) );

		if ( $form->values() )
		{
			$form->saveAsSettings();

			/* Clear guest page caches */
			\IPS\Data\Cache::i()->clearAll();

			//\IPS\Session::i()->log( 'acplogs__blog_settings' );
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->output = $form;
	}
}