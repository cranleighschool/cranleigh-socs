<?php /*
Plugin Name: FRB's SOCS Integration
Plugin URI: http://github-url-here.com
Description: We've kinda copied Wellington here, but oh well!
Version: 1.1.0
Author: Fred Bradley
Author URI: http://fred.im/
License: GPL2
*/

namespace FredBradley\SOCS;

require_once 'vendor/autoload.php';

$settings_api = new SettingsApi();

new Plugin('1.1.0');

