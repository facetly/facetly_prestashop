<?php
require_once("facetly_function.php");
class Facetly extends Module{

private $_html = '';

function __construct(){
    $version_mask = explode('.', _PS_VERSION_, 2);
    $version_test = $version_mask[0] > 0 && $version_mask[1] > 3;
    $this->name = 'facetly';
    $this->tab = $version_test ? 'search_filter' : 'search_filter';
    if ($version_test)
        $this->author = 'Skyshi';
    $this->version = '1.0';
    parent::__construct();
    $this->displayName = $this->l('Facetly Module');
    $this->description = $this->l('Facetly Search Engine.');
}


public function install(){
    parent::install();
    if (!$this->registerHook('leftColumn'))
      return false;
    
    if (!$this->registerHook('addproduct'))
      return false;
      
    if (!$this->registerHook('search'))
      return false;
	  
	if (!$this->registerHook('header'))
      return false;
	  
	if (!$this->registerHook('deleteproduct'))
      return false;
	  
	if (!$this->registerHook('updateproduct'))
      return false;
	
	if (!Db::getInstance()->Execute('
	CREATE TABLE `'._DB_PREFIX_.'facetly_add_product` (
	`id_add` int(10) unsigned NOT NULL auto_increment,
	`id_product` int(10) NOT NULL,
	PRIMARY KEY (`id_add`))
	'))
		return false;
	
	if (!Db::getInstance()->Execute('
	CREATE TABLE `'._DB_PREFIX_.'facetly_del_product` (
	`id_del` int(10) unsigned NOT NULL auto_increment,
	`id_product` int(10) NOT NULL,
	PRIMARY KEY (`id_del`))
	'))
		return false;
	
	return true;
	
}

public function uninstall()
{
	if (!parent::uninstall())
		return false;
	return (Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'facetly_add_product`')AND
			Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'facetly_del_product`'));
}


public function getContent(){
    if (Tools::isSubmit('submitConfig')){
	$facetly_consumer_key = trim(Tools::getValue('facetly_consumer_key'));
	$facetly_consumer_secret = trim(Tools::getValue('facetly_consumer_secret'));
	$facetly_server_name = trim(Tools::getValue('facetly_server_name'));
	$limit = (int)Tools::getValue('facetly_search_limit');
	$add_var = trim(Tools::getValue('facetly_additional_variable'));
	
	if (!empty($facetly_consumer_key) && !empty($facetly_consumer_secret) && !empty($facetly_server_name)){	
		Configuration::updateValue('facetly_language', Tools::getValue('facetly_language'));
		Configuration::updateValue('facetly_consumer_key', $facetly_consumer_key);
		Configuration::updateValue('facetly_consumer_secret', $facetly_consumer_secret);
		Configuration::updateValue('facetly_server_name', $facetly_server_name);
		Configuration::updateValue('facetly_search_limit', $limit);
		Configuration::updateValue('facetly_additional_variable', $add_var);
		$this->_html .= '<div class="conf">
							Configuration Saved
						</div>';

	}
	else{
		$this->_html .= '<div class="error">
							<img src="../img/admin/error2.png">
							Please check your consumer key or consumer secret or server name.
						</div>';
	}			
}	
	
if (Tools::isSubmit('submitTemplate')){		
	
	$facetly_page_template = Tools::getValue('facetly_page_template');
	$facetly_search_template = Tools::getValue('facetly_search_template');
	$facetly_facet_template = Tools::getValue('facetly_facet_template');
	
	$message = facetly_save_template($facetly_page_template, $facetly_search_template, $facetly_facet_template);
	$this->_html .= $message;
}

if (Tools::isSubmit('submitField')){		
	$facetly_map = facetly_map();
	if(!empty($facetly_map)){
		$field = $facetly_map->field;
		$looping = count($field);
		for($i=0;$i<$looping;$i++){
			$temporary = NULL;
			$temporary = Tools::getValue('field_'.$field[$i]->name);
			Configuration::updateValue('facetly_field_'.$field[$i]->name, $temporary);
		}
	}	
		
}

if (Tools::isSubmit('submitReindex')){		
	facetly_truncate();
	$this->_html .= '<link rel="stylesheet" href="'.facetly_base_uri().'modules/facetly/css/progressBar.css">';
	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT 
			MAX(p.id_product) as maks 
		FROM 
			`'._DB_PREFIX_.'product` p
	');


	$max_id = $result[0]['maks'];
	Configuration::updateValue('facetly_max_id', $max_id);
	$page = $_GET['page'];
	$tab = $_GET['tab'];
	$configure = $_GET['configure'];
	$token = $_GET['token'];
	$tab_module = $_GET['tab_module'];
	$module_name = $_GET['module_name'];
	$this->_html .= '	<div class="meter">
				<span style="width: 100%">Initializing</span>
			</div>';
	echo '<meta http-equiv="refresh" content="5;URL='.$_SERVER['PHP_SELF'].'?tab='.$tab.'&configure='.$configure.'&token='.$token.'&tab_module='.$tab_module.'&module_name='.$module_name.'&page=0">';
}

else if($_GET['page'] != NULL){
	$this->_html .= '<link rel="stylesheet" href="'.facetly_base_uri().'modules/facetly/css/progressBar.css">';
	$max_id = Configuration::get('facetly_max_id');
	$limit = 50;
	$max_page = ceil($max_id/$limit);
	$page = $_GET['page'];
	$tab = $_GET['tab'];
	$configure = $_GET['configure'];
	$token = $_GET['token'];
	$tab_module = $_GET['tab_module'];
	$module_name = $_GET['module_name'];
	$bar_val = $page*100/$max_page;
	$this->_html .= '	<div class="meter">
				<span style="width: '.$bar_val.'%"></span>
			</div>';
	facetly_reindex($page,$limit);
	if($page < $max_page) {				
		$page = $page + 1;
		echo '<meta http-equiv="refresh" content="5;URL='.$_SERVER['PHP_SELF'].'?tab='.$tab.'&configure='.$configure.'&token='.$token.'&tab_module='.$tab_module.'&module_name='.$module_name.'&page='.$page.'">';
	}else{
		echo '<meta http-equiv="refresh" content="5;URL='.$_SERVER['PHP_SELF'].'?tab='.$tab.'&configure='.$configure.'&token='.$token.'&tab_module='.$tab_module.'&module_name='.$module_name.'">';
	}
}
else{
	$this->_displayForm();
}
return $this->_html;
}


private function _displayForm(){
	$override = Configuration::get('facetly_override_search');
	if( $override == 0 || $override == NULL) $off = 'checked="checked"';
	else $on = 'checked="checked"';

	$language = '';

	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT 
			p.`id_lang`,
			p.`name`
		FROM 
			`'._DB_PREFIX_.'lang` p
		WHERE 
			p.`active` = 1 
	');
	
	$opsi = '';
	$opsi = '<option value= "0">-----</option>';

	for($i=0;$result[$i]!=NULL;$i++){
		if((int)Configuration::get('facetly_language') == $result[$i]['id_lang']) {
			$select = 'selected="selected"';
		}
		else $select = NULL;
		
		$opsi .= '<option value= "'.$result[$i]['id_lang'].'" '.$select.'>'.$result[$i]['name'].'</option>'; 
	}

	$language .= '<select name="facetly_language">';
	$language .= $opsi;
	$language .= '</select>';

	$this->_html .= '
	<fieldset>
	<h2>Facetly Configuration</h2>
	<form action="'.$_SERVER['REQUEST_URI'].'" method="post" class="form-item">
		<label>'.$this->l('Language').'</label>
		<div class="margin-form">
			'.$language.'
		</div>
		<div class="description"></div>

		<label>'.$this->l('Consumer Key').'</label>
		<div class="margin-form">
			<input type="text" name="facetly_consumer_key" value="'.Configuration::get('facetly_consumer_key').'" size="40" /> ex: qhduafdh
		</div>
		

		<label>'.$this->l('Consumer Secret').'</label>
		<div class="margin-form">
			<input type="text" name="facetly_consumer_secret" value="'.Configuration::get('facetly_consumer_secret').'" size="40" /> ex: q5yvmddqntukobeoszi6zuqmwvy9wwsv
		</div>

		<label>'.$this->l('Server Name').'</label>
		<div class="margin-form">
			<input type="text" name="facetly_server_name" value="'.Configuration::get('facetly_server_name').'" size="40" /> ex: http://sg1.facetly.com/1
		</div>

		<label>'.$this->l('Search Limit Setting').'</label>
		<div class="margin-form">
			<input type="text" name="facetly_search_limit" value="'.Configuration::get('facetly_search_limit').'" size="40" /> ex: 5
		</div>
		
		<label>'.$this->l('Additional variable').'</label>
		<div class="margin-form">
			<input type="text" name="facetly_additional_variable" value="'.Configuration::get('facetly_additional_variable').'" size="40" /> ex: _op[category]=or
		</div>
		
		
		<!--<label>'.$this->l('Override').'</label>
		<div class="margin-form">
			<input type="radio" name="facetly_override_search" value="0" '.$off.' />off <br />
			<input type="radio" name="facetly_override_search" value="1" '.$on.'/>on
		</div>-->
		
		<input type="submit" name="submitConfig" value="'.$this->l('Save Configuration').'" class="button" />
	</form>
	</fieldset>
	  ';
	  
	
	$facetly_map = facetly_map();
	if(!empty($facetly_map)){
	$field = $facetly_map->field;
	$looping = count($field);
	
	for($i=0;$i<$looping;$i++){
		$config_name = "field_".$field[$i]->name;
		$config_value = Configuration::get('facetly_'.$config_name);
		$label_config = ".field.".$field[$i]->name;
		
		if($config_name == "field_title"){
			$map_facetly .= '<label>'.$this->l($label_config).'</label>';
			$map_facetly .= '<select name="'.$config_name.'">';
			if($config_value == "name") $select_name = 'selected="selected"';
			
			$map_facetly .= '<option value= "">-----</option>';
			$map_facetly .= '<option value= "name" '.$select_name.'>name</option>';
			$map_facetly .= '</select><br /><br />';
		}
		
		else if($config_name == "field_body"){
			$map_facetly .= '<label>'.$this->l($label_config).'</label>';
			$map_facetly .= '<select name="'.$config_name.'">';
			if($config_value == "description") $select_desc = 'selected="selected"';
			else if($config_value == "description_short")$select_desc_short = 'selected="selected"';
			
			$map_facetly .= '<option value= "">-----</option>';
			$map_facetly .= '<option value= "description" '.$select_desc.'>description</option>';
			$map_facetly .= '<option value= "description_short" '.$select_desc_short.'>description short</option>';
			$map_facetly .= '</select><br /><br />';
		}
	
		else if($config_name == "field_price"){
			$map_facetly .= '<label>'.$this->l($label_config).'</label>';
			$map_facetly .= '<select name="'.$config_name.'">';
			if($config_value == "price") $select_price = 'selected="selected"';
			else if($config_value == "wholesale_price") $select_wholesale_price = 'selected="selected"';
			
			$map_facetly .= '<option value= "">-----</option>';
			$map_facetly .= '<option value= "price" '.$select_price.'>Price</option>';
			$map_facetly .= '<option value= "wholesale_price" '.$select_wholesale_price.'>Wholesale price</option>';
			$map_facetly .= '</select><br /><br />';
		}
	
		else if($config_name == "field_category"){
			$map_facetly .= '<label>'.$this->l($label_config).'</label>';
			$map_facetly .= '<select name="'.$config_name.'">';
			if($config_value == "category") $select_category = 'selected="selected"';
			
			$map_facetly .= '<option value= "">-----</option>';
			$map_facetly .= '<option value= "category" '.$select_category.'>category</option>';
			$map_facetly .= '</select><br /><br />';
		}
		
		else if($config_name == "field_created"){
			$map_facetly .= '<label>'.$this->l($label_config).'</label>';
			$map_facetly .= '<select name="'.$config_name.'">';
			if($config_value == "date_add") $select_add = 'selected="selected"';
			else if($config_value == "date_upd") $select_upd = 'selected="selected"';
			
			$map_facetly .= '<option value= "">-----</option>';
			$map_facetly .= '<option value= "date_add" '.$select_add.'>Date Add</option>';
			$map_facetly .= '<option value= "date_upd" '.$select_upd.'>Date Updated</option>';
			$map_facetly .= '</select><br /><br />';
		}
		
		else if($config_name == "field_imageurl"){
			$map_facetly .= '<label>'.$this->l($label_config).'</label>';
			$map_facetly .= '<select name="'.$config_name.'">';
			if($config_value == "home") $select_home = 'selected="selected"';
			else if($config_value == "small") $select_small = 'selected="selected"';
			else if($config_value == "medium") $select_medium = 'selected="selected"';
			else if($config_value == "large") $select_large = 'selected="selected"';
			else if($config_value == "thickbox") $select_thickbox = 'selected="selected"';
			
			$map_facetly .= '<option value= "">-----</option>';
			$map_facetly .= '<option value= "home" '.$select_home.'>Home</option>';
			$map_facetly .= '<option value= "small" '.$select_small.'>Small</option>';
			$map_facetly .= '<option value= "medium" '.$select_medium.'>Medium</option>';
			$map_facetly .= '<option value= "large" '.$select_large.'>Large</option>';
			$map_facetly .= '<option value= "thickbox" '.$select_thickbox.'>Thickbox</option>';
			$map_facetly .= '</select><br /><br />';
		}
		
		
		
	}
	
	$this->_html .= '
	<fieldset>
	<h2>Facetly Field</h2>
	<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		'.$map_facetly.'		
		<input type="submit" name="submitField" value="'.$this->l('Update Field').'" class="button" />	
	</form>
	</fieldset>
	';
	
	
	$facetly_search_template = facetly_configuration_decode(Configuration::get('facetly_search_template'));
	$facetly_facet_template = facetly_configuration_decode(Configuration::get('facetly_facet_template'));  
	  
	$this->_html .= '
		<fieldset>
		<h2>Template Configuration</h2>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">									
			<label>'.$this->l('Search Template').'</label>
			<div class="margin-form">
				<textarea name="facetly_search_template" rows="20" cols="90">'.$facetly_search_template.'</textarea>
			</div>
			
			<label>'.$this->l('Facet Template').'</label>
			<div class="margin-form">
				<textarea name="facetly_facet_template" rows="20" cols="90">'.$facetly_facet_template.'</textarea>
			</div>
			
			<input type="submit" name="submitTemplate" value="'.$this->l('Update Template').'" class="button" />
		</form>
		</fieldset>
	  ';
	
	$this->_html .= '
		<fieldset>
		<h2>Reindex</h2>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">			
			<label>'.$this->l('Reindex').'</label>		
			<input type="submit" name="submitReindex" value="'.$this->l('Reindex').'" class="button" />
		</form>
		</fieldset>
	  ';

}  
else{
	$this->_html .= '
		<fieldset>
		<h4>
			Cannot connect to server. Please check your API configuration or contact our customer support if problem persist.
		</h4>
		</fieldset>
	  ';

	}
	  
	  
	  
	  
	  
	  
}
  
  
  
public function hookLeftColumn(){		
	

	global $facetly_output;
	$facetly_block = '			
			<div class="block"><h4>Search</h4>
				<div class="block_content">
					<form action="'.__PS_BASE_URI__.'modules/facetly/find.php" facetly_form="on" method="get">
						<input id="edit-query" type="text" facetly="on" name ="query" size ="15" value="'.$_GET['query'].'" />	
						<input type="submit" value="Search" class="button" />
						<input id="edit-limit" type="hidden" value="'.Configuration::get('facetly_search_limit').'" name="limit">
					</form>
				</div>	
			</div>
			<div class="block"><h4>Filter Results</h4>
				<div class="block_content">
					<div id="facetly_facet">
						'.$facetly_output->facets.'
					</div>					
				</div>
			</div>
			';
	return $facetly_block;
}
  
public function search($params = array()){  
	require_once("facetly_api.php");
	$query = $params['query'];
	if($query == NULL) $query = $_GET['query'];
	$facetly = new facetly_api;
	$api_server = Configuration::get('facetly_server_name');
	$api_path = "search/product";
	$api_method = "GET";
	$consumer_key = Configuration::get('facetly_consumer_key');
	$add_var = Configuration::get('facetly_additional_variable');
	$uri = __PS_BASE_URI__."modules/facetly/find.php?".$add_var;
		
	$api_data = array(
	  "key" => $consumer_key,
	  "limit" => $_GET['limit'],
	  "searchtype" => 'html',
	  "baseurl" => $uri,
	  "query" => $query,
	);
	$api_data = array_merge($_GET,$api_data);
	$facetly->setServer($api_server);
	$api_output = $facetly->call($api_path, $api_data, $api_method);
	$facetly_output = json_decode($api_output);
	return $facetly_output;
	
}

public function hookHeader(){
	Tools::addCSS(facetly_base_uri().'modules/facetly/css/autocomplete.css', 'all');
	Tools::addCSS(facetly_base_uri().'modules/facetly/css/facetly.css', 'all');
Tools::addJS(
					array(
						facetly_base_uri().'modules/facetly/config.js.php',
						facetly_base_uri().'modules/facetly/js/jquery.autocomplete.js', 
						facetly_base_uri().'modules/facetly/js/jquery.address.js',
						facetly_base_uri().'modules/facetly/js/facetly.js',
						facetly_base_uri().'modules/facetly/js/jquery-ui.custom.js',
					)
				);
}

public function hookdeleteproduct($params){
	print_r($params);
	$id_product = (int)$params['product']->id;

	facetly_del_temp($id_product);

}

public function hookupdateproduct($params){
	$id_product = (int)$params['product']->id;
	$product = (array)$params['product'];
	
	if($product['active']==1){
		facetly_add_temp($id_product);
	}
	else if($product['active']==0){
		facetly_del_temp($id_product);
	}
}

public function hookaddproduct($params){
	require_once("facetly_api.php");
	// We check that the product identification is present
	if (!isset($params['product']->id))
		return false;
 
	// And we check that this one is also valid
	$id_product = (int)$params['product']->id;
	if ((int)$id_product < 1)
		return false;
 
	$product = (array)$params['product'];
	
	if($product['active']==1){
		facetly_add_temp($id_product);
	}
	return false;
}
}
