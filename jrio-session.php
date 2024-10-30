<?php
define('JRIO_SESSION_ID_COOKIE', 'wp_jrio_session_id');
define('JRIO_DATABASE_VERSION', '1.0.0');


/**
 *
 */
function jrio_install()
{
	global $jrio_db_version;
	$jrio_db_version = JRIO_DATABASE_VERSION;
	$jrio_installed_db_version = get_option("jrio_db_version");

	if ($jrio_installed_db_version != $jrio_db_version)
	{
		global $wpdb;

		$sessions_table_name = $wpdb->prefix."jrio_sessions";
	
		// 1. You have to put each field on its own line in your SQL statement.
		// 2. You have to have two spaces between the words PRIMARY KEY and the definition of your primary key.
		// 3. You must use the key word KEY rather than its synonym INDEX and you must include at least one KEY.

		$sql = 
			"CREATE TABLE $sessions_table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			session_id varchar(128) NOT NULL,
			execution_id varchar(128) NOT NULL,
			execution_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY execution_id (session_id, execution_id),
			KEY execution_time (execution_time)
			);";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');

		dbDelta($sql);

		update_option("jrio_db_version", $jrio_db_version);
	}
}


/**
 *
 */
function jrio_start_session() 
{
	if (!jrio_session_id()) 
	{
		setcookie(
			JRIO_SESSION_ID_COOKIE, 
			md5(JRIO_SESSION_ID_COOKIE . rand()), 
			0, // session expiration
			parse_url(get_site_url(), PHP_URL_PATH)
			);
	}
}


/**
 *
 */
function jrio_session_id() 
{
	$jrioSessionId = null;
	
	if (array_key_exists(JRIO_SESSION_ID_COOKIE, $_COOKIE))
	{
		$jrioSessionId = $_COOKIE[JRIO_SESSION_ID_COOKIE];
	}
	
	return $jrioSessionId;
}


/**
 *
 */
function jrio_end_session() 
{
	setcookie(
		JRIO_SESSION_ID_COOKIE, 
		'', 
		time() - 3600, 
		parse_url(get_site_url(), PHP_URL_PATH)
		);
}


/**
 *
 */
function jrio_add_session_execution_id($executionId)
{
	global $wpdb;

	$wpdb->query("DELETE FROM " . $wpdb->prefix. "jrio_sessions WHERE execution_time < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");

	$wpdb->insert( 
		$wpdb->prefix."jrio_sessions", 
		array( 
			'session_id' => jrio_session_id(), 
			'execution_id' => $executionId 
			), 
		array( 
			'%s',
			'%s'
			) 
		);
}


/**
 *
 */
function jrio_has_session_execution_id($executionId, $toUpdate) 
{
	global $wpdb;

	$executions_count = 
		$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."jrio_sessions WHERE session_id = '". 
			jrio_session_id() ."' AND execution_id = '". $executionId ."'");
	
	if ($executions_count > 0)
	{
		if ($toUpdate)
		{
			$wpdb->query("UPDATE " . $wpdb->prefix. "jrio_sessions SET execution_time = NOW() WHERE session_id = '". 
				jrio_session_id() ."' AND execution_id = '". $executionId ."'");
		}
		
		return true;
	}
	
	return false;
}


add_action('init', 'jrio_start_session', 1);
add_action('wp_logout', 'jrio_end_session');
add_action('wp_login', 'jrio_end_session');

?>
