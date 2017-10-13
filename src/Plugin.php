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
	public function __construct() {
		$this->version = get_plugin_data(dirname(__FILE__))['Version'];
		$this->settings = new Settings();
		$this->api = new API();
		$this->api->init();

		add_shortcode( "socs-fixtures", array($this,"displayFixtures" ));
		add_shortcode( "socs-results", array($this, "displayResults"));
		add_action( 'wp_footer', array($this, 'wp_footer'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action( 'widgets_init', array($this, 'register_widgets'));
	}
	public function enqueue_styles() {
		wp_enqueue_style('datatables', "//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css");
		wp_enqueue_style('datatables', "//cdn.datatables.net/1.10.16/css/jquery.dataTables.bootstrap.min.css");
	}
	public function register_widgets() {
		register_widget(Widgets\SportFixturesWidget::class);
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
  if ($('.socs-table').length > 0) {
	  $('.socs-table').DataTable(
		  {
			  "columns": [
				  { "orderable": false },
				  { "orderable": false },
				  { "orderable": true },
				  { "orderable": true },
				  { "orderable": true },
				  { "orderable": false },
				  { "orderable": false }
			  ]
		  }
	  );
  }

});
</script>
<?php	}


	/**
	 * @return string
	 */
	public function displayResults() {
		wp_enqueue_script( 'datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js' );
		wp_enqueue_script( 'datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.bootstrap.min.js' );
		wp_enqueue_script('socs-js', plugins_url('socs.js', __FILE__), 'jquery');

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



	public function getTeams() {
		return new SOCSTeams($this->settings->schoolID, $this->settings->apiKey);
	}

	/**
	 * @param $setting
	 *
	 * @return mixed
	 */
	public function get_setting($setting) {
		return $this->settings->$setting;
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
			"hockey indoor" => 15,
			"hockey sevens" => 15

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

	private function showTeamsheet($eventid) {
		return '<a href="javascript:void(0)" class="teamsheet-link" data-foo="'.$eventid.'">Link</a>';
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);

		$json = file_get_contents("https://socs.cranleigh.org/fixture/".$eventid, false, stream_context_create($arrContextOptions));
		$fixture = json_decode($json);

		return $fixture->sport;
	}
	/**
	 * @return string
	 */
	public function displayFixtures() {
		$fixtures = $this->getFixtures();

		ob_start();
		?>


		<!-- Modal -->
		<div class="modal fade" id="teamsheetmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Teamsheet</h4>

					</div>
					<div class="modal-body"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary">Save changes</button>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<!-- /.modal -->
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed socs-table">
			<thead>
				<th>Sport</th>
				<th>Time</th>
				<th>Team</th>
				<th>Location</th>
				<th>Opposition</th>
				<th>Match Type</th>
				<th style="display: none">Teamsheet</th>
			</thead>
			<tbody>
				<?php
					if ($fixtures->result):
					$teams = $this->getTeams();
					foreach($fixtures->result as $fixture):
				?>

				<tr class="<?php echo strtolower((string) $fixture->result); ?>">
					<td><span data-toggle="tooltip" title="<?php echo $fixture->sport; ?>"><?php echo $this->mapSportIcon($fixture->sport); ?></span></td>
					<td><?php echo $this->anglofyDate($fixture->date); ?><br /><small>(<?php echo $fixture->time;?>)</small></td>
					<td><a href="<?php echo $fixture->url; ?>" target="_blank"><?php echo $teams->getTeam($fixture->teamid)->teamname; ?></a></td>
					<td>
						<?php if ((string) $fixture->latlng): ?>
						<a href="https://www.google.co.uk/maps/place/<?php echo $fixture->latlng; ?>" data-src="https://www.google.co.uk/maps/place/<?php echo $fixture->latlng; ?>" target="_blank">
						<?php endif; ?>
							<?php echo $fixture->location;

							if ((string) $fixture->locationdetails && $fixture->locationdetails != "Main School"):
								echo "<br /><small>(".$fixture->locationdetails.")</small>";
							endif;

						if ((string) $fixture->latlng): ?>
						</a>
						<?php endif; ?>
					</td>
					<td><?php echo $fixture->opposition; ?></td>
					<td><?php echo $fixture->matchtype; ?></td>
					<td style="display: none;"><?php echo $this->showTeamsheet($fixture->eventid); ?></td>
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
