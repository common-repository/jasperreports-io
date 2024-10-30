<?php
/*
Plugin Name: JasperReports IO
Plugin URI:  http://www.jaspersoft.com/products/jasperreports-io
Description: Seamlessly embed highly interactive reports and data visualizations inside your pages and blog posts using the TIBCO JasperReports® IO microservice engine.
Version:     1.0.0
License:     GPL2

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

require_once( plugin_dir_path( __FILE__ ) . 'jrio-util.php');
require_once( plugin_dir_path( __FILE__ ) . 'jrio-options.php');
require_once( plugin_dir_path( __FILE__ ) . 'jrio-rest.php');
require_once( plugin_dir_path( __FILE__ ) . 'jrio-proxy.php');
require_once( plugin_dir_path( __FILE__ ) . 'jrio-shortcodes.php');
require_once( plugin_dir_path( __FILE__ ) . 'jrio-session.php');

require_once( plugin_dir_path( __FILE__ ) . 'viewer/report.php');
require_once( plugin_dir_path( __FILE__ ) . 'viewer/overlay.php');
require_once( plugin_dir_path( __FILE__ ) . 'viewer/viewer.php');


register_activation_hook( __FILE__, 'jrio_install');

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'jrio_add_plugin_page_settings_link');

?>
