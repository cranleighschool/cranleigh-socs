<?php
namespace FredBradley\SOCS;

/**
 * Class SOCSResults
 *
 * @package FredBradley\SOCS
 */
class SOCSResults {

	private $school_id;
	private $api_key;
	public $weeks_back = 1;
	/**
	 * @var array
	 */
	public $json;

	/**
	 * @var string
	 */
	private $requestUrl = "https://www.schoolssports.com/school/xml/mso-sport.ashx?";

	/**
	 * SOCSResults constructor.
	 *
	 * @param int    $schoolID
	 * @param string $apiKey
	 */
	public function __construct(int $schoolID, string $apiKey) {

		$this->school_id = $schoolID;
		$this->api_key = $apiKey;
		$query = http_build_query([
			"ID" => $schoolID,
			"key" => $apiKey,
			"data" => "fixtures",
			"enddate" => date("d M Y"),
			"startdate" => date("d M Y", strtotime("-".$this->weeks_back." week"))
		]);

		$this->uri = $this->requestUrl.$query;
		$this->result = simplexml_load_file($this->requestUrl.$query);

	}

	private function getTeam($team_id) {
		$teams = new SOCSTeams($this->school_id, $this->api_key);
		return $teams->getTeam($team_id);
	}
	/**
	 * @param string $date
	 * @param bool   $asTimestamp
	 *
	 * @return false|int|string
	 */
	private function anglofyDate(string $date, bool $asTimestamp=false) {
		$dateParts = explode("/", $date);

		$day = $dateParts[0];
		$month = $dateParts[1];
		$year = $dateParts[2];

		$timestamp = strtotime($year."-".$month."-".$day);
		if ($asTimestamp===true) {
			return $timestamp;
		}
		return date("jS M", $timestamp);

	}

	/**
	 * @return string
	 */
	public function title() {
		return "<div class=\"tickerTapeTitle\">Latest Results</div>";
	}

	/**
	 * @return array
	 */
	public function tickerTape() {

		$output = [];

		foreach ($this->result as $fixture):

			if (isset($fixture->result) && socs_is_normal_sport_fixture($fixture)):
				$result = "<p class=\"fixture\"><span class=\"label label-default\">".$fixture->sport."</span> ".$this->getTeam($fixture->teamid)->teamname." vs ".$fixture->opposition.": ".$fixture->result;
				if (isset($fixture->pointsfor) && isset($fixture->pointsagainst)):
					$result .= " (".$fixture->pointsfor." - ".$fixture->pointsagainst.")";
				endif;
				$result .= "</p>";
			endif;

			array_push($output, $result);


		endforeach;

		if (count($this->result)==0) {
			$result = "<p class=\"fixture\">There have been no results reported in the last ".$this->weeks_back." weeks...</p>";
			array_push($output, $result);
		}

		return $output;
	}


}
