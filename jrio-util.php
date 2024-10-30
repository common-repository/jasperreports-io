<?php
define('JRIO_DEMO_URL', 'https://demo.jaspersoft.com/jrio');


/**
 *
 */
function jrio_url()
{
	return get_rest_url() . "jrio/v1";
}


/**
 *
 */
function jrio_rest_url()
{
	return jrio_url() . "/rest_v2";
}




/**
 *
 */
function jrio_scripts_url()
{
	return get_rest_url() . "jrio-client/v1";
}


/**
 *
 */
function jrio_report_data($atts = [])
{
	$data = array(
  		'reportUnitUri' => $atts['report'],
  		'ignorePagination' => false,
  		'async' => true,
  		'baseUrl' => jrio_url(),
//		'attachmentsPrefix' => jrio_rest_url() . '/reportExecutions/{reportExecutionId}/exports/{exportExecutionId}/attachments/',
  		'parameters' => array(
  			'reportParameter' => array()
  			),
  		'reportLocale' => 'en_US',
  		'reportTimeZone' => 'GMT'
  		);
  	
  	$reportParameter = array();
  		
	$parameterNames = array();
	if (array_key_exists('mappings', $atts))
	{
		$mappings = $atts['mappings'];
		if ($mappings)
		{
			$mappingsArray = explode(",", $mappings);
			foreach($mappingsArray as $mapping)
			{
				$parameter = explode(":", $mapping);
				$parameterNames += [ $parameter[0] => $parameter[1] ];
			}
		}
	}
	
	foreach ($atts as $attrName => $attrValue)
	{
		if (
			$attrName != 'report'
			&& $attrName != 'mappings'
			&& $attrName != 'scale'
			&& $attrName != 'interactive'
			&& $attrName != 'width'
			&& $attrName != 'height'
			)
		{
			$paramName = $attrName;
			if (array_key_exists($paramName, $parameterNames))
			{
				$paramName = $parameterNames[$paramName];
			}
			
			$reportParameter += array( array('name' => $paramName, 'value' => array($attrValue) ) ); 
		}
	}
  	
	$data['parameters']['reportParameter'] = $reportParameter;
  	
	return $data;
}


?>
