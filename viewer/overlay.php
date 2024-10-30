<?php

/**
 *
 */
function jrio_overlay_html() 
{
	ob_start();

	wp_enqueue_style('jrio-overlay-css', plugin_dir_url(__FILE__) . 'overlay.css');
	wp_enqueue_script('jrio-overlay-js', plugin_dir_url(__FILE__) . 'overlay.js', ['jquery'], '1.0.0', false);
?>
<html>
<head>
    <title>JasperReports IO Overlay</title>
    <meta http-equiv="Content-Type" content="text/html; charset='UTF-8'"/>
<?php
	wp_print_styles();
	wp_print_head_scripts();
?>
</head>
<body>
<div id="main_container">
<table>
  <tr height="60px">
    <td width="60px"><div style="display: block; width: 60px; height: 60px;"/></td>
    <td width="100%"><div style="display: block; width: 100%; height: 60px;"/></td>
    <td width="60px"><div style="display: table-cell; width: 60px; height: 60px; text-align: center; vertical-align: middle;">
    <div class="jrio_viewer_close" onclick="jrio_hide_viewer()"/>
    </div></td>
  </tr>
  <tr height="100%">
    <td width="60px">
    </td>
    <td width="100%">
    <div style="display: block; width: 100%; height: 100%;">
    <iframe src="<?= get_rest_url() . 'jrio-viewer/v1/viewer' . (get_option('permalink_structure') ? '?' : '&') . 'executionId=' . $_GET['executionId'] ?>" width="100%" height="100%" frameborder="0" style="display: inline-block;"></iframe>
    </div>
    </td>
    <td width="60px">
    </td>
  </tr>
  <tr height="60px">
    <td width="60px"><div style="display: block; width: 60px; height: 60px;"/></td>
    <td width="100%"><div style="display: block; width: 100%; height: 60px;"/></td>
    <td width="60px"><div style="display: block; width: 60px; height: 60px;"/></td>
  </tr>
</table>
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
