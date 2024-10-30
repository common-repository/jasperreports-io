<?php

/**
 *
 */
function jrio_report_html() 
{
	ob_start();

    wp_enqueue_style('jrio-themes-reset-css', jrio_scripts_url() . '/themes/default/reset.css');
    wp_enqueue_style('jrio-themes-theme-css', jrio_scripts_url() . '/themes/default/theme.css');
    wp_enqueue_style('jrio-themes-page-specific-css', jrio_scripts_url() . '/themes/default/pageSpecific.css');
    wp_enqueue_style('jrio-themes-buttons-css', jrio_scripts_url() . '/themes/default/buttons.css');
    wp_enqueue_style('jrio-themes-controls-css', jrio_scripts_url() . '/themes/default/controls.css');
    wp_enqueue_style('jrio-themes-lists-css', jrio_scripts_url() . '/themes/default/lists.css');
    wp_enqueue_style('jrio-themes-containers-css', jrio_scripts_url() . '/themes/default/containers.css');
	wp_enqueue_style('jrio-report-css', plugin_dir_url(__FILE__) . 'report.css');
	wp_enqueue_script('jrio-report-js', plugin_dir_url(__FILE__) . 'report.js', ['jquery', 'lodash'], '1.0.0', true);
	wp_enqueue_script('jrio-jrio-js', jrio_scripts_url() . '/optimized-scripts/jrio/jrio.js', ['jquery'], '1.0.0', false);
?>
<html>
<head>
    <title>JasperReports IO Report</title>
    <meta http-equiv="Content-Type" content="text/html; charset='UTF-8'"/>
<?php
	wp_print_styles();
	wp_print_head_scripts();
?>
</head>
<body>
<div id="reportContainer" data-rest-url="<?= get_rest_url() ?>"
    data-overlay-url="<?= get_rest_url() . 'jrio-viewer/v1/overlay' . (get_option('permalink_structure') ? '?' : '&') . 'executionId=' . $_GET['executionId'] ?>"></div>
<div class="jrio_report_toolbar">
  <div class="jrio_report_middle">
    <div class="jrio_report_expand" onclick="jrio_open_viewer(); return false;"></div>
  </div>
</div>
<?php
	wp_print_footer_scripts();
?>
</body>
</html>
<?php

	$restResponse = new WP_REST_Response();
	$restResponse->header( 'Content-Type', 'text/html');
	$restResponse->set_data(ob_get_clean());

	return $restResponse;
}

?>
