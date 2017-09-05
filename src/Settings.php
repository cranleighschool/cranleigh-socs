<?php
namespace FredBradley\SOCS;

class Settings {

	private $option_name = "socs-sports";

	public $settings;

	public function __construct($setting=null) {
		$this->settings = get_option($this->option_name);
	}

	public function __get($name) {
		return $this->settings[$name];
	}

}
