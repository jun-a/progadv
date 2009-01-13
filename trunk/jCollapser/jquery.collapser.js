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
	/**
	 * The Plugin itself.
	 * 
	 * @package collapser
	 * @author Peter Halasz <skinner@gmail.com>
	 */
	$.collapser = {
		/**
		 * Collapse a set of elements and set a cookie so we can remember 
		 * the state.
		 * 
		 * <param>
		 * {data: {
		 * 		id: The ID of the element where our collapse/expand images are.
		 * 		targer: The element we want to collapse/expand.
		 * 		}
		 * }
		 * </param>
		 * 
		 * @access public
		 * @param array p An array with the necessary data
		 * @return void
		 */
		collapse : function(p) {
			var data = p.data;
			$('#'+data.id+' > .collapse').css("display","none");
			$('#'+data.id+' > .expand').css("display","block");
        
			$(data.target).slideUp("slow");
			$.cookie('collapser_' + data.target, 'collapsed', { path: '/', expires: 365 });
		},
	
		/**
		 * Expand a set of elements and set a cookie so we can remember 
		 * the state.
		 * 
		 * <param>
		 * {data: {
		 * 		id: The ID of the element where our collapse/expand images are.
		 * 		targer: The element we want to collapse/expand.
		 * 		}
		 * }
		 * </param>
		 * 
		 * @access public
		 * @param array p An array with the necessary data
		 * @return void
		 */
		expand : function(p) {
			var data = p.data;
			$('#'+data.id+' > .expand').css("display","none");
			$('#'+data.id+' > .collapse').css("display","block");
			$(data.target).slideDown("slow");
			$.cookie('collapser_' + data.target, 'expanded', { path: '/', expires: 365 });
		},
		
		/**
		 * Initialize the target element based on the information stored in the
		 * cookie.
		 * 
		 * <param>
		 * {
		 * 	target: The element we want to initialize.
		 * }
		 * </param>
		 * 
		 * @access public
		 * @param array p;
		 * @return void
		 */
		init : function(p) {
			var $data = p;
			
			/* State from the cookie */
			var $state = $.cookie('collapser_' + $data.target);
			
			/* The parent of the target element */
			var $parent = $($data.target).parents().get(0);
			
			if($state ==  'collapsed') {
				$('#' + $parent.id + ' > .collapse').css("display","none");
				$('#' + $parent.id + ' > .expand').css("display","block");        
				$($data.target).hide();
			}
		}
	};
})(jQuery);