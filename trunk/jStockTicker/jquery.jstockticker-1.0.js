/**
 * Horizontal Stock Ticker for jQuery.
 * 
 * @package jStockTicker
 * @author Peter Halasz <skinner@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3.0
 * @copyright (c) 2009, Peter Halasz all rights reserved.
 */
( function($) {

	$.fn.jStockTicker = function(options) {

		if (typeof (options) == 'undefined') {
			options = {};
		}

		var settings = $.extend( {}, $.fn.jStockTicker.defaults, options);

		var $ticker = $(this);

		settings.tickerID = $ticker[0].id;

		$(this).runID = null;
		$(this).shiftBy = settings.speed;
		$(this).name = settings.tickerID;
		$(this).interval = settings.interval

		var $wrap = null;

		if ($ticker.parent().get(0).className != 'wrap') {
			$wrap = $ticker.wrap("<div class='wrap'></div>");
		}

		var $tickerContainer = null;

		if ($ticker.parent().parent().get(0).className != 'container') {
			$tickerContainer = $ticker.parent().wrap(
					"<div class='container'></div>");
		}

		$ticker.width($wrap.width() * 2);
		
		ticker = new Ticker(settings.tickerID, settings.tickerID, settings.speed,
				settings.interval);

		ticker.start();
	};

	function Ticker(name, id, shiftBy, interval) {
		this.name = name;
		this.id = id;
		this.shiftBy = shiftBy;
		this.interval = interval;
		this.runId = null;

		this.div = document.getElementById(id);

		var node = this.div.firstChild;
		var next;

		while (node) {
			next = node.nextSibling;
			if (node.nodeType == 3)
				this.div.removeChild(node);
			node = next;
		}

		// end of extra textnodes removal

		this.left = 0;
		this.shiftLeftAt = this.div.firstChild.offsetWidth;
		this.div.style.height = this.div.firstChild.offsetHeight;
		this.div.style.width = 2 * screen.availWidth;
		this.div.style.visibility = 'visible';
	}

	function startTicker() {
		this.stop();

		this.left -= this.shiftBy;

		if (this.left <= -this.shiftLeftAt) {
			this.left = 0;
			this.div.appendChild(this.div.firstChild);

			this.shiftLeftAt = this.div.firstChild.offsetWidth;
		}

		this.div.style.left = (this.left + 'px');

		this.runId = setTimeout(this.name + '.start()', this.interval);
	}

	function stopTicker() {
		if (this.runId)
			clearTimeout(this.runId);

		this.runId = null;
	}

	function changeTickerInterval(newinterval) {

		if (typeof (newinterval) == 'string')
			newinterval = parseInt('0' + newinterval, 10);

		if (typeof (newinterval) == 'number' && newinterval > 0)
			this.interval = newinterval;

		this.stop();
		this.start();
	}

	/* Prototypes for Ticker */
	Ticker.prototype.start = startTicker;
	Ticker.prototype.stop = stopTicker;
	Ticker.prototype.changeInterval = changeTickerInterval;

	$.fn.jStockTicker.defaults = {
		tickerID :null,
		url :null,
		speed :1,
		interval :20
	};
})(jQuery);