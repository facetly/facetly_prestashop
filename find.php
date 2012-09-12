<?php
global $smarty;
include('../../config/config.inc.php');
require_once("facetly_api.php");
require_once("facetly_function.php");
require_once("facetly.php");
$facetly_output = Facetly::search();
include('../../header.php');

$smarty->assign('facetly_results', $facetly_output->results); // creation of our variable
$smarty->display(dirname(__FILE__).'/facetly_page.tpl');

include('../../footer.php');
?>
