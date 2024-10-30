<?php

/**
 *
 */
function jrio_reports( $request ) 
{
	$queryParams = $request->get_query_params();

	if (is_user_logged_in())
	{
		$currentUser = wp_get_current_user();
	
		$queryParams['WP_User_ID'] = $currentUser->ID;
		$queryParams['WP_User_login'] = $currentUser->user_login;
		$queryParams['WP_User_email'] = $currentUser->user_email;
		$queryParams['WP_User_firstname'] = $currentUser->user_firstname;
		$queryParams['WP_User_lastname'] = $currentUser->user_lastname;
		$queryParams['WP_User_display_name'] = $currentUser->display_name;
		$queryParams['WP_User_roles'] = $currentUser->roles[0];
	}
	else
	{
		$queryParams['WP_User_ID'] = 0;
		$queryParams['WP_User_login'] = '';
		$queryParams['WP_User_email'] = '';
		$queryParams['WP_User_firstname'] = '';
		$queryParams['WP_User_lastname'] = '';
		$queryParams['WP_User_display_name'] = '';
		$queryParams['WP_User_roles'] = '';
	}

	$baseUrl = parse_url(get_home_url(), PHP_URL_PATH) . '/'. 
		rest_get_url_prefix() . '/jrio/v1';
	$queryParams['baseUrl'] = $baseUrl;

	$request->set_query_params($queryParams);

	return jrio_proxy($request);
}


/**
 *
 */
function jrio_report_execution( $request ) 
{
	$wpParams = [];

	if (is_user_logged_in())
	{
		$currentUser = wp_get_current_user();
	
		$wpParams[] = jrio_report_param_object('WP_User_ID', $currentUser->ID);
		$wpParams[] = jrio_report_param_object('WP_User_login', $currentUser->user_login);
		$wpParams[] = jrio_report_param_object('WP_User_email', $currentUser->user_email);
		$wpParams[] = jrio_report_param_object('WP_User_firstname', $currentUser->user_firstname);
		$wpParams[] = jrio_report_param_object('WP_User_lastname', $currentUser->user_lastname);
		$wpParams[] = jrio_report_param_object('WP_User_display_name', $currentUser->display_name);
		$wpParams[] = jrio_report_param_object('WP_User_roles', $currentUser->roles);
	}
	else
	{
		$wpParams[] = jrio_report_param_object('WP_User_ID', 0);
		$wpParams[] = jrio_report_param_object('WP_User_login', '');
		$wpParams[] = jrio_report_param_object('WP_User_email', '');
		$wpParams[] = jrio_report_param_object('WP_User_firstname', '');
		$wpParams[] = jrio_report_param_object('WP_User_lastname', '');
		$wpParams[] = jrio_report_param_object('WP_User_display_name', '');
		$wpParams[] = jrio_report_param_object('WP_User_roles', '');
	}

	$postData = $request->get_body();
	$postBodyJson = json_decode($postData);
	
	// "parameters":{"reportParameter":[{"name":"a","value":["A", "Z", "1"]},{"name":"b","value":["b"]}]}}
	// get to the array of reportParameters
	if (!isset($postBodyJson->parameters)) 
	{
		$param = new stdClass;
		$param->reportParameter = [];
		$postBodyJson->parameters = $param;
	}
	
	$postBodyJson->parameters->reportParameter = array_merge($postBodyJson->parameters->reportParameter, $wpParams);
	$postData = json_encode($postBodyJson);
	
	$request->set_body($postData);
	
	$response = jrio_proxy($request);
	if ( is_wp_error ($response) ) 
	{
		return $response;
	}
	
	$jsonBody = $response->get_data();
	if (isset($jsonBody->requestId))
	{
		jrio_add_session_execution_id($jsonBody->requestId);
	}
	else
	{
		// could be an error. Leave it in the response.
	}
		
	return $response;
}


/**
 *
 */
function jrio_verified_execution_id_proxy( $request ) 
{
	$errResponse = jrio_verify_report_execution_id($request, false);
	if (!is_null($errResponse))
	{
		return $errResponse;
	}

	return jrio_proxy($request);
}


/**
 *
 */
function jrio_updated_verified_execution_id_proxy( $request ) 
{
	$errResponse = jrio_verify_report_execution_id($request, true);
	if (!is_null($errResponse))
	{
		return $errResponse;
	}

	return jrio_proxy($request);
}


/**
 *
 */
function jrio_rest_api_init() 
{
	register_rest_route(
		'jrio/v1', '/rest_v2/reports/(?P<reportUri>.+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_reports',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions', 
		array(
			'methods' => 'POST',
			'callback' => 'jrio_report_execution',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/status', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/info', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/pages/(?P<pageNumber>[^/]+)/status', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/exports/(?P<exportId>[^/]+)/outputResource', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/exports', 
		array(
			'methods' => 'POST',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/parameters', 
		array(
			'methods' => 'POST',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/exports/(?P<exportId>[^/]+)/status', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_updated_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/exports/(?P<exportId>[^/]+)/attachments/(?P<attachmentName>[^/]+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_verified_execution_id_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/fonts/(?P<fontName>.+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/resources/(?P<reportUri>.+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/bundles/(?P<bundleName>.+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/settings/dateTimeSettings', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_proxy',
			)
		);

	register_rest_route(
		'jrio/v1', '/rest_v2/reportExecutions/(?P<executionId>[^/]+)/runAction', 
		array(
			'methods' => 'POST',
			'callback' => 'jrio_proxy',
			)
		);

	register_rest_route(
		'jrio-client/v1', '/(?P<resourcePath>.+)', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_client_proxy',
			)
		);

	register_rest_route(
		'jrio-viewer/v1', '/report', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_report_html',
			)
		);

	register_rest_route(
		'jrio-viewer/v1', '/overlay', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_overlay_html',
			)
		);

	register_rest_route(
		'jrio-viewer/v1', '/viewer', 
		array(
			'methods' => 'GET',
			'callback' => 'jrio_viewer_html',
			)
		);
} 

add_action( 'rest_api_init', 'jrio_rest_api_init');


/**
 *
 */
function jrio_rest_pre_serve_request( $served, $result, $request, $server ) 
{
	if (
		!$result->is_error()
		&& (strpos($request->get_route(), 'outputResource') !== false
		|| strpos($request->get_route(), 'fonts/') !== false
		|| strpos($request->get_route(), 'attachments/img') !== false
		|| strpos($request->get_route(), 'reports/') !== false
		|| strpos($request->get_route(), 'resources/') !== false
		|| (strpos($request->get_route(), 'jrio-client/v1') !== false
			&& strpos($request->get_route(), 'bi/report/schema') == false)
			)
		|| strpos($request->get_route(), 'jrio-viewer/v1') !== false
		)
	{
		echo $result->get_data();
		return true;
	}
	return false;
}


add_filter( 'rest_pre_serve_request', 'jrio_rest_pre_serve_request', 10, 4 );


/**
 *
 */
function jrio_verify_report_execution_id($request, $toUpdate)
{
	$executionId = $request['executionId'];
	
	$errResponse = null;
	
	if (is_null($executionId))
	{
		$errResponse = new WP_Error( 'jrio_error', 'Missing report execution ID.', array( 'status' => '400' ) );
	}
	else
	{
		if (!jrio_has_session_execution_id($executionId, $toUpdate))
		{
			$errResponse = new WP_Error( 'jrio_error', 'Invalid report execution ID.', array( 'status' => '403' ) );
		}
	}
	
	return $errResponse;
 }


/**
 *
 */
function jrio_report_param_object($name, $values)
{
	$param = new stdClass;
	$param->name = $name;
	
	if (is_array($values)) 
	{
		$param->value = $values;
	}
	else
	{
		$param->value = [ $values ];
	}
	
	return $param;
 }

?>
