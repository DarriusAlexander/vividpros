/** CountUp script
 * @link http://inorganik.github.io/countUp.js/
 * @version 1.3.1
 */
function countUp(a,b,c,d,e,f){for(var g=0,h=["webkit","moz","ms","o"],i=0;i<h.length&&!window.requestAnimationFrame;++i)window.requestAnimationFrame=window[h[i]+"RequestAnimationFrame"],window.cancelAnimationFrame=window[h[i]+"CancelAnimationFrame"]||window[h[i]+"CancelRequestAnimationFrame"];window.requestAnimationFrame||(window.requestAnimationFrame=function(a){var c=(new Date).getTime(),d=Math.max(0,16-(c-g)),e=window.setTimeout(function(){a(c+d)},d);return g=c+d,e}),window.cancelAnimationFrame||(window.cancelAnimationFrame=function(a){clearTimeout(a)}),this.options=f||{useEasing:!0,useGrouping:!0,separator:",",decimal:"."},""==this.options.separator&&(this.options.useGrouping=!1),null==this.options.prefix&&(this.options.prefix=""),null==this.options.suffix&&(this.options.suffix="");var j=this;this.d="string"==typeof a?document.getElementById(a):a,this.startVal=Number(b),this.endVal=Number(c),this.countDown=this.startVal>this.endVal?!0:!1,this.startTime=null,this.timestamp=null,this.remaining=null,this.frameVal=this.startVal,this.rAF=null,this.decimals=Math.max(0,d||0),this.dec=Math.pow(10,this.decimals),this.duration=1e3*e||2e3,this.version=function(){return"1.3.1"},this.printValue=function(a){var b=isNaN(a)?"--":j.formatNumber(a);"INPUT"==j.d.tagName?this.d.value=b:this.d.innerHTML=b},this.easeOutExpo=function(a,b,c,d){return 1024*c*(-Math.pow(2,-10*a/d)+1)/1023+b},this.count=function(a){null===j.startTime&&(j.startTime=a),j.timestamp=a;var b=a-j.startTime;if(j.remaining=j.duration-b,j.options.useEasing)if(j.countDown){var c=j.easeOutExpo(b,0,j.startVal-j.endVal,j.duration);j.frameVal=j.startVal-c}else j.frameVal=j.easeOutExpo(b,j.startVal,j.endVal-j.startVal,j.duration);else if(j.countDown){var c=(j.startVal-j.endVal)*(b/j.duration);j.frameVal=j.startVal-c}else j.frameVal=j.startVal+(j.endVal-j.startVal)*(b/j.duration);j.frameVal=j.countDown?j.frameVal<j.endVal?j.endVal:j.frameVal:j.frameVal>j.endVal?j.endVal:j.frameVal,j.frameVal=Math.round(j.frameVal*j.dec)/j.dec,j.printValue(j.frameVal),b<j.duration?j.rAF=requestAnimationFrame(j.count):null!=j.callback&&j.callback()},this.start=function(a){return j.callback=a,isNaN(j.endVal)||isNaN(j.startVal)?(console.log("countUp error: startVal or endVal is not a number"),j.printValue()):j.rAF=requestAnimationFrame(j.count),!1},this.stop=function(){cancelAnimationFrame(j.rAF)},this.reset=function(){j.startTime=null,j.startVal=b,cancelAnimationFrame(j.rAF),j.printValue(j.startVal)},this.resume=function(){j.stop(),j.startTime=null,j.duration=j.remaining,j.startVal=j.frameVal,requestAnimationFrame(j.count)},this.formatNumber=function(a){a=a.toFixed(j.decimals),a+="";var b,c,d,e;if(b=a.split("."),c=b[0],d=b.length>1?j.options.decimal+b[1]:"",e=/(\d+)(\d{3})/,j.options.useGrouping)for(;e.test(c);)c=c.replace(e,"$1"+j.options.separator+"$2");return j.options.prefix+c+d+j.options.suffix},j.printValue(j.startVal)}

(function ($) {
	function builder_counter() {
		$('.module.module-counter').each(function(){
		var $this = $(this);
		$this.waypoint(function(){
			var $countup = $this.find('.bc-timer'),
				id = $countup.attr('id'),
				from = $countup.data('from'),
				to = $countup.data('to'),
				decimals = $countup.data('decimals'),
				prefix = $countup.data('prefix'),
				suffix = $countup.data('suffix');

			new countUp(id, from, to, decimals, 4, { useEasing : true, useGrouping : true, separator : $countup.data( 'grouping' ), decimal : '.', prefix : prefix, suffix: suffix }).start();
			var $chart = $this.find('.counter-chart'),
			barColor = $chart.data('color'),
			percent = $chart.data('percent');
			$chart.easyPieChart({
			'percent' : percent,
				'barColor' : barColor,
				'trackColor' : $chart.data('trackcolor'),
				'scaleColor' : $chart.data('scalecolor'),
				'scaleLength' : $chart.data('scalelength'),
				'lineCap' : $chart.data('linecap'),
				'rotate' : $chart.data('rotate'),
				'size' : $chart.data('size'),
				'lineWidth' : $chart.data('linewidth'),
				'animate' : $chart.data('animate')
			});
		}, {
			offset: '100%',
				triggerOnce: true
			});
		});
	}

	function builder_counter_init() {
		Themify.LoadAsync(themify_vars.url+'/js/waypoints.min.js', function(){
			Themify.LoadAsync(themify_vars.url+'/js/jquery.easy-pie-chart.js', builder_counter, null, null, function(){
				return ('undefined' !== typeof $.fn.easyPieChart);
			});
		}, null, null, function(){
			return ('undefined' !== typeof $.fn.waypoint);
		} );
	}

	$(document).ready(builder_counter_init);
	$( 'body' ).on( 'builder_load_module_partial builder_toggle_frontend', builder_counter_init );
}(jQuery));