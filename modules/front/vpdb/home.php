<?php


namespace IPS\vpdb\modules\front\vpdb;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * home
 */
class _home extends \IPS\Dispatcher\Controller
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

		//\IPS\Session::i()->setLocation( \IPS\Http\Url::internal( 'app=vpdb', 'front', 'vpdb' ), array(), 'games_overview' );

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack('vpdb_page_title');
        \IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'home' )->index();
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}