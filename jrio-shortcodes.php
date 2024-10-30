<?php


/**
 *
 */
function jrio_test_shortcode($atts = [], $content = null)
{
	ob_start();
		
	$value = '';
	
	if (isset($_SESSION[JRIO_SESSION_EXECUTIONS])) 
	{
		$sessionExecutionIds = $_SESSION[JRIO_SESSION_EXECUTIONS];
		foreach ($sessionExecutionIds as $executionId)
		{
			$value = $value . "<br/>" . $executionId;
		}
	}
?>
<?= $value ?>
<?php
	return ob_get_clean();
}


/**
 *
 */
function jrio_shortcode($atts = [], $content = null)
{
	ob_start();

	$jrioUrl = jrio_url_option();
	if (!$jrioUrl)
	{
?>
<a href="#" onclick="alert('The JasperReports IO plugin is not properly configured.'); return false;">[jrio ...]</a>
<?php
		return ob_get_clean();
	}
	
	global $to_create_jrio_viewer;
	$to_create_jrio_viewer = true;

	wp_enqueue_style('jrio-util-css');
	wp_enqueue_script('jrio-report-util-js');
	wp_enqueue_script('jrio-jrio-js');

	$jrioUrl = jrio_rest_url() . '/reportExecutions' . jrio_append_wpnonce();

	static $frameIndex = 1;
	$frameId = "reportFrame" . $frameIndex++;
	
	$data = jrio_report_data($atts);
	
	$viewerUrl = get_rest_url() . 'jrio-viewer/v1/report' . (get_option('permalink_structure') ? '?' : '&');
	if (array_key_exists('scale', $atts))
	{
		$viewerUrl = $viewerUrl . 'scale=' . $atts['scale'] . '&';
	}
	if (array_key_exists('interactive', $atts))
	{
		$viewerUrl = $viewerUrl . 'interactive=' . $atts['interactive'] . '&';
	}
	$viewerUrl = $viewerUrl . 'executionId=';
	
	$width = '100%';
	if (array_key_exists('width', $atts))
	{
		$width = $atts['width'];
	}
	
	$height = '50px';
	if (array_key_exists('height', $atts))
	{
		$height = $atts['height'];
	}
?>
<iframe id="<?= $frameId ?>" class="jrio_report_frame"
    data-jrio-url="<?= $jrioUrl ?>" 
    data-jrio-data='<?= json_encode($data) ?>' 
    data-viewer-url="<?= $viewerUrl ?>" 
    width="<?= $width ?>" 
    height="<?= $height ?>"
    src="<?= plugins_url( 'viewer/load.html', __FILE__ ) ?>"></iframe>
<?php
	return ob_get_clean();
}


/**
 *
 */
function jrio_export_shortcode($atts = [], $content = null)
{
	ob_start();

	$jrioUrl = jrio_url_option();
	if (!$jrioUrl)
	{
?>
<a href="#" onclick="alert('The JasperReports IO plugin is not properly configured.'); return false;"><?= $content ?></a>
<?php
		return ob_get_clean();
	}
	
	$link = jrio_rest_url() . '/reports' . 
		$atts['report'] . '.' . $atts['output'] . jrio_append_wpnonce();
?>
<a href="<?= $link ?>" target="_blank"><?= $content ?></a>
<?php
	return ob_get_clean();
}


/**
 *
 */
function jrio_viewer_shortcode($atts = [], $content = null)
{
	ob_start();

	$jrioUrl = jrio_url_option();
	if (!$jrioUrl)
	{
?>
<a href="#" onclick="alert('The JasperReports IO plugin is not properly configured.'); return false;"><?= $content ?></a>
<?php
		return ob_get_clean();
	}
	
	global $to_create_jrio_viewer;
	$to_create_jrio_viewer = true;

	$jrioUrl = jrio_rest_url() . '/reportExecutions' . jrio_append_wpnonce();
	
	static $viewerIndex = 1;
  	$viewerId = 'viewer' . $viewerIndex++;

	$data = jrio_report_data($atts);
?>
<a id="<?= $viewerId ?>" 
    data-jrio-url="<?= $jrioUrl ?>" 
    data-jrio-data='<?= json_encode($data) ?>' 
    data-viewer-url="<?= get_rest_url() . 'jrio-viewer/v1/overlay' . (get_option('permalink_structure') ? '?' : '&') . 'executionId=' ?>" 
    onclick="jrio_call_viewer('#<?= $viewerId ?>'); return false;"
    href="#"><?= $content ?></a>
<?php
	return ob_get_clean();
}


/**
 *
 */
function jrio_create_viewer_frame()
{
	global $to_create_jrio_viewer;
	if ($to_create_jrio_viewer)
	{
		wp_enqueue_style('jrio-util-css');
		wp_enqueue_script('jrio-viewer-util-js');
?>
<div id="jrio_viewer">
  <iframe id="jrio_viewer_frame" src="" width="100%" height="100%" frameborder="0" style="display: inline-block;"></iframe>
</div>
<?php
	}
}


/**
 *
 */
function jrio_append_wpnonce()
{
	return (get_option('permalink_structure') ? '?' : '&') . '_wpnonce=' . wp_create_nonce('wp_rest');
}


/**
 *
 */
function jrio_register_scripts()
{
	wp_register_style('jrio-util-css', plugin_dir_url(__FILE__) . 'viewer/util.css');
	wp_register_script('jrio-report-util-js', plugin_dir_url(__FILE__) . 'viewer/report-util.js', ['jquery'], '1.0.0', false);
	wp_register_script('jrio-viewer-util-js', plugin_dir_url(__FILE__) . 'viewer/viewer-util.js', ['jquery'], '1.0.0', false);
	wp_register_script('jrio-jrio-js', jrio_scripts_url() . '/optimized-scripts/jrio/jrio.js', ['jquery'], '1.0.0', false);
}


add_action('wp_enqueue_scripts', 'jrio_register_scripts');

add_shortcode('jrio', 'jrio_shortcode');
add_shortcode('jrio_export', 'jrio_export_shortcode');
add_shortcode('jrio_viewer', 'jrio_viewer_shortcode');
add_shortcode('jrio_test', 'jrio_test_shortcode');

add_action('wp_footer', 'jrio_create_viewer_frame');

?>
