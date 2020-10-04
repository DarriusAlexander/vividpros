(function ($) {
    'use strict';

    var AjaxLoop = function ($data, $i) {
        var $length = $data.length - 1;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {'action': 'ptb_search_set_values', 'data': $data[$i]},
            success: function (resp) {
                var $container = $($('form.ptb-search-form')[$i]);
                for (var $j in resp) {
                    if (resp[$j].min) {
                        var $min = Math.floor(resp[$j].min),
                                $max = Math.floor(resp[$j].max),
                                $val_min = $('#' + $j + '_min'),
                                $val_max = $('#' + $j + '_max');
                        if (!$val_min.val()) {
                            $val_min.val($min);
                        }
                        if (!$val_max.val()) {
                            $val_max.val($max);
                        }
                        $val_min.attr('min', $min);
                        $val_max.prop('max', $max);
                    }
                    else {
                        for (var $k in resp[$j]) {
                            $('#' + $j + '_' + resp[$j][$k]).prop('disabled', true);
                            $('#' + $j + '_' + resp[$j][$k]).closest('div').addClass('ptb-search-disabled');
                        }
                    }
                }
                InitSlider($container);
                if ($length > $i) {
                    ++$i;
                    AjaxLoop($data, $i);
                }
            }
        });
    };

    var InitSelect = function () {
        if ($.fn.chosen) {
            $('form.ptb-search-form').find('select').chosen({
                disable_search_threshold: 7,
                search_contains: true,
                width: "100%",
            });
        }
    };

    var InitSlider = function ($container) {
        if ($.fn.slider) {
            $container.find('.ptb-search-slider').each(function () {
                var $wrap = $(this).closest('.ptb_search_wrap_number'),
                        $max = $wrap.find('.ptb_search_number_max'),
                        $min = $wrap.find('.ptb_search_number_min'),
                        $min_val = parseInt($min.prop('min')),
                        $max_val = parseInt($max.prop('max')),
                        $v1 = parseInt($min.val()),
                        $v2 = parseInt($max.val());
                if($min_val && $max_val){
                    $(this).slider({
                        range: true,
                        min: $min_val,
                        step: 1,
                        max: $max_val,
                        values: [$v1, $v2],
                        slide: function (event, ui) {
                            $min.val(ui.values[ 0 ]);
                            $max.val(ui.values[ 1 ]);
                            var $slider = $(this).find('.ptb-search-slider-tooltip-inner');
                            $slider.first().html(ui.values[0]);
                            $slider.last().html(ui.values[1]);
                        },
                        create: function (event, ui) {
                            var tooltip = '<span class="ptb-search-slider-tooltip"><span class="ptb-search-slider-tooltip-inner">' + $v1 + '</span><span class="ptb-search-slider-tooltip-arrow"></span></span>',
                                    $slider = $(this).children('.ui-slider-handle');
                            $slider.first().html(tooltip);
                            $slider.last().html(tooltip.replace($v1, $v2));
                        }
                    });
                }
            });
        }
    };

    var InitAutoComplete = function () {

        $('.ptb-search-autocomplete').each(function () {
            var $this = $(this),
                    $post_type = $this.data('post_type'),
                    cache = [];
            cache[$post_type] = [];
            $this.autocomplete({
                minLength: 2,
                source: function (request, response) {
                    var term = $.trim(request.term);
                    if (term.length < 2) {
                        return;
                    }
                    term = term.toLowerCase();
                    if (term in cache[$post_type]) {
                        response(cache[$post_type][ term ]);
                        return;
                    }
                    request.action = 'ptb_search_autocomplete';
                    request.key = $this.data('key');
                    $.getJSON(ajaxurl, request, function (data, status, xhr) {
                        cache[$post_type][ term ] = data;
                        response(data);
                    });
                },
                select: function (event, ui) {
                    $this.val(ui.item.value);

                    $this.next('input').val(ui.item.id)
                    return false;
                }
            })
                    .focus(function () {
                        $(this).autocomplete("search");
                    })
                    .autocomplete("widget")
                    .addClass("ptb-search-autocomplete-dropdown");
        });

    };

    var InitSubmit = function () {
        $('body').delegate('.ptb-search-form:not(.ptb-search-form-submit)', 'submit', function (e) {
            e.preventDefault();
            var $form = $(this),
                    $container = $('.ptb-search-container').first(),
                    $data = $form.serialize();
                    if($container.length===0){
                            $container = $('.post').first();
                    }
            $form.find('input[name="p"]').val('');
            $.ajax({
                url: $form.attr('action'),
                data: $data,
                type: 'POST',
                beforeSend: function () {
                    $form.addClass('ptb-search-submit');
                    $container.addClass('ptb-search-wait');
                },
                complete: function () {
                    $form.removeClass('ptb-search-submit');
                    $container.removeClass('ptb-search-wait');
                },
                success: function (resp) {
                    if (resp) {
                        $container.html(resp);
                        ToScroll($container);
                        history.replaceState({}, null, '?' + $data);
                        if( typeof window.wp.mediaelement != 'undefined' ) {
                            window.wp.mediaelement.initialize();
                        }
                        $.event.trigger({type: "ptb_loaded"});
                    }
                }
            });
        });
    };

    function find_page_number(element) {
        var $page = parseInt(element.text());
        if (!$page) {
            $page = parseInt(element.closest('.ptb_pagenav').find('.current').text());
            if (element.hasClass('next')) {
                ++$page;
            }
            else {
                --$page;
            }
        }
        return $page;
    }
    var InitPagination = function () {
        $('body').delegate('.ptb-search-container .ptb_pagenav a', 'click', function (e) {
            var $slug = $(this).closest('.ptb-search-container').data('slug'),
                    $form = $('.ptb-search-' + $slug);
            if ($form.length > 0) {
                e.preventDefault();
                $form.find('input[name="ptb_page"]').val(find_page_number($(this)));
                $form.submit();
            }
        });
    };
    var ToScroll = function ($container) {
		if($container.length>0){
			$('html,body').animate({
				scrollTop: $container.offset().top - $('#wpadminbar').outerHeight(true) - 10
			}, 1000);
		}
    };
    
    var InitDates = function(){
        if( $.timepicker){
            var $dates = $('.ptb_search_field_date input');
            var $defaultargs ={
                       showOn: 'focus',
                       showButtonPanel: true,  
                       showHour:1,
                       showMinute:1,
                       timeOnly:false,
                       showTimepicker:false,
                       buttonText: false,
                       dateFormat: 'yy-mm-dd',
                       timeFormat: 'hh:mm tt',
                       stepMinute: 5,
                       separator: '@',
                       minInterval:0,
                       beforeShow: function() {
                           $('#ui-datepicker-div').addClass( 'ptb_search_datepicker' );
                       },
                       onClose: function(dateText, inst) {
                           
                       }
               }; 
               $dates.each(function(){
                   var $key = $(this).data('id');
                   if($key){
                       var $endDateTextBox = $('#'+$key+'_end');
                       if($(this).data('time')){
                           $defaultargs.showTimepicker = 1;
                       }
                       $.timepicker.datetimeRange(
                               $(this),
                               $endDateTextBox,
                               $defaultargs
                       ); 
                   }

               });
           }
    };

    $(document).ready(function () {
        InitSelect();
        InitAutoComplete();
        var $forms = [];
        $('form.ptb-search-form').each(function ($i) {
            $forms[$i] = $(this).find('.ptb_search_keys').val();
        });
        if(('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)){
            PTB.LoadAsync(ptb_search.url + 'js/jquery.ui.touch-punch.min.js', function(){
                InitDates();
                AjaxLoop($forms, 0);
            }, null, ptb_search.ver);
        }
        else{
            AjaxLoop($forms, 0);
            InitDates();
        }
        InitSubmit();
        InitPagination();
    });


}(jQuery));