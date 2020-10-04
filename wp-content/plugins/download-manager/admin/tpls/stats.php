
<div class="wrap w3eden">

    <div class="panel panel-default" id="wpdm-wrapper-panel">
        <div class="panel-heading">
            <a class="btn btn-primary btn-sm pull-right" href="edit.php?post_type=wpdmpro&page=wpdm-stats&task=export" style="font-weight: 400"><i class="sinc far fa-arrow-alt-circle-down"></i> <?php _e("Export History",'download-manager'); ?></a>
            <b><i class="fas fa-chart-line color-purple"></i> &nbsp; <?php echo __( "Download Statistics" , "download-manager" ); ?></b>

        </div>
        <ul id="tabs" class="nav nav-tabs nav-wrapper-tabs" style="padding: 60px 10px 0 10px;background: #f5f5f5">
            <li <?php if((!isset($_GET['type']))&&!isset($_GET['task'])){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats'><?php echo __( "Monthly Stats" , "download-manager" ); ?></a></li>
            <li <?php if(isset($_GET['type'])&&$_GET['type']=='history'){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats&type=history'><?php echo __( "Download History" , "download-manager" ); ?></a></li>
            <li <?php if(isset($_GET['type'])&&$_GET['type']=='pvdpu'){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats&type=pvdpu'><?php echo __( "Package vs Date" , "download-manager" ); ?></a></li>
            <li <?php if(isset($_GET['type'])&&$_GET['type']=='pvupd'){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats&type=pvupd'><?php echo __( "Package vs User" , "download-manager" ); ?></a></li>
        </ul>
        <div class="tab-content" style="padding: 15px;">
<?php 

$type = isset($_GET['type'])?WPDM_BASE_DIR."admin/tpls/stats/{$_GET['type']}.php":WPDM_BASE_DIR."admin/tpls/stats/current-month.php";

include($type);

?>
</div>
</div>