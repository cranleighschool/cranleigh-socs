<?php
namespace FredBradley\SOCS;

class SOCSTeams {

	private $requestUrl = "https://www.schoolssports.com/school/xml/mso-sport.ashx?";

	public function __construct($schoolID, $apiKey) {
		$query = http_build_query([
			"ID" => $schoolID,
			"test" => "test",
			"key" => $apiKey,
			"data" => "teams"
		]);
		$this->result = simplexml_load_file($this->requestUrl.$query);
	}

	public function getTeam($teamID) {
		foreach ($this->result as $team) {
			if ($team->teamid == (string)$teamID)
				return $team;
		}
		return null;
	}
}
