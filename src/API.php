<?php
namespace FredBradley\SOCS;
use YeEasyAdminNotices\V1\AdminNotice;

error_reporting(E_ALL);

/**
 * Class API
 *
 * @package FredBradley\SOCS
 */
class API {

	/**
	 * API constructor.
	 */
	public function __construct() {
		$this->settings = new Settings();
	}

	public function init() {
		add_action('rest_api_init', [ $this, 'apiinit' ]);
	}
	public function apiinit() {

		register_rest_route(
			'cranleigh/socs', 'fixtures', [
				"methods"  => "GET",
				"callback" => [ $this, 'getFixtures' ],
				"args"     => [
					"startdate" => [],
					"enddate"   => [],
					"sport"     => [],
					"limit" => []
				]
			]
		);

		register_rest_route(
			'cranleigh/socs', 'results', [
				"methods"  => "GET",
				"callback" => [ $this, 'getResults' ],
				"args"     => [
				]
			]
		);

		register_rest_route(
			"cranleigh/socs", "sports", [
				"methods" => "GET",
				"callback" => [ $this, 'listSports' ],
				"args" => [

				]
			]
		);

		register_rest_route(
			"cranleigh/socs", "teams", [
				"methods" => "GET",
				"callback" => [ $this, 'listTeams' ],
				"args" => [
					"teamid" => [],
					"find" => [],
					"value" => []
				]
			]
		);

	}

	public static function listTeams(\WP_REST_Request $request) {
		$teams = new SOCSTeams($this->settings->schoolID, $this->settings->apiKey);

		if (null !== $request->get_param('teamid')) {
			return $teams->getTeam($request->get_param('teamid'));

		}

		if (null !== $request->get_param('find') && null !== $request->get_param('value')) {
			return $teams->findTeams($request->get_param('find'), $request->get_param('value'));
		}

		return $teams->allTeams();

	}

	public static function listSports() {
		$output = SOCS_Wrapper::getInstance()
		                   ->setID( (new Settings())->schoolID )
		                   ->setData('teams')
		                   ->setKey((new Settings())->apiKey)
		                   ->get();

		if ($output === false) {
			AdminNotice::create()->error("Teams could not be found from the API")->show();
			return false;
		}

		$teams = $output->result;
		$listSports = [];

		foreach ($teams as $team):
			if (!in_array((string)$team->sport, $listSports)) {
				array_push($listSports, (string)$team->sport);
			}
		endforeach;

		return $listSports;
	}

	public function getResults() {

		$today  = strtotime( 'today' );
		$before = ( strtotime( '-' . $this->settings->infoFuture . ' weeks' ) );

		$obj = SOCS_Wrapper::getInstance()
		                   ->setID( $this->settings->schoolID )
		                   ->setKey( $this->settings->apiKey )
		                   ->setData( 'fixtures' )
		                   ->setEndDate( strtotime( 'today' ) )
		                   ->setStartDate( strtotime( 'today' ) - ( $this->settings->infoFuture * WEEK_IN_SECONDS ) );

		return $obj->get();
	}

	public function getFixtures(\WP_REST_Request $request=null) {

		if ( isset( $_GET[ 'startdate' ] ) ) {
			$startDate = strtotime( $_GET[ 'startdate' ] );
		} else {
			$startDate = strtotime( 'today' );
		}

		if ( isset( $_GET[ 'enddate' ] ) ) {
			$endDate = strtotime( $_GET[ 'enddate' ] );
		} else {
			$endDate = $startDate + ( $this->settings->intoFuture * WEEK_IN_SECONDS );
		}

		$obj = SOCS_Wrapper::getInstance()
		                   ->setID( $this->settings->schoolID )
		                   ->setKey( $this->settings->apiKey )
		                   ->setData( 'fixtures' )
		                   ->setStartdate( $startDate )
		                   ->setEndDate( $endDate );

		$result = $obj->get()->result;

		if (null !== $request->get_param('sport')) {

			$fixtures = [];
			$i = 1;

			foreach ($result as $fixture):

				if ((string) $fixture->sport !== $request->get_param('sport')) {

					continue;

				} else {

					if (null !== $request->get_param('limit') && $i > $request->get_param('limit')) {

						if ((string)$last_fixture->date !== (string)$fixture->date) {
							break;
						}

					}

					array_push($fixtures, $fixture);
					$last_fixture = $fixture;
					$i++;

				}

			endforeach;

			return $fixtures;

		} else {

			return $result;

		}

	}


}
