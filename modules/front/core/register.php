<?php


namespace IPS\vpdb\modules\front\core;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * register
 */
class _register extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{

		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('front_core.js', 'vpdb'));

		$registerUrl = \IPS\Http\Url::internal('app=vpdb&module=core&controller=register&do=register');

		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('core')->register($registerUrl);
	}

	protected function register() {

		// enter ajax land
		if (!\IPS\Request::i()->isAjax() || !$_POST['confirmed']) {
			// todo do, uh, something else.
			return;
		}

		// register user at vpdb

		// return
		\IPS\Output::i()->json(array('success' => true));
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}