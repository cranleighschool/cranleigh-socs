<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 12/10/2017
 * Time: 15:12
 */

namespace FredBradley\SOCS\Widgets;


use FredBradley\SOCS\API;
use FredBradley\SOCS\Settings;
use FredBradley\SOCS\SOCSTeams;

class SportFixturesWidget extends \WP_Widget {

	protected $id_name;
	protected $default_name;
	protected $sportURL;
	protected $sportID;
	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		$this->id_name = $this->get_class_name();
		$this->default_name = "Cranleigh Sports Fixtures Widget";
		$this->description = "Show the upcoming fixtures limited to a chosen sport. Limited to 5 or as many on the same day. (So that it doesn't look like there's only 5 on one day where there are actually 10).";

		$widget_ops = array(
			'classname' => $this->id_name." no-pad",
			'description' => $this->description
		);
		$this->settings = new Settings();
		$this->api= new API();
		$this->sports = $this->api->listSports();

		parent::__construct($this->id_name,$this->default_name, $widget_ops);
	}

	public function get_class_name( $without_namespace = true ) {
		$class = get_called_class();
		if ( $without_namespace ) {
			$class = explode( '\\', $class );
			end( $class );
			$last  = key( $class );
			$class = $class[ $last ];
		}
		return $class;
	}
	/**
	 * Get the form in the widget.
	 *
	 * @param mixed $instance
	 */
	public function form($instance)
	{
		$current_sport = ! empty( $instance['sport'] ) ? $instance['sport'] : null;

		?>
<p>
			<label for="<?php echo esc_attr($this->get_field_id('sport')); ?>"><?php _e(esc_attr('Choose Sport:')); ?></label>
<select class="widefat" id="<?php echo esc_attr($this->get_field_id('sport')); ?>" name="<?php echo esc_attr($this->get_field_name('sport')); ?>">
	<option value="">All Sports</option>
	<?php

	foreach ($this->sports as $sport):
		if ($current_sport == $sport) {
			$selected = "selected=\"selected\"";
		} else {
			$selected = null;
		}
		echo "<option value=\"".$sport."\" ".$selected.">".$sport."</option>";
	endforeach;
	?>
</select>
</p>
	<?php }

	/**
	 * Update the data inside the form.
	 * This method will prepare data and return them.
	 *
	 * @param mixed $newInstance
	 * @param mixed $oldInstance
	 *
	 * @return mixed
	 */
	public function update($newInstance, $oldInstance)
	{
		$instance = array();
		$instance['sport'] = ( ! empty( $newInstance['sport'] ) ) ? strip_tags( $newInstance['sport'] ) : '';
		//$instance['category'] = ( ! empty( $new_instance['category'] ) ) ? strip_tags( $new_instance['category'] ) : '';

		return $instance;
	}

	/**
	 * Get the widget html.
	 *
	 * @param array $args
	 * @param mixed $instance
	 */
	public function widget($args, $instance)
	{
		$sport = ! empty( $instance['sport'] ) ? $instance['sport'] : null;

		echo $args['before_widget'];

		echo $args['before_title'];
		echo apply_filters( 'widget_title', wp_sprintf("Upcoming %s Fixtures", $sport) );
		echo $args['after_title'];

		$response = wp_remote_get( get_site_url() . "/wp-json/cranleigh/socs/fixtures?limit=10&sport=" . $sport );
		if ($response['response']['code'] !== 200) {

			$error_message = "There was a problem getting the fixtures list. Please try again later.";
			error_log($error_message);
			echo '<div class="alert alert-warning">'.$error_message.'</div>';

		} else {

			$obj = json_decode( wp_remote_retrieve_body( $response ) );
			echo '<div class="table-responsive">';
			echo '<table class="table table-striped table-condensed table-hover">';

			foreach ( $obj as $fixture ) {
				if (isset($fixture->url) && !isset($this->sportURL)) {
					$this->getSportID($fixture->url);
				}

				echo $this->getCalendarFixtureRow( $fixture );
			}
			echo '</table>';
			echo '</div>';

			if (!empty($obj)) {
				echo '<div style="padding:5px;">';
				echo do_shortcode( '[signpost block="true" url="' . $this->sportURL . '" text="All ' . $sport . ' Fixtures"]' );
				echo '</div>';
			}

		}

		echo $args['after_widget'];
	}
	private function getSportID($url) {
		error_log("Getting SID from URL: ".$url);
		$parts = explode("SID=", $url); // Sometimes the "SID=" is not found!
		error_log(print_r($parts, true));
		$this->sportID = $parts[1]; // when SID is not found in the URL then this breaks!
		$this->sportURL = "http://sportsdesk.cranleigh.org/Fixtures_Teams.asp?SID=".$this->sportID;
	}
	private function getMonthNameFromDate(string $date) {
		$date = str_replace('/', '-', $date);

		$month = date("M", strtotime($date)); //May

		return $month;
	}
	private function getDayFromDate(string $date) {
		$date = str_replace('/', '-', $date);

		$day = date("d", strtotime($date));
		return $day;
	}
	private function getCalendarFixtureRow($fixture) {
		$teams = new SOCSTeams($this->api->settings->schoolID, $this->api->settings->apiKey);

		$title = $teams->getTeam($fixture->teamid)->teamname." vs ".$fixture->opposition;
		$location = $fixture->location;
		$permalink = "http://sportsdesk.cranleigh.org/iframeFixtureDetails.asp?ID=89&FID=".$fixture->eventid."&SID=24";
		if (!empty($fixture->latlng) && is_string($fixture->latlng)) {
			$google_link = $this->createGoogleMapsLink($fixture->latlng);
			$latlng = '<i class="fa fa-fw fa-map-marker"></i><a href="'.$google_link.'" target="_blank">'.$fixture->locationdetails.'</a>';
		} else {
			$latlng = null;
		}

		$output = '<tr data-placement="left" data-toggle="tooltip" title="'.$fixture->time.'">';
		$output .= '<td class="cal-event-tag"><div class="cal-tag"><span class="cal-month">'.$this->getMonthNameFromDate($fixture->date).'</span><br /><span class="cal-date">'.$this->getDayFromDate($fixture->date).'</span></div></td>';
		$output .= '<td class="cal-event-detail"><a target="fancybox" href="#" onclick="window.open(\''.$permalink.'\', \'popup\', \'width=600,height=500,toolbar=no,location=no,menubar=no,titlebar=no,top=50%,left=50%\'); return false;">'.$title.'</a><br /><span class="cal-event-time">'.$location.$latlng.'</span></td>';
		$output .='</tr>';
		return $output;
	}
	private function createGoogleMapsLink($latlng) {
		return "https://www.google.co.uk/maps/place/".$latlng;
	}
}
