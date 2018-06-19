<?php
namespace FredBradley\SOCS;

class Settings {

	private $option_name = "socs-sports";

	public $settings;

	public function __construct($setting=null) {
		if (!get_option($this->option_name)) {
			update_option($this->option_name, array("schoolID" => 1, "apiKey" => 123, "intoFuture" => 8));
		}
		$this->settings = get_option($this->option_name);

	}

	public function __get($name) {
		return $this->settings[$name];
	}

}
