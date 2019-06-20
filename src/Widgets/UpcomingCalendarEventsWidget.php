<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 19/07/2018
 * Time: 11:33
 */

namespace FredBradley\SOCS\Widgets;

use FredBradley\SOCSICSParser\CalendarEvents;
use ICal\Event;

class UpcomingCalendarEventsWidget extends \WP_Widget {

	protected $className   = 'Cranleigh (SOCS) Upcoming Events';
	protected $classId     = 'cranleigh-socs-events-widget';
	protected $description = 'Creates a widget that pulls through calendar events from SOCS Calendar.';
	protected $rangeStart;
	protected $rangeEnd;
	protected $min_num_events_shown = 10;
	protected $cssClasses           = [
		'events',
		'no-pad',
	];

	public function __construct() {

		$widget_ops = [
			'classname'   => $this->cssClassesToString(),
			'description' => $this->description,
		];
		parent::__construct( $this->classId, $this->className, $widget_ops );
	}

	private function cssClassesToString() {

		return implode( ' ', $this->cssClasses );
	}

	public function widget( $args, $instance ) {

		$calendar = new CalendarEvents(
			$instance['socs_uri'],
			[
				'cacheName'    => $args['widget_id'],
				'minNumEvents' => 10,
			]
		);
		$this->setMinNumEventsShown( $calendar->minNumEvents );

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters(
				'widget_title',
				$instance['title']
			) . $args['after_title'];
		}

		if ( is_wp_error( $calendar ) ) {

			echo '<div class="alert alert-danger"><p><strong>Error: </strong>' . $calendar->get_error_message() . '</p></div>';

		} else {
			echo '<div class="table-responsive">';
			echo '<table class="table table-striped table-condensed table-hover">';
			$i = 0;

			foreach ( $calendar->events as $key => $event ) :

				$i++;
				if ( $i > $this->min_num_events_shown ) {
					$lastRecord = ( $key - 1 );
					$lastEvent  = $calendar->events[ $lastRecord ];
					if ( $this->iCalDateHelper( $event->dtstart )->format( 'Y-m-d' ) !== $this->iCalDateHelper( $lastEvent->dtstart )->format( 'Y-m-d' ) ) {
						break;
					}
				}

				$event_start_date = $this->iCalDateHelper( $event->dtstart );
				$event_end_date   = $this->iCalDateHelper( $event->dtend );

				$event_meta = $this->setEventMeta( $event );
				?>
				<tr>
					<td class="cal-event-tag">
						<div class="cal-tag">
							<span class="cal-month"><?php echo $event_start_date->format( 'M' ); ?></span><br /><span class="cal-date"><?php echo $event_start_date->format( 'd' ); ?></span>
						</div>
					</td>
					<td class="cal-event-detail">
						<a target="socs_calendar" href="<?php echo $this->getSocsHttpUri( $instance['socs_uri'] ); ?>"><?php echo $event->summary; ?></a>
						<br />
						<span class="cal-event-time"><?php echo $event->timeLabel; ?></span>
						<span class="cal-meta"><?php echo $event_meta; ?></span>
					</td>
				</tr>
				<?php

			endforeach;

			echo '</table>';
			echo '</div>';

		}

		echo $args['after_widget'];
	}

	private function setMinNumEventsShown( int $number ) {

		$this->min_num_events_shown = $number;
	}

	private function iCalDateHelper( string $iCalDate ) {

		$iCalDate = str_replace( 'T', '', $iCalDate );
		$iCalDate = str_replace( 'Z', '', $iCalDate );

		return new \DateTime( $iCalDate );
	}


	private function setEventMeta( Event $event ) {

		$meta = false;

		if ( $event->location !== null ) {
			$meta = '<i class="fa fa-fw fa-map-marker"></i>' . $this->mapLocationText( $event->location );
		}

		return $meta;
	}

	private function mapLocationText( string $input ) {

		switch ( $input ) {
			case 'H':
				$output = 'Home';
				break;
			case 'A':
				$output = 'Away';
				break;
			case 'N':
				$output = 'Netural';
				break;
			default:
				$output = $input;
				break;
		}

		return $output;
	}

	private function getSocsHttpUri( string $uri ) {

		$uri = parse_url( $uri );

		return $uri['scheme'] . '://' . $uri['host'];
	}

	public function update( $new_instance, $old_instance ) {

		$instance                 = [];
		$instance['title']        = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['socs_uri']     = ( ! empty( $new_instance['socs_uri'] ) ) ? strip_tags( $new_instance['socs_uri'] ) : '';
		$instance['minNumEvents'] = ( ! empty( $new_instance['minNumEvents'] ) ) ? strip_tags( $new_instance['minNumEvents'] ) : '';

		return $instance;
	}

	public function form( $instance ) {

		$title        = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Upcoming Events', 'cranleigh-2016' );
		$socs_uri     = ! empty( $instance['socs_uri'] ) ? $instance['socs_uri'] : '';
		$minNumEvents = ! empty( $instance['minNumEvents'] ) ? $instance['minNumEvents'] : '5';

		?>
		<p>First, visit the front end of your SOCS Calendar, and click on &quot;Calendar Sync&quot;. Get the link of the ICS calendar and put that in the field below.</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'socs_uri' ) ); ?>"><?php _e( esc_attr( 'SOCS ics URI:' ) ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'socs_uri' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'socs_uri' ) ); ?>" type="url" value="<?php echo esc_attr( $socs_uri ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'minNumEvents' ) ); ?>"><?php _e( esc_attr( 'Minimum Number Events Shown:' ) ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'minNumEvents' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'minNumEvents' ) ); ?>" type="number" value="<?php echo esc_attr( $minNumEvents ); ?>">
			<span><small><em>Eg. If you want to show 5 events, but it turns out that the 6th even is on the same day as the 5th, then it will show more than 5 - up until the next event is on a different day!</em></small></span>
		</p>



		<?php
	}
}
