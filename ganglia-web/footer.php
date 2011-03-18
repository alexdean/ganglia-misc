<?php
/* $Id: footer.php 2362 2010-11-26 01:43:53Z vvuksan $ */
$tpl = new Dwoo_Template_File( template("footer.tpl") );
$data = new Dwoo_Data();

include_once "./version.php";
$data->assign("webfrontend_version",$ganglia_version);

# Get rrdtool version
$rrdtool_version = array();
exec($conf['rrdtool'], $rrdtool_version);
$rrdtool_version = explode(" ", $rrdtool_version[0]);
$rrdtool_version = $rrdtool_version[1];
$data->assign("rrdtool_version",$rrdtool_version);

# "gmetad", "gmetad-python", "gmond", from XML
$data->assign("webbackend_component", $backend_component['source']);
$data->assign("webbackend_version",$backend_component['version']);

$data->assign("parsetime", sprintf("%.4f", $parsetime) . "s");

$dwoo->output($tpl, $data);
?>
