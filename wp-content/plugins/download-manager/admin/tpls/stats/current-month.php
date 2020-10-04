
<script language="JavaScript">
<!--
  var mmyy = "<?php echo date(" F Y"); ?>";
//-->
</script>


<?php
global $wpdb;

$my  = $wpdb->get_var("select min(year) as my from {$wpdb->prefix}ahm_download_stats");

$pid = isset($_GET['pid']) && $_GET['pid']!=''?intval($_GET['pid']):null;
$m = isset($_GET['m'])?intval($_GET['m']):date('m');
$y = isset($_GET['y'])?intval($_GET['y']):date('Y');
$pidq = $pid>0 ? ' and pid='.$pid:'';
if(isset($_GET['pid'])) $post = get_post($_GET['pid']);
?>
<br>
<?php

$data = $wpdb->get_results("select day,count(day) as downloads from {$wpdb->prefix}ahm_download_stats where year='".$y."' and month='".$m."' $pidq group by day");

$d = array();
for($i=1; $i<=31; $i++){
    $d[$i] = array('day' => $i, 'value' => 0);
}
foreach($data as $dd){
    $d[$dd->day] = array('day' => $dd->day, 'value' => $dd->downloads);
}
$d = array_values($d);
if(count($d)==0) echo "<div class='alert alert-warning'>No Download Yet in This Month</div>";
?>
<?php if(isset($post)) echo "<b>".$post->post_title."</b>"; ?>
<div id="myfirstchart" style="height: 500px;"></div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Date', 'Download Count'],
            <?php foreach ($d as $_d){ ?>
            ['<?php echo $_d['day']; ?>',  <?php echo $_d['value']; ?>],
            <?php } ?>

        ]);

        var options = {

            curveType: 'function',
            legend: { position: 'bottom' },
            backgroundColor: { fill: '#f1f1f1' }
        };

        var chart = new google.visualization.AreaChart(document.getElementById('myfirstchart'));

        chart.draw(data, options);
    }
</script>


</div>
<div class="panel-footer">
    <form method="get" action="edit.php">
        <input type="hidden" name="post_type" value="wpdmpro">
        <input type="hidden" name="page" value="wpdm-stats">
        Package ID: <?php if(isset($_GET['pid'])){ ?><input style="width: 60px;display: inline;" type="text" class="form-control" name="pid" value="<?php echo isset($_GET['pid'])?(int)($_GET['pid']):'';?>"><?php } else { ?><a class="btn btn-warning" href="edit.php?post_type=wpdmpro"><?php _e( "Select Package" , "download-manager" ) ;?></a><?php } ?>
        Year:
        <select name="y" class="form-control wpdm-custom-select" style="width: 80px;min-width: 60px;display: inline;">
            <?php for($i=$my;$i<=date('Y');$i++) { $sel = $y==$i?'selected=selected':''; echo "<option $sel value='{$i}'>{$i}</option>";} ?>
        </select>
        Month: <select class="form-control wpdm-custom-select" name="m" style="min-width: 30px;width: 60px;display: inline;">
            <?php for($i=1;$i<=12;$i++) { $sel = $m==$i?'selected=selected':''; echo "<option $sel value='{$i}'>{$i}</option>";} ?>
        </select>
        <input type="submit" class="btn btn-secondary" value="Submit">

    </form>

