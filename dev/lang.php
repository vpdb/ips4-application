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
	'module__vpdb_vpdb' => "VPDB (deprecated)",
	'module__vpdb_releases' => "Releases",
	'module__vpdb_core' => 'Core',
	'frontnavigation_home' => "VPDB Home",
	'frontnavigation_releases' => "Releases",
	'frontnavigation_releases_admin' => "Releases (VPDB)",

	// admincp
	'menu__vpdb_vpdb' => "VPDB",
	'menu__vpdb_vpdb_settings' => 'Settings',

	// app settings
	'vpdb_app_key' => 'App Key',
	'vpdb_oauth_client' => 'Provider',
	'vpdb_authorization_header' => 'Authorization Header',
	'vpdb_url_api' => 'API Endpoint',
	'vpdb_url_web' => 'Web Endpoint',
	'vpdb_url_storage' => 'Storage Endpoint',
	'vpdb_settings_authentication' => 'Authentication',
	'vpdb_settings_endpoints' => 'Endpoints',
	'vpdb_settings_invalid_api_url' => 'Could not connect to API!',
	'vpdb_settings_not_provider_key' => 'Not a provider token. Personal tokens will not work when connecting as a service.',
	'vpdb_settings_inactive_key' => 'App key is disabled. Please enable at VPDB or use an enabled key.',
	'r__vpdb_manage' => 'Can change VPDB settings?',

	// home
	'vpdb_home_title' => "VPDB Home",

	// releases - list
	'sort_release_name' => 'Release Name',
	'sort_release_date' => 'Most recent',
	'sort_release_rating' => 'Best rated',
	'sort_release_popularity' => 'Popularity',
	'vpdb_releases_count' => "{# [1:release][?:releases]}",

	// releases - download
	'vpdb_download_flavor_each' => "<b>%s</b> Orientation, <b>%s</b> Lighting",
	'vpdb_download_flavor_both_universal' => "<b>Universal</b> Orientation and Lighting",
	'vpdb_download_media' => "Media",
	'vpdb_download_include_game_media' => "Include game media",
	'vpdb_download_include_playfield_image' => "Include playfield shot",
	'vpdb_download_include_playfield_video' => "Include playfield video",
	'vpdb_download_roms' => "ROMs",
	'vpdb_download_no_roms_available' => "No ROMs available for this game.",
	'vpdb_release_download_teaser' => "Create an account or sign in to download",

	// notifications
	'__indefart_vpdb_release_comment' => 'a comment on',
	'__indefart_vpdb_release' => 'a Table Release',
	'__defart_vpdb_release' => 'Table Release',

	// streams
	'vpdb_settings_content' => 'Content',
	'all_activity_vpdb_releases' => 'Show Releases in Streams',
	'vpdb_user_activity_comment' => "%s commented on %s",
	'vpdb_release_stream_content_type' => 'Table Release',
	'vpdb_user_own_activity_release' => '%s posted <b>%s</b> under Table Releases',
	'view_this_release' => 'View this release',
	'vpdb_release_pl' => 'Released Tables',
	'vpdb_release_comment_pl' => 'Commented Table Releases',

);