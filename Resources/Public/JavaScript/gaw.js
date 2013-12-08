var lolliGawGame = {

	initialize: function() {
		$('#lolli-gaw-player-selectPlanet-select').on('change', function() {
			$('#lolli-gaw-player-selectPlanet').submit();
		});
	}
};

/**
 * Initialize when dom is ready
 */
jQuery(document).ready(function($) {
	if ($('#lolli-gaw-game').length > 0) {
		lolliGawGame.initialize();
	}
});