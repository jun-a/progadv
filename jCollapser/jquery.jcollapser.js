/**
 * Small plugin to make toggling the visibility of certain parts of a page 
 * easier.
 * 
 * @package Collapser
 * @author Peter Halasz <skinner@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3.0
 * @copyright (c) 2008, Peter Halasz all rights reserved.
 */
(function($) {
	$.fn.jcollapser = function(options) {
		
		var $this = $(this)[0];

		var settings = $.extend({}, $.fn.jcollapser.defaults, options);
		
		if(typeof(options.container) == 'undefined') {
			settings.container = "#" + $this.id;
		}
		
		$.fn.jcollapser.settings[$this.id] = settings; 
		
		$(settings.container + " > .collapse").bind("click", {}, $.fn.jcollapser.collapse);
		$(settings.container + " > .expand").bind("click", {}, $.fn.jcollapser.expand);
	};
	
	$.fn.jcollapser.collapse = function() {
		var settings = $(this).parents().get(0).id;
		settings = $(this).jcollapser.settings[settings];
		$( settings.container + ' > .collapse').css("display","none");
		$( settings.container + ' > .expand').css("display","block");
    
		$(settings.target).slideUp("slow");
		$.cookie('collapser_' + settings.target, 'collapsed', { path: '/', expires: 365 });
	}
	
	$.fn.jcollapser.expand = function() {
		var settings = $(this).parents().get(0).id;
		settings = $(this).jcollapser.settings[settings];
		$(settings.container + ' > .expand').css("display","none");
		$(settings.container + ' > .collapse').css("display","block");
		$(settings.target).slideDown("slow");
		$.cookie('collapser_' + settings.target, 'expanded', { path: '/', expires: 365 });
	}
	
	$.fn.jcollapser.settings = {};
	
	$.fn.jcollapser.defaults = {
			container: '#example',
			target: '#data'
	};
})(jQuery);