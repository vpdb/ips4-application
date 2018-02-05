<?php


namespace IPS\vpdb\modules\front\releases;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * listReleases
 */
class _listReleases extends \IPS\Dispatcher\Controller
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
		$table = new \IPS\vpdb\Helpers\TableReleases($this->url);
		$table->classes = array('ipsDataList_large');

		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('vpdb_home_title');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('releases')->list((string) $table);
	}

	// Create new methods with the same name as the 'do' parameter which should execute it
}