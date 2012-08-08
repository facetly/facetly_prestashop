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

//print_r($_SERVER);

//$sampel = facetly_load_group_product((int)Configuration::get('facetly_language'),0,12);
		//print_r($sampel);

		//print_r(file_exists("../../img/p/50-59-home.jpg"));
		
//print_r(facetly_prestashop_category_mapping());

//print_r(facetly_map());

//printf(intval($cookie->id_lang));
//print_r($cookie);
//$sampel = Product::getNewProducts(intval($cookie->id_lang), 1, 3, false, 'id_product', 'DESC');
//print_r($sampel);
//print_r(Image::getImages((int)($cookie->id_lang), 45));
//printf(__PS_BASE_URI__);

/*
$sql = 'SELECT * FROM '._DB_PREFIX_.'country_lang

	WHERE id_lang = 1';

if ($results = Db::getInstance()->ExecuteS($sql))

	foreach ($results as $row)

		echo $row['id_country']." :: ".$row['name']."<br />";
*/
include('../../footer.php');
?>
