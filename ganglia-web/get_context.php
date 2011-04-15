<?php
/* $Id: get_context.php 2559 2011-04-11 22:01:34Z bernardli $ */

include_once "./functions.php";

$meta_designator = "Grid";
$cluster_designator = "Cluster Overview";

# Blocking malicious CGI input.
$input['clustername'] = isset($_GET["c"]) ?
    escapeshellcmd( clean_string( rawurldecode($_GET["c"]) ) ) : NULL;
$input['gridname'] = isset($_GET["G"]) ?
    escapeshellcmd( clean_string( rawurldecode($_GET["G"]) ) ) : NULL;
if($conf['case_sensitive_hostnames'] == 1) {
    $input['hostname'] = isset($_GET["h"]) ?
        escapeshellcmd( clean_string( rawurldecode($_GET["h"]) ) ) : NULL;
} else {
    $input['hostname'] = isset($_GET["h"]) ?
        strtolower( escapeshellcmd( clean_string( rawurldecode($_GET["h"]) ) ) ) : NULL;
}
$input['range'] = isset( $_GET["r"] ) && in_array($_GET["r"], array_keys( $conf['time_ranges'] ) ) ?
    escapeshellcmd( rawurldecode($_GET["r"])) : NULL;
$input['metricname'] = isset($_GET["m"]) ?
    escapeshellcmd( clean_string( rawurldecode($_GET["m"]) ) ) : NULL;
$input['metrictitle'] = isset($_GET["ti"]) ?
    escapeshellcmd( clean_string( rawurldecode($_GET["ti"]) ) ) : NULL;
$input['sort'] = isset($_GET["s"]) ?
    escapeshellcmd( clean_string( rawurldecode($_GET["s"]) ) ) : NULL;
$input['controlroom'] = isset($_GET["cr"]) ?
    escapeshellcmd( clean_string( rawurldecode($_GET["cr"]) ) ): NULL;
# Default value set in conf.php, Allow URL to overrride
if (isset($_GET["hc"]))
    //TODO: shouldn't set $conf from user input.
    $conf['hostcols'] = clean_number($_GET["hc"]);
if (isset($_GET["mc"]))
    $conf['metriccols'] = clean_number($_GET["mc"]);
# Flag, whether or not to show a list of hosts
$input['showhosts'] = isset($_GET["sh"]) ?
    clean_number( $_GET["sh"] ) : NULL;
# The 'p' variable specifies the verbosity level in the physical view.
$input['physical'] = isset($_GET["p"]) ?
    clean_number( $_GET["p"] ) : NULL;
$input['tree'] = isset($_GET["t"]) ?
    escapeshellcmd($_GET["t"] ) : NULL;
# A custom range value for job graphs, in -sec.
$input['jobrange'] = isset($_GET["jr"]) ?
    clean_number( $_GET["jr"] ) : NULL;
# A red vertical line for various events. Value specifies the event time.
$input['jobstart'] = isset($_GET["js"]) ?
    clean_number( $_GET["js"] ) : NULL;
# custom start and end
$input['cs'] = isset($_GET["cs"]) ?
    escapeshellcmd($_GET["cs"]) : NULL;
$input['ce'] = isset($_GET["ce"]) ?
    escapeshellcmd($_GET["ce"]) : NULL;
# The direction we are travelling in the grid tree
$input['gridwalk'] = isset($_GET["gw"]) ?
    escapeshellcmd( clean_string( $_GET["gw"] ) ) : NULL;
# Size of the host graphs in the cluster view
$input['clustergraphsize'] = isset($_GET["z"]) && in_array( $_GET[ 'z' ], $conf['graph_sizes_keys'] ) ?
    escapeshellcmd($_GET["z"]) : NULL;
# A stack of grid parents. Prefer a GET variable, default to cookie.
if (isset($_GET["gs"]) and $_GET["gs"])
    $input['gridstack'] = explode( ">", rawurldecode( $_GET["gs"] ) );
else if ( isset($_COOKIE['gs']) and $_COOKIE['gs'])
    $input['gridstack'] = explode( ">", $_COOKIE["gs"] );

if (isset($input['gridstack']) and $input['gridstack']) {
   foreach( $input['gridstack'] as $key=>$value )
      $input['gridstack'][ $key ] = clean_string( $value );
}

// 
if ( isset($_GET['host_regex']) )
  $user['host_regex'] = $_GET['host_regex'];

if ( isset($_GET['max_graphs']) && is_numeric($_GET['max_graphs'] ) )
  $user['max_graphs'] = $_GET['max_graphs'];


# Assume we are the first grid visited in the tree if there is no gridwalk
# or gridstack is not well formed. Gridstack always has at least one element.
if ( !isset($input['gridstack']) or !strstr($input['gridstack'][0], "http://"))
    $initgrid = TRUE;

# Default values
if (!isset($conf['hostcols']) || !is_numeric($conf['hostcols'])) $conf['hostcols'] = 4;
if (!isset($conf['metriccols']) || !is_numeric($conf['metriccols'])) $conf['metriccols'] = 2;
if (!is_numeric($input['showhosts'])) $input['showhosts'] = 1;

# Filters
if(isset($_GET["choose_filter"]))
{
  $req_choose_filter = $_GET["choose_filter"];
  $input['choose_filter'] = array();
  foreach($req_choose_filter as $k_req => $v_req)
  {
    $k = escapeshellcmd( clean_string( rawurldecode ($k_req)));
    $v = escapeshellcmd( clean_string( rawurldecode ($v_req)));
    $input['choose_filter'][$k] = $v;
  }
}

# Set context.
if(!$input['clustername'] && !$input['hostname'] && $input['controlroom'])
   {
      $context = "control";
   }
else if (isset($input['tree']))
   {
      $context = "tree";
   }
else if(!$input['clustername'] and !$input['gridname'] and !$input['hostname'])
   {
      $context = "meta";
   }
else if($input['gridname'])
   {
      $context = "grid";
   }
else if ($input['clustername'] and !$input['hostname'] and $input['physical'])
   {
      $context = "physical";
   }
else if ($input['clustername'] and !$input['hostname'] and !$input['showhosts'])
   {
      $context = "cluster-summary";
   }
else if($input['clustername'] and !$input['hostname'])
   {
      $context = "cluster";
   }
else if($input['clustername'] and $input['hostname'] and $input['physical'])
   {
      $context = "node";
   }
else if($input['clustername'] and $input['hostname'])
   {
      $context = "host";
   }

if (!$input['range'])
    $input['range'] = $conf['default_time_range'];

$end = "N";

# $conf['time_ranges'] defined in conf.php
if( $user['range'] == 'job' && isSet( $user['jobrange'] ) ) {
    $start = $user['jobrange'];
} else if( isSet( $conf['time_ranges'][ $user['range'] ] ) ) {
    $start = $conf['time_ranges'][ $user['range'] ] * -1 . "s";
} else {
    $start = $conf['time_ranges'][ $conf['default_time_range'] ] * -1 . "s";
}

if ($input['cs'] or $input['ce'])
    $input['range'] = "custom";

if (!$input['metricname'])
    $input['metricname'] = $conf['default_metric'];

if (!$input['sort'])
    $input['sort'] = "by name";

# Since cluster context do not have the option to sort "by hosts down" or
# "by hosts up", therefore change sort order to "descending" if previous
# sort order is either "by hosts down" or "by hosts up"
if ($context == "cluster") {
    if ($input['sort'] == "by hosts up" || $input['sort'] == "by hosts down") {
        $input['sort'] = "descending";
    }
}

// TODO: temporary step until all scripts expect $input.
extract( $input );

# A hack for pre-2.5.0 ganglia data sources.
$always_constant = array(
   "swap_total" => 1,
   "cpu_speed" => 1,
   "swap_total" => 1
);

$always_timestamp = array(
   "gmond_started" => 1,
   "reported" => 1,
   "sys_clock" => 1,
   "boottime" => 1
);

# List of report graphs
$reports = array(
   "load_report" => "load_one",
   "cpu_report" => 1,
   "mem_report" => 1,
   "network_report" => 1,
   "packet_report" => 1
);

?>
