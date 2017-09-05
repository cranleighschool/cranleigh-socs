<?php
namespace FredBradley\SOCS;

class SOCS_Wrapper {
	private static $instance = null;
	public $startDate;
	public $endDate;
	public $numRows;
	public $result;
	private $key;
	private $ID;
	private $requestUrl;
	private $apiQuery;

	private function __construct()
    {
		$this->apiQuery = [];
    }

	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new self;
		}

		return self::$instance;
    }

    public function get()
    {
		$this->requestUrl = "https://www.schoolssports.com/school/xml/mso-sport.ashx?";

		$this->buildQuery("ID", $this->ID);
		$this->buildQuery("key", $this->key);
		$this->buildQuery("data", $this->data);

		if ($this->startDate) {
			$this->buildQuery("startdate", $this->startDate);
		}

		if ($this->endDate) {
			$this->buildQuery("enddate", $this->endDate);
		}

		$this->result = simplexml_load_file($this->requestUrl.http_build_query($this->apiQuery));

		$this->numRows = count($this->result);

		return self::$instance;
    }

    private function buildQuery(string $key, string $value) {
	    $this->apiQuery[$key] = $value;
    }

    public function setEndDate(int $timestamp) {
	    $this->endDate = date('j M Y', $timestamp);
	    return self::$instance;
    }

    public function setStartdate(int $timestamp) {
	    $this->startDate = date('j M Y', $timestamp);
	    return self::$instance;
    }

	public function setID(int $id) {
		$this->ID = $id;
		return self::$instance;
	}

	public function setKey(string $key) {
		$this->key = $key;
		return self::$instance;
	}

	public function setData(string $data) {
		$this->data = $data;
		return self::$instance;
	}

	public function asJson() {

		$json = json_encode($this);
		return $json;
	}

}
