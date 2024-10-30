<?php

/**
 *
 */
function jrio_url_option() 
{
	$jrioUrl = get_option('jrio_option_name')['jrio_url'];
	
	if ($jrioUrl)
	{	
		$urlLen = strlen($jrioUrl);
		if (
			$urlLen >= 1
			&& substr_compare($jrioUrl, '/', $urlLen - 1, 1) === 0
			)
		{
			$jrioUrl = substr($jrioUrl, 0, $urlLen - 1);
		}
    }

	return $jrioUrl;
}

/**
 *
 */
function jrio_proxy( $request ) 
{
	$jrioUrl = jrio_url_option();
	if (!$jrioUrl)
	{
		return new WP_Error( 'jrio_error', 'Missing JasperReports IO URL.', array( 'status' => '500' ) );
	}

	$jrioPath =  $request->get_route();
	$jrioPath = str_replace('/jrio/v1', '', $jrioPath);

	return jrio_do_proxy($request, $jrioUrl, $jrioPath);
}

/**
 *
 */
function jrio_client_proxy( $request ) 
{
	$jrioUrl = jrio_url_option();
	if (!$jrioUrl)
	{
		return new WP_Error( 'jrio_error', 'Missing JasperReports IO URL.', array( 'status' => '500' ) );
	}

	$jrioPath =  $request->get_route();
	$jrioPath = str_replace('/jrio-client/v1', '', $jrioPath);

	return jrio_do_proxy($request, $jrioUrl . '-client', $jrioPath);
}

/**
 *
 */
function jrio_do_proxy( $request, $targetUrl, $proxyPath ) 
{
	// ------
	// declare getallheaders function as some versions of PHP might not have it
	// ------

	if (!function_exists('getallheaders'))
	{
		function getallheaders()
		{
			$headers = [];
			foreach ($_SERVER as $name => $value)
			{
				if (substr($name, 0, 5) == 'HTTP_' || $name == 'CONTENT_TYPE')
				{
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', str_replace('HTTP_', '', $name)))))] = $value;
				}
			}
			return $headers;
		}
	}


	// ------
	// prepare the request headers
	// ------
	
	$allRequestHeaders = getallheaders();
	$processedHeaders = array();
	$origin = "";
	foreach ($allRequestHeaders as $name => $value) 
	{
		// Get Origin to return
		if (strtolower($name) == 'origin') 
		{
			$origin = $value;
		}
		
		// Suppress some headers.
		if (
			strtolower($name) == 'cookie'
			|| strtolower($name) == 'content-length'
			) 
		{
			// skip it. No cookies in JRIO
		}
		else
		{
			$processedHeaders += [ $name => $value ];
		}
	}
	

	// ------
	// prepare the proxy request url
	// ------
	
	$queryParams = $request->get_query_params();
	if (!empty($queryParams)) 
	{
		$proxyPath = $proxyPath . '?' . http_build_query($queryParams);
	}


	// ------
	// prepare the proxy request arguments
	// ------
	
	$args = array();
	$args += [ 'headers' => $processedHeaders ];
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) 
	{
		$args += [ 'method' => 'POST' ];
		$args += [ 'body' => $request->get_body() ];
	}
	else if (! $_SERVER['REQUEST_METHOD'] == 'GET')
	{
		$args += [ 'method' => 'GET' ];
	}


	// ------
	// make the proxy call
	// ------
	
	$response = wp_remote_get ( $targetUrl . $proxyPath, $args );
	if ( is_wp_error ($response) ) 
	{
		return $response;
	}

	
	// ------
	// prepare the response headers and body
	// ------
	
	$restResponseHeaders = array();

	$body = wp_remote_retrieve_body($response);
	$bodyLength = strlen($body);

	$allResponseHeaders = wp_remote_retrieve_headers($response);
	
	// Propagate headers to response.
	foreach ( $allResponseHeaders as $headerName => $headerValue ) 
	{
		$upperHeaderName = strtoupper($headerName);
		
		if (strpos($upperHeaderName, 'ACCESS-CCONTROL-ALLOW-ORIGIN') !== false) 
		{
			$restResponseHeaders += [ 'Access-Control-Allow-Origin' => $origin ];
		}
		else if (
			is_array($headerValue)                                       // headers with array values will cause error downstream in WP 
			|| strpos($upperHeaderName, 'TRANSFER-ENCODING') !== false   // skip chunking; let the Server/PHP use its own
			|| strpos($upperHeaderName, 'CONTENT-ENCODING') !== false    // skip encoding; let the Server/PHP use its own 
			|| strpos($upperHeaderName, 'CONTENT-LENGTH') !== false      // we deal with content length separately
			) 
		{
			// skip header
		}
		else
		{
			if (
				strpos($upperHeaderName, 'CONTENT-TYPE') !== false
				&& strpos($headerValue, 'json') !== false
				) 
			{
				$body = json_decode($body);
				$bodyLength = -1;
			}
			
			$restResponseHeaders += [ $headerName => $headerValue ];
		}
	}

	if ($bodyLength > 0)
	{
		$restResponseHeaders += [ 'Content-Length' => $bodyLength ];
	}

	if (strpos($targetUrl, 'jrio-client') !== false)
	{
		$restResponseHeaders += [ 'Cache-Control', 'max-age=3600' ];
	}


	// ------
	// create and return response
	// ------

	$restResponse = new WP_REST_Response();

	$restResponse->set_status(wp_remote_retrieve_response_code($response));
	$restResponse->set_headers($restResponseHeaders);
	$restResponse->set_data($body);

	return $restResponse;
}

?>
