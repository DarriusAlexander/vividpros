<?php
/**
 * User: shahnuralam
 * Date: 10/22/17
 * Time: 2:41 AM
 */
$res = $wpdb->get_results("select * from {$wpdb->prefix}ahm_emails order by {$field} {$ord} limit $start, $limit",ARRAY_A);
$total = $wpdb->get_var("select count(*) as t from {$wpdb->prefix}ahm_emails");
?>
<form method="post" action="edit.php?post_type=wpdmpro&page=wpdm-subscribers&task=delete&lockOption=email" id="posts-filter" class="panel-body">


    <div class="clear"></div>


    <table id="subtbl" cellspacing="0" class="table table-striped">
        <thead>
        <tr>
            <th style="width: 50px" class="manage-column column-cb check-column text-center" id="cb" scope="col"><input type="checkbox"></th>
            <th style="width:50px" style="" class="manage-column column-id"  scope="col"><?php echo __( "ID" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __( "Email" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __( "Name" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-media" id="filename" scope="col"><?php echo __( "Package Name" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __( "Date" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __( "Action" , "download-manager" ); ?></th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <th style="" class="manage-column column-cb check-column text-center" id="cb" scope="col"><input type="checkbox"></th>
            <th style="width:50px" style="" class="manage-column column-id"  scope="col"><?php echo __( "ID" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __( "Email" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __( "Name" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-media" id="filename" scope="col"><?php echo __( "Package Name" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __( "Date" , "download-manager" ); ?></th>
            <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __( "Action" , "download-manager" ); ?></th>
        </tr>
        </tfoot>

        <tbody class="list:post" id="the-list">
        <?php
        $nonce = wp_create_nonce(NONCE_KEY);
        foreach($res as $row) {

            ?>
            <tr valign="top" class="author-self status-inherit" id="__emlrow_<?php echo $row['id']; ?>">

                <th class="check-column text-center" style="padding: 5px 0px !important;" scope="row"><input type="checkbox" value="<?php echo $row['id']; ?>" name="id[]"></th>
                <td scope="row">
                    <?php echo $row['id']; ?>
                </td>
                <td scope="row"><?php echo $row['email']; ?></td>
                <td scope="row"><?php $cd = unserialize($row['custom_data']); if($cd) { ?>
                    <div class="btn-group">
                        <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo isset($cd['name'])?$cd['name']:'Other Info'; ?></button>
                        <div class="dropdown-menu">
                            <?php echo "<ul class='list-group'>"; foreach($cd as $k=>$v): echo "<li class='list-group-item'>".ucfirst($k).": ".(is_array($v)?implode(", ",$v):$v)."</li>"; endforeach; echo "</ul>"; } ?>
                        </div>
                    </div>
                </td>

                <td class="media column-media">
                    <a href='edit.php?post_type=wpdmpro&page=wpdm-subscribers&pid=<?php echo $row['pid']; ?>'><?php $p =  get_post($row['pid']); if(is_object($p) && $p->post_type =='wpdmpro') echo $p->post_title; else echo "Not Found or Deleted"; ?></a>
                </td>
                <td class="author column-author"><?php echo date_i18n(get_option('date_format')." ".get_option('time_format'),$row['date']); ?></td>
                <td class="author column-author"><?php echo (int)$row['request_status']===2?"<a class='btn btn-xs btn-info __wpdm_approvedr' style='width: 80px' data-nonce='{$nonce}' data-rid='{$row['id']}' href='#'>Approve</a> <a class='btn btn-xs btn-danger __wpdm_declinedr __wpdm_declinedr_{$row['id']}' data-nonce='{$nonce}' data-rid='{$row['id']}' href='#'>Decline</a>":"<div disabled='disabled' style='width: 80px' class='btn btn-xs btn-success'>Approved</div>"; ?></td>

            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php
    $cp = $_GET['paged']?(int)$_GET['paged']:1;
    $page_links = paginate_links( array(
        'base' => add_query_arg( 'paged', '%#%' ),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => ceil($total/$limit),
        'current' => $cp
    ));


    ?>

    <div class="well">
        <nobr>
            <input type="submit" class="btn btn-secondary btn-sm action submitdelete" id="doaction" value="<?php echo __( "Delete Selected" , "download-manager" ); ?>">
            <?php if(isset($_REQUEST['q'])) { ?>
                <input type="button" class="button-secondary action" onclick="location.href='admin.php?page=file-manager'" value="<?php echo __( "Reset Search" , "download-manager" ); ?>">
            <?php } ?>
        </nobr>
        <div class="pull-right">

            <?php  if ( $page_links ) { ?>
                <div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
                        number_format_i18n( ( $_GET['paged'] - 1 ) * $limit + 1 ),
                        number_format_i18n( min( $_GET['paged'] * $limit, $total ) ),
                        number_format_i18n( $total ),
                        $page_links
                    ); echo $page_links_text; ?></div>
            <?php }  ?>

        </div><div style="clear: both"></div>
    </div>




</form>
