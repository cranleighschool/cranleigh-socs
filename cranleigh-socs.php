<?php
/*
Plugin Name: SOCS API Integration
Plugin URI: https://github.com/cranleighschool/cranleigh-socs
Description: We've kinda copied Wellington here, but oh well!
Version: 1.3.0
Author: Fred Bradley
Author URI: http://fred.im/
License: GPL2
*/

namespace FredBradley\SOCS;

require_once 'vendor/autoload.php';

$settings_api = new SettingsApi();

$updates = new PluginUpdateCheck("cranleigh-socs");

new Plugin();

