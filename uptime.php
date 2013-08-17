<?php
function sec2human($time) {
  $seconds = $time%60;
	$mins = floor($time/60)%60;
	$hours = floor($time/60/60)%24;
	$days = floor($time/60/60/24);
	return $days > 0 ? $days . ' day'.($days > 1 ? 's' : '') : $hours.':'.$mins.':'.$seconds;
}

$array = array();
$fh = fopen('/proc/uptime', 'r');
$uptime = fgets($fh);
fclose($fh);
$uptime = explode('.', $uptime, 2);
$array['uptime'] = sec2human($uptime[0]);


$fh = fopen('/proc/meminfo', 'r');
  $mem = 0;
  while ($line = fgets($fh)) {
    $pieces = array();
    if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
      $memtotal = $pieces[1];
    }
    if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
      $memfree = $pieces[1];
    }
    if (preg_match('/^Cached:\s+(\d+)\skB$/', $line, $pieces)) {
      $memcache = $pieces[1];
    }
    if (preg_match('/^SwapTotal:\s+(\d+)\skB$/', $line, $pieces)) {
      $swaptotal = $pieces[1];
    }
    if (preg_match('/^SwapFree:\s+(\d+)\skB$/', $line, $pieces)) {
      $swapfree = $pieces[1];
    break;
    }
  }
fclose($fh);

$memused = $memtotal - ($memcache + $memfree);
$memprecent = $memused / $memtotal * 100;

if ($memprecent >= "75") { $memlevel = "danger"; }
elseif ($memprecent >= "36") { $memlevel = "warning"; }
elseif ($memprecent <= "35") { $memlevel = "success"; }

$array['memory'] = '<div class="progress progress-striped active">
<div class="bar bar-'.$memlevel.'" style="width:'.$memprecent.'%;">'.round($memused/1000).'&nbsp;mb</div>
</div>';

$buffused = $memtotal - $memfree - $memused;
$buffpercent = round($buffused / $memtotal * 100);

$array['buff'] = '<div class="progress progress-striped active">
<div class="bar bar-'.$memlevel.'" style="width:'.$buffpercent.'%;">'.round($buffused/1000).'&nbsp;mb</div>
</div>';

$swapused = $swaptotal - $swapfree;
$swapperect = round($swapused / $swaptotal * 100);

if ($swapperect >= "75") { $swaplevel = "danger"; }
elseif ($swapperect >= "36") { $swaplevel = "warning"; }
elseif ($swapperect <= "35") { $swaplevel = "success"; }

$array['swap'] = '<div class="progress progress-striped active">
<div class="bar bar-'.$swaplevel.'" style="width:'.$swapperect.'%;">'.round($swapused/1000).'&nbsp;mb</div>
</div>';

$hddtotal = disk_total_space("/");
$hddfree = disk_free_space("/");
$hddused = $hddtotal - $hddfree;
$hddpercent = round($hddused / $hddtotal * 100);

if ($hddpercent >= "75") { $hddlevel = "danger"; }
elseif ($hddpercent >= "36") { $hddlevel = "warning"; }
elseif ($hddpercent <= "35") { $hddlevel = "success"; }

$array['hdd'] = '<div class="progress progress-striped active">
<div class="bar bar-'.$hddlevel.'" style="width:'.$hddpercent.'%;">'.round($hddused*0.00000000098,2).'&nbsp;gb</div>
</div>';

$load = sys_getloadavg();
$array['load'] = $load[0];

$array['online'] = '<div class="progress">
<div class="bar bar-success" style="width: 100%;"><small>Up</small></div>
</div>';

echo json_encode($array);
