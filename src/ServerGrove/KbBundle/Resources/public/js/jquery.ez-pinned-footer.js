/******************************************************
	* jQuery plug-in
	* Easy Pinned Footer
	* Developed by J.P. Given (http://johnpatrickgiven.com)
	* Useage: anyone so long as credit is left alone
******************************************************/
(function($) {
	// plugin definition
	$.fn.pinFooter = function(options) {		
		// Get the height of the footer and window + window width
		var wH = getWindowHeight();
		wW = getWindowWidth();
		var fH = $("#navbar-footer").outerHeight(true);
		var bH = $("body").outerHeight(true);
		var mB = parseInt($("body").css("margin-bottom"));
		
		if (options == 'relative') {


			if (bH > getWindowHeight()) {
				$("#navbar-footer").css("position","absolute");
				$("#navbar-footer").css("width","100%");
				$("#navbar-footer").css("top",bH + fH + "px");
				$("body").css("overflow-x","hidden");
                $("#navbar-footer").css("z-index","100");
                $('.left-nav').css('height', bH-113 +'px');
                $('.docs .left-nav').css('height', bH-8 +'px'); //sgdocs section only

			} else {
				$("#navbar-footer").css("position","fixed");
                $("#navbar-footer").css("z-index","100");
				$("#navbar-footer").css("width",wW + "px");
				$("#navbar-footer").css("top",wH - fH + "px");

                //left nav adjustment
                var leftnavHeight = wH-155;
               $('.left-nav').css('height', leftnavHeight+'px');
                $('.docs .left-nav').css('height', leftnavHeight+33 +'px'); //sgdocs section only
			}
		} else { // Pinned option
			// Set CSS attributes for positioning footer
			$("#navbar-footer").css("position","fixed");
			$("#navbar-footer").css("width",wW + "px");
			$("#navbar-footer").css("top",wH - fH + "px");
			$("body").css("height",(bH + mB) + "px");
		}
	};
	
	// private function for debugging
	function debug($obj) {
		if (window.console && window.console.log) {
			window.console.log('Window Width: ' + $(window).width());
			window.console.log('Window Height: ' + $(window).height());
		}
	};
	
	// Dependable function to get Window Height
	function getWindowHeight() {
		var windowHeight = 0;
		if (typeof(window.innerHeight) == 'number') {
			windowHeight = window.innerHeight;
		}
		else {
			if (document.documentElement && document.documentElement.clientHeight) {
				windowHeight = document.documentElement.clientHeight;
			}
			else {
				if (document.body && document.body.clientHeight) {
					windowHeight = document.body.clientHeight;
				}
			}
		}
		return windowHeight;
	};
	
	// Dependable function to get Window Width
	function getWindowWidth() {
		var windowWidth = 0;
		if (typeof(window.innerWidth) == 'number') {
			windowWidth = window.innerWidth;
		}
		else {
			if (document.documentElement && document.documentElement.clientWidth) {
				windowWidth = document.documentElement.clientWidth;
			}
			else {
				if (document.body && document.body.clientWidth) {
					windowWidth = document.body.clientWidth;
				}
			}
		}
		return windowWidth;
	};
})(jQuery);