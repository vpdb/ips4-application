<?php
/**
 * This is the real language file.
 *
 * There is also an .XML which gets compiled from this file, but hey, why take
 * a script when we can parse an XML?
 */
$lang = array(

	'__app_vpdb' => "VPDB",

	// front navigation
	'module__vpdb_vpdb' => "VPDB",
	'module__vpdb_releases' => "Releases",
	'frontnavigation_home' => "VPDB Home",
	'frontnavigation_releases' => "Releases",
	'frontnavigation_releases_admin' => "Releases (VPDB)",

	// admincp
	'menu__vpdb_vpdb' => "VPDB",
	'menu__vpdb_vpdb_settings' => 'Settings',

	// app settings
	'vpdb_app_key' => 'App Key',
	'vpdb_oauth_client' => 'Provider',
	'vpdb_url_api' => 'API Endpoint',
	'vpdb_url_web' => 'Web Endpoint',
	'vpdb_url_storage' => 'Storage Endpoint',
	'vpdb_settings_authentication' => 'Authentication',
	'vpdb_settings_endpoints' => 'Endpoints',
	'vpdb_settings_invalid_api' => 'Could not connect to API!',
	'r__vpdb_manage' => 'Can change VPDB settings?',

	// home
	'vpdb_home_title' => "VPDB Home",

	// releases - list
	'sort_release_name' => 'Release Name',
	'sort_release_date' => 'Most recent',
	'sort_release_rating' => 'Best rated',
	'sort_release_popularity' => 'Popularity',
	'vpdb_releases_count' =>  "{# [1:release][?:releases]}",

	// notifications
	'__indefart_vpdb_release_comment' => 'a comment on',
	'__indefart_vpdb_release' => 'a release',
	'__defart_vpdb_release' => 'release',

	// streams
	'vpdb_settings_content' => 'Content',
	'all_activity_vpdb_releases' => 'Show Releases in Streams',
	'vpdb_release_stream_content_type' => 'Table Release',
	'vpdb_user_own_activity_release' => '%s posted <b>%s</b> under table releases',

);