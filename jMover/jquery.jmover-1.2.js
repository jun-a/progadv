/**
 * This handy little plugin allows the user to move items between 2 list boxes.
 * 
 * @package jMover
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3.0
 * @copyright (c) 2008, Peter Halasz all rights reserved
 */
(function($) {
	
	$.fn.jmover = function(options) {
		if(options == null)
			options = {};
		
		var settings = $.extend({}, $.fn.jmover.defaults, options);
		
		switch(settings.action) {
			case 'init':
				$.fn.jmover.init(this, settings);
		}
	}
	
	/**
	 * Initialize the thing.
	 * 
	 */
	$.fn.jmover.init = function(element, options) {
		
		/**
		 * Should we create the empty skeleton for the mover and 
		 * the another script will fill it up later on
		 */
		if(options.create)
			this.createMover(element, options);
		
		/**
		 *  Set up the click bindings.
		 *  
		 *   Naming convention is that the buttons id's are made up from
		 *   the container's ID and the text 'RightSelect', 'RightAll',
		 *   'LeftSelect', 'LeftAll'
		 *   
		 *   <example>
		 *   	<span id="exampleRightSelect">
		 *   	...
		 *   	</span>
		 *   </example>
		 */
		var $moveRightSelect = element.selector + 'RightSelect';
		var $moveRightAll = element.selector + 'RightAll';
		var $moveLeftSelect = element.selector + 'LeftSelect';
		var $moveLeftAll = element.selector + 'LeftAll';
		
		/* Set the click handlers */
		$($moveRightSelect).bind('click', {to: options.targetID, from: options.sourceID}, this.moveSelected);
		$($moveRightAll).bind('click', {to: options.targetID, from: options.sourceID}, this.moveAll);
		$($moveLeftSelect).bind('click', {to: options.sourceID, from: options.targetID}, this.moveSelected);
		$($moveLeftAll).bind('click', {to: options.sourceID, from: options.targetID}, this.moveAll);
		
	};

	$.fn.jmover.createMover = function(element, options) {
		/**
		 * Arguments for the "source" select html tag.
		 */
		var $sourceArgs = 'id="' + options.sourceID +'"' +
					  ' multiple="multiple" size=' + options.size +
					  ' name="' + options.sourceID + '[]"'
		/**
		 * Arguments for the "target" select html tag.
		 */
		var $targetArgs = 'id="' + options.targetID + '"' +
					  ' multiple="multiple" size=' + options.size +
					  ' name="' + options.targetID + '[]"';
		
		/**
		 * IDs of the move buttons.
		 */
		var $moveRightSelect = element.get(0).id + 'RightSelect';
		var $moveRightAll = element.get(0).id + 'RightAll';
		var $moveLeftSelect = element.get(0).id + 'LeftSelect';
		var $moveLeftAll = element.get(0).id + 'LeftAll';
		
		/* source select field */
		element.append('<div class="leftSelect" id="' + options.fromID + '"></div>');
		var $source = $(element.selector + ' .leftSelect')
		$source.append('<label for="' + options.sourceID + '">'+ options.fromLabel +'</label>');
		$source.append('<select class="source" ' + $sourceArgs + '></select>');
		
		/* mover buttons */
		element.append('<div class="selectButtons"></div>');
		var $buttonContainer = $(element.selector + ' .selectButtons');
		$buttonContainer.append('<p>' +
		        				'<span id="' + $moveRightAll + '">' +
		        				'<a>&nbsp;&gt;&gt;&nbsp;</a>' +
		        				'</span>' +
	        					'</p>');
		
		$buttonContainer.append('<p>' +
				'<span id="' + $moveRightSelect + '">' +
				'<a>&nbsp;&gt;&nbsp;</a>' +
				'</span>' +
				'</p>');
		
		$buttonContainer.append('<p>' +
				'<span id="' + $moveLeftSelect + '">' +
				'<a>&nbsp;&lt;&nbsp;</a>' +
				'</span>' +
				'</p>');
		
		$buttonContainer.append('<p>' +
				'<span id="' + $moveLeftAll + '">' +
				'<a>&nbsp;&lt;&lt;&nbsp;</a>' +
				'</span>' +
				'</p>');
		
		/* target select field */
		element.append('<div class="rightSelect" id="' + options.toID + '"></div>');
		var $target = $(element.selector + ' .rightSelect')
		$target.append('<label for="' + options.targetID + '">'+ options.toLabel +'</label>');
		$target.append('<select class="target" ' + $targetArgs + '></select>');
	};
	
	/**
	 * Moves selected options from the DOM element specified in the 'from'
	 * argument to the DOM element specified by the 'to' argument.
	 * 
	 * @access public
	 * @param string to The ID of the select box we want to move elements to
	 * @param string from The ID of the select box we want to move elements from
	 * @return void
	 */
	$.fn.jmover.moveSelected = function(args) {
		jQuery.each($("#" + args.data.from + " option:selected"), function() {
			$("#" + args.data.to).append(this);
		});
	};
	
	/**
	 * Moves all options from the DOM element specified in the 'from'
	 * argument to the DOM element specified by the 'to' argument.
	 * 
	 * @access public
	 * @param string to The ID of the select box we want to move elements to
	 * @param string from The ID of the select box we want to move elements from
	 * @return void
	 */
	$.fn.jmover.moveAll = function(args) {
		jQuery.each($("#" + args.data.from + " option"), function() {
			$("#" + args.data.to).append(this);
		});
	}
	
	/**
	 * Plugin defaults.
	 * 
	 * @access public
	 */
	$.fn.jmover.defaults = {
			/**
			 * The ID of the source field container.
			 * Needed for auto creation
			 * 
			 * @var string
			 */
			toID: 'to',
			/**
			 * The label of the source field container.
			 * Needed for auto creation
			 * 
			 * @var string
			 */
			toLabel: 'Target',
			/**
			 * The ID of the target field container.
			 * Needed for auto creation
			 * 
			 * @var string
			 */
			fromID: 'from',
			/**
			 * The label of the target field container.
			 * Needed for auto creation
			 * 
			 * @var string
			 */
			fromLabel: 'Source',
			/**
			 * The ID of the source select field
			 * 
			 * @var string
			 */
			sourceID: 'exampleSource',
			/**
			 * The ID of the target select field
			 * 
			 * @var string
			 */
			targetID: 'exampleTarget',
			/**
			 * Size of the select fields
			 * 
			 * @var integer
			 */
			size: 15,
			/**
			 * Auto create the mover elements
			 * 1 - Yes
			 * 0 - No
			 * 
			 * @var integer
			 */
			create: 1,
			/**
			 * The action we want to do.
			 * 
			 * @var string
			 */
			action: null
	};
})(jQuery);