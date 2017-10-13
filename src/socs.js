/**
 * Created by fredbradley on 08/09/2017.
 */
jQuery(document).ready(function($) {
	function socsTeamSheet(eventid) {
		$.getJSON('https://socs.cranleigh.org/fixture/'+eventid, function (data) {
			console.log(data);
			$("#teamsheetmodal .modal-body").html(formatTeamsheet(data.teamsheet));
		});

		$("#teamsheetmodal").modal('show');

	}
	function formatTeamsheet(teamsheet) {
		html = '';
		$.each(teamsheet, function( index, value ) {
			html = html + "<li>" + value.name + "</li>";
		});
		return "<ul>"+html+"</ul>";
	}
	$(".teamsheet-link").click(function() {
		socsTeamSheet($(this).data('foo'));
	});
});
