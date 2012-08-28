<?php

	include('../../config/config.inc.php');
	require_once("facetly_api.php");
	require_once("facetly_function.php");
	require_once("facetly.php");		


	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
					SELECT 
						f.`id_add`,
						f.`id_product`
					FROM 
						`'._DB_PREFIX_.'facetly_add_product` f 
				');
				
	for($i=0;$result[$i]!=NULL;$i++){
		$id_product = $result[$i]['id_product'];
		facetly_insert_server($id_product);
	}

	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
					SELECT 
						f.`id_del`,
						f.`id_product`
					FROM 
						`'._DB_PREFIX_.'facetly_del_product` f 
				');
				
	for($i=0;$result[$i]!=NULL;$i++){
		$id_product = $result[$i]['id_product'];
		facetly_delete_server($id_product);
	}
	
	facetly_temporary_table_truncate();
?>