<?php
namespace FredBradley\SOCS;

use Puc_v4_Factory;

class Plugin {
	private $version;
	private $settings;

	/**
	 * Plugin constructor.
	 *
	 * @param string $version
	 */
	public function __construct(string $version) {
		$this->version = $version;
		$this->settings = new Settings();
		add_shortcode( "socs-fixtures", array($this,"displayFixtures" ));
		add_shortcode( "socs-results", array($this, "displayResults"));
		$this->plugin_update_check('cranleigh-socs');
		add_action( 'wp_footer', array($this, 'wp_footer'));
		$this->api = new API();
}

	/**
	 *
	 */
	public function wp_footer() {
		?>

<script src="<?php echo plugins_url( 'jquery.simpleTicker/jquery.simpleTicker.js' , __FILE__ )?>"></script>
<script>
jQuery(function($){
  $.simpleTicker($("#ticker-fade"),{'effectType':'fade'});
  $.simpleTicker($("#ticker-roll"),{'effectType':'roll'});
  $.simpleTicker($("#ticker-slide"),{'effectType':'slide'});
  $.simpleTicker($("#ticker-one-item"),{'effectType':'fade',});
});
</script>
<?php	}

	/**
	 * @return string
	 */
	public function displayResults() {
		$results = new SOCSResults($this->settings->schoolID, $this->settings->apiKey);
		$output = "<div class=\"ticker-container\">";
		$output .= $results->title();
		$output .= "<div class=\"ticker\" id=\"ticker-slide\">";
		$output .= "<ul>";
		foreach ($results->tickerTape() as $result):
			$output .= "<li>".$result."</li>";
		endforeach;
		$output .= "</ul>";
		$output .= "</div></div>";



		return $output;
	}

	/**
	 * @param string $plugin_name
	 */
	public function plugin_update_check(string $plugin_name) {
		$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/cranleighschool/'.$plugin_name.'/',
			dirname(dirname(__FILE__)).'/'.$plugin_name.'.php',
			$plugin_name
		);
	}

	public function getTeams() {
		return new SOCSTeams($this->settings->schoolID, $this->settings->apiKey);
	}

	/**
	 * @param $setting
	 *
	 * @return mixed
	 */
	public function get_setting($setting) {
		return (new Settings())->$setting;
	}

	/**
	 * @param int|null $startDate
	 * @param int|null $endDate
	 *
	 * @return array $obj->get()
	 */
	public function getFixtures(int $startDate=null, int $endDate=null) {

		if ($startDate===null) {
			$startDate = time();
		}

		if ($endDate===null) {
			$endDate = $startDate + ($this->settings->intoFuture * WEEK_IN_SECONDS);
		}

		$obj = SOCS_Wrapper::getInstance();
		$obj->setID($this->settings->schoolID);
		$obj->setKey($this->settings->apiKey);
		$obj->setData('fixtures');
		$obj->setStartdate($startDate);
		$obj->setEndDate($endDate);

		return $obj->get();

	}

	/**
	 * @param string $sport
	 *
	 * @return string
	 */
	private function mapSportIcon(string $sport) {
		$sport_lr = strtolower($sport);
		$icons = [
			"badminton" => 4,
			"cricket" => 9,
			"equestrian" => 11,
			"rugby union" => 24,
			"water polo" => 74,
			"tennis" => 31,
			"swimming" => 29,
			"squash" => 28,
			"netball" => 18,
			"hockey" => 15,
			"golf" => 13,
			"football" => 26,
			"hockey indoor" => 15
		];
		if (array_key_exists($sport_lr, $icons))
			return '<img src="https://www.schoolssports.com/images/sporticons/'.$icons[$sport_lr].'.gif" />';
		else
			return $sport;
	}

	/**
	 * @param string $date
	 *
	 * @return false|string
	 */
	public function anglofyDate(string $date) {
		$dateParts = explode("/", $date);

		$day = $dateParts[0];
		$month = $dateParts[1];
		$year = $dateParts[2];

		return date("jS M", strtotime($year."-".$month."-".$day));

	}

	/**
	 * @return string
	 */
	public function displayFixtures() {
		$fixtures = $this->getFixtures();
		ob_start();
		?>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed socs-table">
			<thead>
				<th>Sport</th>
				<th>Time</th>
				<th>Team</th>
				<th>Location</th>
				<th>Opposition</th>
				<th>Match Type</th>
			</thead>
			<tbody>
				<?php
					if ($fixtures->result):
					$teams = $this->getTeams();
					foreach($fixtures->result as $fixture):
				?>

				<tr class="<?php echo strtolower((string) $fixture->result); ?>">
					<td><span data-toggle="tooltip" title="<?php echo $fixture->sport; ?>"><?php echo $this->mapSportIcon($fixture->sport); ?></span></td>
					<td><?php echo $this->anglofyDate($fixture->date); ?> (<?php echo $fixture->time;?>)</td>
					<td><a href="<?php echo $fixture->url; ?>" target="_blank"><?php echo $teams->getTeam($fixture->teamid)->teamname; ?></a></td>
					<td>
						<?php if ((string) $fixture->latlng): ?>
						<a href="https://www.google.co.uk/maps/place/<?php echo $fixture->latlng; ?>" data-src="https://www.google.co.uk/maps/place/<?php echo $fixture->latlng; ?>" target="_blank">
						<?php endif; ?>
							<?php echo $fixture->location." ";
							if ($fixture->location == "Home"  && (string) $fixture->locationdetails && $fixture->locationdetails != "Main School"):
								echo "(".$fixture->locationdetails.")";
							endif;
						if ((string) $fixture->latlng): ?>
						</a>
						<?php endif; ?>
					</td>
					<td><?php echo $fixture->opposition; ?></td>
					<td><?php echo $fixture->matchtype; ?></td>
				</tr>
				<?php endforeach; ?>

				<?php else: ?>

				<tr>
					<td colspan="5" class="text-center">
						<div class="well well-sm">
							<span class="text-warning">No fixtures could be found between <?php echo $fixtures->startDate;?> and <?php echo $fixtures->endDate; ?></span>
						</div>
					</td>
				</tr>

				<?php endif; ?>
			</tbody>
		</table>
		</div>
		<?php

			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
	}


}
