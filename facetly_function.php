<?php
	function facetly_map(){
		static $var;
	
		if (empty($var)){
			$consumer_secret = strtolower(Configuration::get('facetly_consumer_secret'));
			$consumer_key = strtolower(Configuration::get('facetly_consumer_key'));
			$path_server = Configuration::get('facetly_server_name'). "/field/select";
			
			
			
			$post = array(
					"key" => $consumer_key,
					"secret" => $consumer_secret,
			);
			$Curl_Session = curl_init($path_server);
			curl_setopt ($Curl_Session, CURLOPT_POST, 1);
			curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, $post);
			curl_setopt ($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
			$output = curl_exec ($Curl_Session);
			curl_close ($Curl_Session);
			$var = json_decode($output);
		}
		return $var;

	}
	
	function facetly_save_template($facetly_page_template, $facetly_search_template, $facetly_facet_template){
		//Configuration::updateValue('facetly_page_template', facetly_configuration_encode($facetly_page_template));
		Configuration::updateValue('facetly_search_template', facetly_configuration_encode($facetly_search_template));
		Configuration::updateValue('facetly_facet_template', facetly_configuration_encode($facetly_facet_template));
		

		
		$consumer_secret = strtolower(Configuration::get('facetly_consumer_secret'));
		$consumer_key = strtolower(Configuration::get('facetly_consumer_key'));
		$path_server = Configuration::get('facetly_server_name'). "/template/update";
		$post = array(
					"key" => $consumer_key,
					"secret" => $consumer_secret,
					"tplpage" => $facetly_page_template,
					"tplsearch" => $facetly_search_template,
					"tplfacet" => $facetly_facet_template,
		);
		
		//print_r($post);
		//exit();	
		$Curl_Session = curl_init($path_server);
		curl_setopt ($Curl_Session, CURLOPT_POST, 1);
		//curl_setopt ($Curl_Session, CURLOPT_HEADER, TRUE);
		curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, $post);
		curl_setopt ($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec ($Curl_Session);
		$header = curl_getinfo($Curl_Session);
		curl_close ($Curl_Session);

		if (!empty($output)) {
			return '<div class="conf">
						'.$output.'
					</div>';
		}
		else {
			return '<div class="conf">
						No Reply from Facetly Server
					</div>';
		}
	}

	function facetly_configuration_encode($template){
		$template = str_replace("\n","<br />",$template);
		$template = str_replace("\r","",$template);
		$template = str_replace(" ","\_",$template);
		$template = str_replace("<","[",$template);
		$template = str_replace(">","]",$template);
		return $template;
	}

	function facetly_configuration_decode($template){
		$template = str_replace("]",">",$template);
		$template = str_replace("[","<",$template);
		$template = str_replace("\_"," ",$template);
		$template = str_replace("<br />","\n",$template);
		return $template;
	}

	function facetly_prestashop_category_mapping(){
		$cats = Category::getCategories( (int)($cookie->id_lang), true, false  ) ;
		$looping = count($cats);
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));

		for($i=0;$i<$looping;$i++){
			$indeks = $cats[$i]['id_category'];
			if($defaultLanguage == (int)$cats[$i]['id_lang']){
				$mapping[$indeks]['id_category'] = (int)$cats[$i]['id_category'];
				$mapping[$indeks]['name'] = $cats[$i]['name'];
				$mapping[$indeks]['id_parent'] = $cats[$i]['id_parent'];
				$mapping[$indeks]['depth'] = (int)$cats[$i]['level_depth'];		
			}
		}
		//$id_cats=2;
		for($id_cats = 1; $id_cats <= $looping; $id_cats++){
			$chain_cats = NULL;
			if($mapping[$id_cats]['depth'] >=1){
				$deep = $mapping[$id_cats]['depth'];
				$chain_cats = $mapping[$id_cats]['name'];
				//printf($mapping[$id_cats]['name']);
				$parents = $mapping[$id_cats]['id_parent'];
				for($x=1;$x<=$deep;$x++){
					if((int)$mapping[$parents]['id_parent'] == 0)break;
					$chain_cats = $mapping[$parents]['name'].';'.$chain_cats;
					$parents = $mapping[$parents]['id_parent'];
				}
			}
			$mapping[$id_cats]['chain_cats'] = $chain_cats;
		}
		return $mapping;
	}

	function facetly_base_uri(){
		$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'].__PS_BASE_URI__;
		return $base_url;
	}
	
	function facetly_truncate(){
		facetly_temporary_table_truncate();
		require_once("facetly_api.php");
		$facetly = new facetly_api;
		$api_server = Configuration::get('facetly_server_name');
		$api_path = "product/truncate";
		$api_method = "POST";
		$api_data = array(
		  "key" => Configuration::get('facetly_consumer_key'),
		  "secret" => Configuration::get('facetly_consumer_secret'),
		);
		$facetly->setServer($api_server);
		$api_output = $facetly->call($api_path, $api_data, $api_method);
		$return = json_decode($api_output);
		print_r($return);

	}
	
	function facetly_temporary_table_truncate(){
		Db::getInstance()->Execute('
			TRUNCATE TABLE 
				`'._DB_PREFIX_.'facetly_add_product` 
		');

		Db::getInstance()->Execute('
			TRUNCATE TABLE
				`'._DB_PREFIX_.'facetly_del_product` 
		');
	}
	
	
	function facetly_load_product($id_product,$id_lang){
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT 
				p.*, 
				pl.`description`, 
				pl.`description_short`, 
				pl.`link_rewrite`, 
				pl.`meta_description`, 
				pl.`meta_keywords`, 
				pl.`meta_title`, 
				pl.`name`, 
				p.`ean13`, 
				p.`upc`,
				i.`id_image`, 
				il.`legend`, 
				t.`rate`, 
				m.`name` AS manufacturer_name, 
				DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
				(p.`price` * ((100 + (t.`rate`))/100)) AS orderprice, 
				pa.id_product_attribute
			FROM 
				`'._DB_PREFIX_.'product` p
			LEFT JOIN 
				`'._DB_PREFIX_.'product_lang` pl 
					ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
			LEFT OUTER JOIN 
				`'._DB_PREFIX_.'product_attribute` pa 
					ON (p.`id_product` = pa.`id_product` AND `default_on` = 1)
			LEFT JOIN 
				`'._DB_PREFIX_.'image` i 
					ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
			LEFT JOIN 
				`'._DB_PREFIX_.'image_lang` il 
					ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
			LEFT JOIN 
				`'._DB_PREFIX_.'tax_rule` tr 
					ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
						AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
						AND tr.`id_state` = 0)
			LEFT JOIN 
				`'._DB_PREFIX_.'tax` t 
					ON (t.`id_tax` = tr.`id_tax`)
			LEFT JOIN 
				`'._DB_PREFIX_.'manufacturer` m 
					ON (m.`id_manufacturer` = p.`id_manufacturer`)
			WHERE 
				p.`active` = 1 
			AND 
				p.`id_product` = '.$id_product
		);
		return $result;
	}
	
		function facetly_load_group_product($id_lang,$page,$limit){
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT 
				p.*, 
				pl.`description`, 
				pl.`description_short`, 
				pl.`link_rewrite`, 
				pl.`meta_description`, 
				pl.`meta_keywords`, 
				pl.`meta_title`, 
				pl.`name`, 
				p.`ean13`, 
				p.`upc`,
				i.`id_image`, 
				il.`legend`, 
				t.`rate`, 
				m.`name` AS manufacturer_name, 
				DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
				(p.`price` * ((100 + (t.`rate`))/100)) AS orderprice, 
				pa.id_product_attribute
			FROM 
				`'._DB_PREFIX_.'product` p
			LEFT JOIN 
				`'._DB_PREFIX_.'product_lang` pl 
					ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
			LEFT OUTER JOIN 
				`'._DB_PREFIX_.'product_attribute` pa 
					ON (p.`id_product` = pa.`id_product` AND `default_on` = 1)
			LEFT JOIN 
				`'._DB_PREFIX_.'image` i 
					ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
			LEFT JOIN 
				`'._DB_PREFIX_.'image_lang` il 
					ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
			LEFT JOIN 
				`'._DB_PREFIX_.'tax_rule` tr 
					ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
						AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
						AND tr.`id_state` = 0)
			LEFT JOIN 
				`'._DB_PREFIX_.'tax` t 
					ON (t.`id_tax` = tr.`id_tax`)
			LEFT JOIN 
				`'._DB_PREFIX_.'manufacturer` m 
					ON (m.`id_manufacturer` = p.`id_manufacturer`)
			WHERE 
				p.`active` = 1 
			ORDER BY
				p.`id_product`
			LIMIT '.(int)($page * $limit).', '.(int)(($page+1) * $limit)
		);
		return $result;
	}
	
	function facetly_add_temp($id_product){
		Db::getInstance()->Execute('
			DELETE FROM 
				`'._DB_PREFIX_.'facetly_add_product` 
			WHERE
				id_product = '.(int)($id_product).'
		');

		Db::getInstance()->Execute('
			DELETE FROM 
				`'._DB_PREFIX_.'facetly_del_product` 
			WHERE
				id_product = '.(int)($id_product).'
		');
				
		Db::getInstance()->Execute('
			INSERT INTO 
				`'._DB_PREFIX_.'facetly_add_product` 
				(`id_product`)
			VALUES 
				('.(int)($id_product).')
		');
	}
	
	function facetly_del_temp($id_product){
		Db::getInstance()->Execute('
			DELETE FROM 
				`'._DB_PREFIX_.'facetly_add_product` 
			WHERE
				id_product = '.(int)($id_product).'
		');

		Db::getInstance()->Execute('
			DELETE FROM 
				`'._DB_PREFIX_.'facetly_del_product` 
			WHERE
				id_product = '.(int)($id_product).'
		');
				
		Db::getInstance()->Execute('
			INSERT INTO 
				`'._DB_PREFIX_.'facetly_del_product` 
				(`id_product`)
			VALUES 
				('.(int)($id_product).')
		');
	}
	
	function facetly_reindex($page,$limit){
		$start = $page;
		require_once("facetly_api.php");
		//$sampel = Product::getNewProducts((int)Configuration::get('facetly_language'), $page, $limit, false, 'id_product', 'DESC');
		$sampel = facetly_load_group_product((int)Configuration::get('facetly_language'),$page,$limit);
		//print_r($sampel);
		$mapping_cats = facetly_prestashop_category_mapping();
		$facetly = new facetly_api;
		$api_server = Configuration::get('facetly_server_name');
		$consumer_key = Configuration::get('facetly_consumer_key');
		$consumer_secret = Configuration::get('facetly_consumer_secret');
		$api_path = "product/insert";
		$api_method = "POST";
		
		
		for($i=0;$i<$limit;$i++){
			$id_product = $sampel[$i]['id_product'];
			
			if($id_product == NULL) continue;
			
			$field_title = Configuration::get('facetly_field_title');
			$title = $sampel[$i][$field_title];
			
			$field_body = Configuration::get('facetly_field_body');
			$body = $sampel[$i][$field_body];
			
			$id_category = $sampel[$i]['id_category_default'];
			$chain_cats = $mapping_cats[$id_category]['chain_cats'];
			
			$field_price = Configuration::get('facetly_field_price');
			$price = $sampel[$i][$field_price];
			
			$url = facetly_base_uri()."product.php?id_product=".$id_product;
			
			$field_imageurl = Configuration::get('facetly_field_imageurl');
			if($field_imageurl != NULL) $field_imageurl = '-'.$field_imageurl;
			$id_image = $sampel[$i]['id_image'];
			if($id_image!=NULL){
				$url_image = facetly_base_uri()."img/p/".$id_product."-".$id_image.$field_imageurl.".jpg";
				$dir = "../img/p/".$id_product."-".$id_image.$field_imageurl.".jpg";
				if(file_exists($dir)==0){
					$url_image = facetly_base_uri()."img/p/".floor($id_image/10)."/".($id_image%10)."/".$id_image.$field_imageurl.".jpg";
				}				
			}
			else{
				$url_image = facetly_base_uri()."img/p/en-default".$field_imageurl.".jpg";
			}
			$field_created = Configuration::get('facetly_field_created');
			$created = strtotime($sampel[$i][$field_created]);
			
			$api_data = array(
				"key" => $consumer_key,
				"secret" => $consumer_secret,
				"id" => $id_product,
				"title" => $title,
				"body" => $body,
				"category" => $chain_cats,
				"price" => $price,
				"url" => $url,
				"imageurl" => $url_image,
				"created" => $created,
			);
			//print_r($api_data);
			$facetly->setServer($api_server);
			$api_output = $facetly->call($api_path, $api_data, $api_method);
			$return = json_decode($api_output);
			//print_r($return);
		}
	}
	
	function facetly_insert_server($id_product){
		$id_lang = Configuration::get('facetly_language');
		$product = facetly_load_product($id_product,$id_lang);
	
		$mapping_cats = facetly_prestashop_category_mapping();
		$facetly = new facetly_api;
		$api_server = Configuration::get('facetly_server_name');
		$consumer_key = Configuration::get('facetly_consumer_key');
		$consumer_secret = Configuration::get('facetly_consumer_secret');
		$api_path = "product/insert";
		$api_method = "POST";
		//print_r($product);
		
		$product = $product[0];
		$id_product = $product['id_product'];
			
		$field_title = Configuration::get('facetly_field_title');
		$title = $product[$field_title];
			
		$field_body = Configuration::get('facetly_field_body');
		$body = $product[$field_body];
			
		$id_category = $product['id_category_default'];
		$chain_cats = $mapping_cats[$id_category]['chain_cats'];
			
		$field_price = Configuration::get('facetly_field_price');
		$price = $product[$field_price];
			
		$url = facetly_base_uri()."product.php?id_product=".$id_product;
			
		$field_imageurl = Configuration::get('facetly_field_imageurl');
		if($field_imageurl != NULL) $field_imageurl = '-'.$field_imageurl;
		$id_image = $product['id_image'];
		if($id_image!=NULL){
			$url_image = facetly_base_uri()."img/p/".$id_product."-".$id_image.$field_imageurl.".jpg";
			$dir = "../../img/p/".$id_product."-".$id_image.$field_imageurl.".jpg";
			if(file_exists($dir)==0){
				$url_image = facetly_base_uri()."img/p/".floor($id_image/10)."/".($id_image%10)."/".$id_image.$field_imageurl.".jpg";
			}			
		}
		else{
			$url_image = facetly_base_uri()."img/p/en-default".$field_imageurl.".jpg";
		}
			
		$field_created = Configuration::get('facetly_field_created');
		$created = strtotime($product[$field_created]);
			
		$api_data = array(
			"key" => $consumer_key,
			"secret" => $consumer_secret,
			"id" => $id_product,
			"title" => $title,
			"body" => $body,
			"category" => $chain_cats,
			"price" => $price,
			"url" => $url,
			"imageurl" => $url_image,
			"created" => $created,
		);
		//print_r($product);
		//	print_r($api_data);
		$facetly->setServer($api_server);
		$api_output = $facetly->call($api_path, $api_data, $api_method);
		$return = json_decode($api_output);
	}
	
	function facetly_delete_server($id_product){
		require_once("facetly_api.php");
		$facetly = new facetly_api;
		$api_server = Configuration::get('facetly_server_name');
		$consumer_key = Configuration::get('facetly_consumer_key');
		$consumer_secret = Configuration::get('facetly_consumer_secret');
		$api_path = "product/delete";
		$api_method = "POST";
		$api_data = array(
		  "key" => $consumer_key,
		  "secret" => $consumer_secret,
		  "id" => $id_product,
		);
		//print_r($api_data);
		$facetly->setServer($api_server);
		$api_output = $facetly->call($api_path, $api_data, $api_method);
		$return = json_decode($api_output);
	}
?>