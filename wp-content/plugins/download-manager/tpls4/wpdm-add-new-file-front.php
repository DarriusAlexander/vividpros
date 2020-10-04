<?php
global $post;
if(!defined('ABSPATH')) die();

if(isset($pid))
    $post = get_post($pid);
else {
    $post = new stdClass();
    $post->ID = 0;
    $post->post_title = '';
    $post->post_content = '';
}

if(isset($hide)) $hide = explode(',',$hide);
else $hide = array();
?>
<div class="w3eden">
 <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/download-manager/assets/css/chosen.css'); ?>" />
<style>
    .cat-card ul,
    .cat-card label,
    .cat-card li{
        padding: 0;
        margin: 0;
        font-size: 9pt;
    }
    .cat-card ul{
        margin-left: 20px;
    }
    .cat-card > ul{
        padding-top: 10px;
    }
    #wpdm-pf .card{
        margin-bottom: 15px;
    }
</style>
<div class="wpdm-front">
    <?php if($post->ID > 0){ ?>
<form id="wpdm-pf" action="" method="post">
    <?php wp_nonce_field(NONCE_KEY, '__wpdmepnonce'); ?>

    <div class="row">

    <div class="col-md-8">


<input type="hidden" id="act" name="act" value="<?php echo $task =='edit-package'?'_ep_wpdm':'_ap_wpdm'; ?>" />

<input type="hidden" name="id" id="id" value="<?php echo isset($pid)?$pid:0; ?>" />
<div class="form-group">
    <div class="card " id="package-description">
        <div class="card-header"><strong><?php _e( "Title" , "download-manager" ); ?></strong></div>
        <div class="card-body">
            <input id="title" class="form-control input-lg"  placeholder="Enter title here" type="text" value="<?php echo isset($post->post_title)?$post->post_title:''; ?>" name="pack[post_title]" />
        </div>
    </div>
</div>
<div  class="form-group">
    <div class="card " id="package-description">
        <div class="card-header"><strong><?php _e( "Description" , "download-manager" ); ?></strong></div>
        <div class="card-body">
            <?php $cont = isset($post)?$post->post_content:''; wp_editor(stripslashes($cont),'post_content',array('textarea_name'=>'pack[post_content]', 'teeny' => 1, 'media_buttons' => 0)); ?>
        </div>
    </div>
</div>

        <div class="card " id="attached-files-section">
            <div class="card-header"><b><?php _e( "Attached Files" , "download-manager" ); ?></b></div>
            <div class="card-body">
                <?php
                require_once wpdm_tpl_path('metaboxes/attached-files.php');
                ?>
            </div>
        </div>

            <div class="card " <?php if(in_array('settings', $hide)) { ?>style="display: none"<?php }?> id="package-settings-section">
                <div class="card-header"><b><?php _e( "Package Settings" , "download-manager" ); ?></b></div>
                <div class="card-body">
                    <?php
                    require_once wpdm_tpl_path("metaboxes/package-settings-front.php");
                    ?>
                </div>
            </div>

        <?php

            do_action("wpdm-package-form-left");
        ?>


</div>
<div class="col-md-4">

    <div class="card " id="attach-file-section">
        <div class="card-header"><b><?php _e( "Attach Files" , "download-manager" ); ?></b></div>
        <div class="card-body">
            <?php
            require_once wpdm_tpl_path("metaboxes/attach-file.php");
            ?>
        </div>
    </div>


    <div class="card " id="categories-section" <?php if(in_array('cats', $hide)) { ?>style="display: none"<?php }?>>
        <div class="card-header"><b><?php _e( "Categories" , "download-manager" ); ?></b></div>
        <div class="card-body cat-card">
            <?php
            $term_list = wp_get_post_terms($post->ID, 'wpdmcategory', array("fields" => "all"));
            if(!function_exists('wpdm_categories_checkboxed_tree')) {
                function wpdm_categories_checkboxed_tree($parent = 0, $selected = array())
                {
                    $categories = get_terms('wpdmcategory', array('hide_empty' => 0, 'parent' => $parent));
                    $checked = "";
                    foreach ($categories as $category) {
                        if ($selected) {
                            foreach ($selected as $ptype) {
                                if ($ptype->term_id == $category->term_id) {
                                    $checked = "checked='checked'";
                                    break;
                                } else $checked = "";
                            }
                        }
                        echo '<li><label><input type="checkbox" ' . $checked . ' name="cats[]" value="' . $category->term_id . '"> ' . $category->name . ' </label>';
                        echo "<ul>";
                        wpdm_categories_checkboxed_tree($category->term_id, $selected);
                        echo "</ul>";
                        echo "</li>";
                    }
                }
            }

            echo "<ul class='ptypes'>";
            $cparent = isset($params['base_category'])?$params['base_category']:0;
            if($cparent!==0){
                $cparent = get_term_by('slug', $cparent, 'wpdmcategory');
                $cparent = $cparent->term_id;
                echo "<input type='hidden' value='{$cparent}' name='cats[]' />";
            }
            wpdm_categories_checkboxed_tree($cparent, $term_list);
            echo "</ul>";
            ?>
        </div>
    </div>

    <div class="card " id="tags-section" <?php if(in_array('tags', $hide)) { ?>style="display: none"<?php }?>>
        <div class="card-header"><b><?php _e( "Tags" , "download-manager" ); ?></b></div>
        <div class="card-body tag-card" id="tags">
            <?php
            $tags =  wp_get_post_tags($post->ID);

            foreach ($tags as $tag){ ?>
                <div style="margin: 4px" id="tag_<?php echo $tag->term_id; ?>">
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" disabled="disabled" value="<?php echo $tag->name; ?>">
                        <div class="input-group-append">
                            <span class="input-group-text color-red" style="cursor: pointer">
                                <input type="hidden" name="tags[]" value="<?php echo $tag->name; ?>" />
                                <i class="fa fa-times-circle" onclick="$('#tag_<?php echo $tag->term_id; ?>').remove();"></i>
                            </span>

                        </div>
                    </div>
                </div>
            <?php }
            ?>
        </div>
        <div class="card-footer">
            <div class="input-group">
                <input type="text" id="tagname" class="form-control" value="">
                <div class="input-group-append">
                    <button type="button" id="addtag" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="card " <?php if(in_array('images', $hide)) { ?>style="display: none"<?php }?>>
        <div class="card-header"><b><?php _e( "Main Preview Image" , "download-manager" ); ?></b></div>

            <div id="img"><?php if(has_post_thumbnail((isset($pid)?$pid:0))):
                    $thumbnail_id = get_post_thumbnail_id((isset($pid)?$pid:0));
                    $thumbnail_url = wp_get_attachment_image_src($thumbnail_id,'full', true);
                    ?>
                    <img src="<?php  echo $thumbnail_url[0]; ?>" alt="preview" /><input type="hidden" name="file[preview]" value="<?php  echo $thumbnail_url[0]; ?>" id="fpvw" />
                <?php else: ?>
                <div class="inside">
                    <a href="#" id="wpdm-featured-image">&nbsp;</a>
                    <input type="hidden" name="file[preview]" value="" id="fpvw" />
                    <div class="clear"></div>
                </div>
                <?php endif; ?>
            </div>
            <!-- <input type="file" name="preview" /> -->

        <?php if(has_post_thumbnail((isset($pid)?$pid:0))): ?>
            <div class="card-footer"  id="rmvp">
                <a href="#" class="text-danger"><?php _e( "Remove Featured Image" , "download-manager" ); ?></a>
            </div>
        <?php endif; ?>
    </div>

<div class="card " <?php if(in_array('images', $hide)) { ?>style="display: none"<?php }?>>
<div class="card-header"><b><?php _e( "Additional Preview Images" , "download-manager" ); ?></b></div>
<div class="inside">

    <?php
    wpdm_additional_preview_images($post); ?>


 <div class="clear"></div>
</div>
</div>














<div class="card card-primary " id="form-action">
    <div class="card-header">
        <b>Actions</b>
    </div>
<div class="card-body">

<label><input class="wpdm-checkbox" type="checkbox" <?php if(isset($post->post_status)) checked($post->post_status,'draft'); ?> value="draft" name="status"> <?php _e( "Save as Draft" , "download-manager" ); ?></label><br/><br/>


<button type="submit" accesskey="p" tabindex="5" id="publish" class="btn btn-success btn-block btn-lg" name="publish"><i class="far fa-hdd" id="psp"></i> &nbsp;<?php echo $task=='edit-package'?__( "Update Package" , "download-manager" ):__( "Create Package" , "download-manager" ); ?></button>

</div>
</div>

</div>
</div>
</form>
    <?php } else { ?>

    <form id="wpdm-pf" action="" method="post">
        <input type="hidden" id="act" name="act" value="_ap_wpdm" />
        <input type="hidden" name="id" id="id" value="0" />
        <?php wp_nonce_field(NONCE_KEY, '__wpdmepnonce'); ?>

        <div class="card">
        <div class="card-header"><?php _e( "Create New Package" , "download-manager" ); ?></div>
    <div class="card-body">
        <div class="form-group">
        <input id="title" class="form-control form-control-lg" required="required" placeholder="Enter title here" type="text" value="<?php echo isset($post->post_title)?$post->post_title:''; ?>" name="pack[post_title]" />
        </div>
        <input type="hidden" value="auto-draft" name="status">
        <button type="submit" accesskey="p" tabindex="5" id="publish" class="btn btn-primary" name="publish"><i class="fas fa-hdd" id="psp"></i> &nbsp;<?php echo __( "Continue..." , "download-manager" ); ?></button>

    </div>
    </div>
    </form>

    <?php } ?>
</div>


<script type="text/javascript" src="<?php echo plugins_url('/download-manager/assets/js/chosen.jquery.min.js'); ?>"></script>
      <script type="text/javascript">

          var ps = "", pss = "", allps = "";

      jQuery(function($) {

          $('.w3eden select').chosen();
          $('span.infoicon').css({color:'transparent',width:'16px',height:'16px',cursor:'pointer'}).tooltip({placement:'right',html:true});
          $('span.infoicon').tooltip({placement:'right'});
          $('.nopro').click(function(){
            if(this.checked) $('.wpdmlock').removeAttr('checked');
        });

        $('#addtag').on('click', function () {
            var tid = Math.random().toString(36).substr(2, 16);
            var tag = $('#tagname').val();
            $('#tags').append("<div style=\"margin: 4px\" id=\"tag_"+tid+"\">\n" +
                "                    <div class=\"input-group\">\n" +
                "                        <input type=\"text\" class=\"form-control input-sm\" disabled=\"disabled\" value=\""+tag+"\">\n" +
                "                        <div class=\"input-group-append\">\n" +
                "                            <span class=\"input-group-text color-red\" style=\"cursor: pointer\">\n" +
                "                                <input type=\"hidden\" name=\"tags[]\" value=\""+tag+"\" />\n" +
                "                                <i class=\"fa fa-times-circle\" onclick=\"$('#tag_"+tid+"').remove();\"></i>\n" +
                "                            </span>\n" +
                "\n" +
                "                        </div>\n" +
                "                    </div>\n" +
                "                </div>");
            $('#tagname').val('');
        });

          $('.wpdmlock').click(function(){

            if(this.checked) {
                $('#'+$(this).attr('rel')).slideDown();
                $('.nopro').removeAttr('checked');
            } else {
                $('#'+$(this).attr('rel')).slideUp();
            }
        });

       // jQuery( "#pdate" ).datepicker({dateFormat:'yy-mm-dd'});
       // jQuery( "#udate" ).datepicker({dateFormat:'yy-mm-dd'});

        jQuery('#wpdm-pf').submit(function(){
             try {
                var editor = tinymce.get('post_content');
                editor.save();
             }catch(e){}
             if(jQuery('#title').val() === '') return false;
             jQuery('#psp').removeClass('fa-hdd').addClass('fa-sun fa-spin');
             jQuery('#publish').attr('disabled','disabled');
             jQuery('#wpdm-pf').ajaxSubmit({
                 //dataType: 'json',
                 beforeSubmit: function() { jQuery('#sving').fadeIn(); },
                 success: function(res) {  jQuery('#sving').fadeOut(); jQuery('#nxt').slideDown();
                     jQuery(".card-file.card-danger").remove();
                     if(res.result=='_ap_wpdm') {
                         <?php
                         $edit_url = $burl.$sap.'adb_page=edit-package/%d/';
                         if(isset($params['flaturl']) && $params['flaturl'] == 1)
                             $edit_url = $burl . '/edit-package/%d/'; ?>
                         var edit_url = '<?php echo $edit_url; ?>';
                         edit_url = edit_url.replace('%d', res.id);
                         location.href = edit_url;
                         jQuery('#wpdm-pf').prepend('<div class="alert alert-success" style="width:300px;" data-title="<?php _e( "Package Created Successfully!" , "download-manager" ); ?>" data-dismiss="alert"><?php _e( "Opening Edit Window ..." , "download-manager" ); ?></div>');
                     }
                     else {
                         jQuery('#psp').removeClass('fa-sun fa-spin').addClass('fa-hdd');
                         jQuery('#publish').removeAttr('disabled');
                         jQuery('#wpdm-pf').prepend('<div class="alert alert-success" data-title="<?php _e( "DONE!" , "download-manager" ); ?>" data-dismiss="alert"><?php _e( "Package Updated Successfully" , "download-manager" ); ?></div>');
                     }
                 }


             });
             return false;
        });




          allps = jQuery('#pps_z').val();
          if(allps == undefined) allps = '';
          jQuery('#ps').val(allps.replace(/\]\[/g,"\n").replace(/[\]|\[]+/g,''));
          shuffle = function(){
              var sl = 'abcdefghijklmnopqrstuvwxyz';
              var cl = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
              var nm = '0123456789';
              var sc = '~!@#$%^&*()_';
              ps = "";
              pss = "";
              if(jQuery('#ls').attr('checked')=='checked') ps = sl;
              if(jQuery('#lc').attr('checked')=='checked') ps += cl;
              if(jQuery('#nm').attr('checked')=='checked') ps += nm;
              if(jQuery('#sc').attr('checked')=='checked') ps +=sc;
              var i=0;
              while ( i <= ps.length ) {
                  $max = ps.length-1;
                  $num = Math.floor(Math.random()*$max);
                  $temp = ps.substr($num, 1);
                  pss += $temp;
                  i++;
              }

              jQuery('#ps').val(pss);


          };
          jQuery('#gps').click(shuffle);

          jQuery('body').on('click', '#gpsc', function(){
              var allps = "";
              shuffle();
              for(k=0;k<jQuery('#pcnt').val();k++){
                  allps += "["+randomPassword(pss,jQuery('#ncp').val())+"]";

              }
              vallps = allps.replace(/\]\[/g,"\n").replace(/[\]|\[]+/g,'');
              jQuery('#ps').val(vallps);

          });

          jQuery('body').on('click', '#pins', function(){
              var aps;
              aps = jQuery('#ps').val();
              aps = aps.replace(/\n/g, "][");
              allps = "["+aps+"]";
              jQuery(jQuery(this).data('target')).val(allps);
              tb_remove();
          });






      });

      jQuery('#upload-main-preview').click(function() {
            tb_show('', '<?php echo admin_url('media-upload.php?type=image&TB_iframe=1&width=640&height=551'); ?>');
            window.send_to_editor = function(html) {
              var imgurl = jQuery('img',html).attr('src');
              jQuery('#img').html("<img src='"+imgurl+"' style='max-width:100%'/><input type='hidden' name='file[preview]' value='"+imgurl+"' >");
              tb_remove();
              };
            return false;
        });


      function randomPassword(chars, size) {

          //var size = 10;
          if(parseInt(size)==Number.NaN || size == "") size = 8;
          var i = 1;
          var ret = "";
          while ( i <= size ) {
              $max = chars.length-1;
              $num = Math.floor(Math.random()*$max);
              $temp = chars.substr($num, 1);
              ret += $temp;
              i++;
          }
          return ret;
      }



      </script>

</div>