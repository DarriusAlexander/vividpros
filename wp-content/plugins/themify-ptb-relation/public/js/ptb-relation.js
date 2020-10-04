(function ($) {
    'use strict';
   


    $(document).on('ptb_loaded',function(){
        if($.fn.bxSlider){
             var $bxslider = $('.ptb_relation_post_slider');
             $bxslider.each(function(){
                     if($(this).closest('.bx-wrapper').length===0 && $(this).find('li').length>0){
                        var $attr = $(this).attr('data-slider');
                        if($attr){
                           $attr = JSON.parse($attr);
                           $attr.controls = $attr.controls && parseInt($attr.controls)==1?true:false;
                           $attr.pager = $attr.pager && parseInt($attr.pager)==1?true:false;
                           $attr.autoHover = $attr.autoHover && parseInt($attr.autoHover)==1?true:false;
                           $attr.adaptiveHeight = true;
                           $attr.useCSS = false;
                           if($attr.pause==0){
                                   $attr.auto = false;
                                   $attr.pause = null;
                           }
                           else{
                                $attr.pause = $attr.pause*1000;
                                $attr.auto = true;
                           }
                           $attr.video = false;
                           $attr.mode='horizontal';
                           if($attr.slideHeight>0){
                               $(this).find('img').css('height',$attr.slideHeight);
                           }
                          
                            $attr.maxSlides = $attr.minSlides;
                            if(!$attr.slideWidth){
                                $attr.slideWidth = parseInt($(this).closest('.ptb_module').width()/$attr.minSlides);
                            }
                           
                            $(this).bxSlider($attr); 
                       }
                     }
             });
        } 
    });

}(jQuery));