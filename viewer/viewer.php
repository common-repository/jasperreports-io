<?php

/**
 *
 */
function jrio_viewer_html() 
{
	ob_start();

    wp_enqueue_style('jrio-themes-reset-css', jrio_scripts_url() . '/themes/default/reset.css');
    wp_enqueue_style('jrio-themes-theme-css', jrio_scripts_url() . '/themes/default/theme.css');
    wp_enqueue_style('jrio-themes-page-specific-css', jrio_scripts_url() . '/themes/default/pageSpecific.css');
    wp_enqueue_style('jrio-themes-buttons-css', jrio_scripts_url() . '/themes/default/buttons.css');
    wp_enqueue_style('jrio-themes-controls-css', jrio_scripts_url() . '/themes/default/controls.css');
    wp_enqueue_style('jrio-themes-lists-css', jrio_scripts_url() . '/themes/default/lists.css');
    wp_enqueue_style('jrio-themes-containers-css', jrio_scripts_url() . '/themes/default/containers.css');
	wp_enqueue_style('jrio-viewer-css', plugin_dir_url(__FILE__) . 'viewer.css');
	wp_enqueue_script('jrio-viewer-js', plugin_dir_url(__FILE__) . 'viewer.js', ['jquery', 'lodash'], '1.0.0', true);
	wp_enqueue_script('jrio-jrio-js', jrio_scripts_url() . '/optimized-scripts/jrio/jrio.js', ['jquery'], '1.0.0', false);
?>
<html>
<head>
    <title>JasperReports IO Viewer</title>
    <meta http-equiv="Content-Type" content="text/html; charset='UTF-8'"/>
<?php
	wp_print_styles();
	wp_print_head_scripts();
?>
</head>
<body>
<div id="reportViewer">
    <div id="reportContainer" data-rest-url="<?= get_rest_url() ?>"></div>
    <div id="viewerToolbar" class="toolbar">
        <!-- ========== LEFT BUTTON SET =========== -->
        <ul class="list buttonSet j-toolbar">
            <li class="leaf">
                <button id="export" class="button capsule mutton up last" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                        <span class="indicator"></span>
                    </span>
                </button>
            </li>
            <li class="leaf divider"></li>
            <li class="leaf">
                <button id="undo" class="button capsule up first" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="leaf">
                <button id="redo" class="button capsule up middle" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="leaf">
                <button id="undoAll" class="button capsule up last" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="leaf divider"></li>
        </ul>
        <!-- ========== END LEFT BUTTON SET =========== -->

        <!-- ========== RIGHT BUTTON SET =========== -->
        <ul class="control toolsRight j-toolbar list">
            <!-- ========== ZOOM =========== -->
            <li class="leaf divider"></li>
            <li class="control zoom leaf">
                <button id="zoom_out" class="button action square move zoomOut up" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="control zoom leaf">
                <button id="zoom_in" class="button action square move zoomIn up" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="control zoom leaf j-dropdown">
                <label for="zoom_value" class="control input textPlus inline">
                    <input id="zoom_value" type="text" value="100%" name="zoom_value" disabled="disabled">
                    <button id="zoom_value_button" class="button disclosure" disabled="disabled">
                        <span class="icon"></span>
                    </button>
                </label>
            </li>
            <li class="leaf divider"></li>

            <!-- ========== PAGINATION =========== -->
            <li class="control paging leaf page_first">
                <button id="page_first" class="button action square move toLeft up" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="control paging leaf page_prev">
                <button id="page_prev" class="button action square move left up" disabled="disabled">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="control paging leaf j-dropdown">
                <label class="control input text inline" for="page_current">
                    <span class="wrap">Page</span>
                    <input id="page_current" type="text" name="currentPage" value="">
                    <span class="wrap" id="page_total">of <span id="totalPagesNo"></span></span>
                </label>
            </li>
            <li class="control paging leaf page_next">
                <button id="page_next" class="button action square move right up">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
            <li class="control paging leaf page_last">
                <button id="page_last" aria-label="Last" class="button action square move toRight up">
                    <span class="wrap">
                        <span class="icon"></span>
                    </span>
                </button>
            </li>
        </ul>
    </div>
</div>
<div id="viewerElements">
    <div id="exportMenu" class="menu vertical dropDown fitable hidden">
        <div class="content">
            <ul id="menuList">
                <li class="leaf">
                    <p class="wrap button" data-val="pdf"><span class="icon"></span>PDF</p>
                </li>
                <li class="leaf">
                    <p class="wrap button" data-val="xlsx"><span class="icon"></span>XLSX</p>
                </li>
                <li class="leaf">
                    <p class="wrap button" data-val="docx"><span class="icon"></span>DOCX</p>
                </li>
                <li class="leaf">
                    <p class="wrap button" data-val="pptx"><span class="icon"></span>PPTX</p>
                </li>
                <li class="leaf">
                    <p class="wrap button" data-val="csv"><span class="icon"></span>CSV</p>
                </li>
                <li class="leaf">
                    <p class="wrap button" data-val="xls"><span class="icon"></span>XLS</p>
                </li>
                <li class="leaf">
                    <p class="wrap button" data-val="rtf"><span class="icon"></span>RTF</p>
                </li>
            </ul>
        </div>
    </div>
    <div id="zoomMenu" class="menu vertical dropDown fitable hidden">
        <div class="content">
            <ul>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="0.1"><span class="icon"></span>10%</p>
                </li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="0.25"><span class="icon"></span>25%</p></li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="0.5"><span class="icon"></span>50%
                    </p></li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="0.75"><span class="icon"></span>75%</p>
                </li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="1"><span class="icon"></span>100%</p>
                </li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="1.25"><span class="icon"></span>125%</p>
                </li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="2"><span class="icon"></span>200%</p>
                </li>
                <li class="leaf">
                    <p class="wrap toggle button" data-val="4"><span class="icon"></span>400%</p>
                </li>
            </ul>
        </div>
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
