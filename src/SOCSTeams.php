<?php
namespace FredBradley\SOCS;

class SOCSTeams {

	private $requestUrl = "https://www.schoolssports.com/school/xml/mso-sport.ashx?";

	public function __construct($schoolID, $apiKey) {

		$query = http_build_query([
			"ID" => $schoolID,
			"key" => $apiKey,
			"data" => "teams"
		]);

		$transient_name = "cs_socs_".$schoolID."_teamslist";

		if (!$this->result = get_transient($transient_name)) {
			$xml = simplexml_load_file($this->requestUrl.$query);
			$json = json_encode($xml);
			$this->result = json_decode($json);
			set_transient($transient_name, $this->result, 2 * DAY_IN_SECONDS);
		}

	}
	public function findTeams($find, $value) {
		$teams = [];
		$negative = false;

		if (substr($value, 0, 1)==="-") {
			$negative = true;
			$value = substr($value, 1);
		}

		foreach ($this->result->team as $team) {

			if ($negative) {
				if ($team->$find != (string) $value) {
					array_push($teams, $team);
				}
			} else {
				if ($team->$find == (string) $value) {
					array_push( $teams, $team );
				}
			}

		}

		return $teams;
	}
	public function allTeams() {
		return $this->result->team;
	}
	public function getTeam($teamID) {
		foreach ($this->result->team as $team) {

			if ($team->teamid == (string)$teamID)
				return $team;
		}
		return null;
	}
}
