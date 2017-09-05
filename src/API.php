<?php

namespace FredBradley\SOCS;

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
		add_action( 'rest_api_init', [ $this, 'results' ] );
	}

	public function results() {

		register_rest_route(
			'cranleigh/socs', 'fixtures', [
				"methods"  => "GET",
				"callback" => [ $this, 'getFixtures' ],
				"args"     => [
					"startdate" => [],
					"enddate"   => []
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

	}

	public function getResults() {

		$today  = strtotime( 'today' );
		$before = ( strtotime( '-' . $this->settings->infoFuture . ' weeks' ) );
		echo "Today: " . date( 'r', $today );
		echo "<hr />";
		echo "Before Today: " . date( 'r', $before );


		$obj = SOCS_Wrapper::getInstance()
		                   ->setID( $this->settings->schoolID )
		                   ->setKey( $this->settings->apiKey )
		                   ->setData( 'fixtures' )
		                   ->setEndDate( strtotime( 'today' ) )
		                   ->setStartDate( strtotime( 'today' ) - ( $this->settings->infoFuture * WEEK_IN_SECONDS ) );

		return $obj->get();
	}

	public function getFixtures() {

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

		return $obj->get();

	}


}
