<?php
namespace FredBradley\SOCS;

/**
 * Class SOCSResults
 *
 * @package FredBradley\SOCS
 */
class SOCSResults {

	/**
	 * @var array
	 */
	public $json;

	/**
	 * @var string
	 */
	private $requestUrl = "https://www.schoolssports.com/school/xml/results.ashx?";

	/**
	 * SOCSResults constructor.
	 *
	 * @param int    $schoolID
	 * @param string $apiKey
	 */
	public function __construct(int $schoolID, string $apiKey) {
		$query = http_build_query([
			"ID" => $schoolID,
			"key" => $apiKey,
		]);
		$this->uri = $this->requestUrl.$query;
		$this->result = simplexml_load_file($this->requestUrl.$query);
		$this->json = ["thing" => "thingtwo"];
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

		$array = (array) $this->result;
		if (isset($array['fixture'])) {
			$fixtures = array_reverse( $array[ 'fixture' ] );
		} else {
			$fixtures = $array;
		}
		$weeks_to_count_back = 30;

		$count = 0;
		foreach ($fixtures as $fixture):

			if ($this->anglofyDate((string) $fixture->date, true) < (time() - ($weeks_to_count_back * WEEK_IN_SECONDS)))
				break;
			$result = "<p class=\"fixture\"><span class=\"label label-default\">".$fixture->sport."</span> ".$fixture->team." vs ".$fixture->opposition.": ".$fixture->result."</p>";
			array_push($output, $result);

			$count++;

		endforeach;

		if ($count == 0) {
			$result = "<p class=\"fixture\">There have been no results reported in the last ".$weeks_to_count_back." weeks...</p>";
			array_push($output, $result);
		}

		return $output;
	}


}
