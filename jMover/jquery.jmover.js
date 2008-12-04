/**
 * This handy little plugin allows the user to move items between 2 list boxes.
 * 
 * @package jMover
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3.0
 * @copyright (c) 2008, Peter Halasz all rights reserved
 */
(function($) {
	/**
	 * Moves selected options from the DOM element specified in the 'from'
	 * argument to the DOM element specified by the 'to' argument.
	 * 
	 * @access public
	 * @param string to The ID of the select box we want to move elements to
	 * @param string from The ID of the select box we want to move elements from
	 * @return void
	 */
	$.jMover = function(to, from) {
		jQuery.each($("#" + from + " option:selected"), function() {
		var option = document.createElement("option");
		option.value = $(this).val();
		option.text = $(this).text();
					
		$("#"+to).append(option);
		});
				
		$("#" + from + " option:selected").remove();
	};
	
})(jQuery);