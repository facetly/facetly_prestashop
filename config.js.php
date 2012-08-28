<?php
	include('../../config/config.inc.php');
	$consumer_key = Configuration::get('facetly_consumer_key');
	$api_server = Configuration::get('facetly_server_name');
	$limit = Configuration::get('facetly_search_limit');
	$add_var = Configuration::get('facetly_additional_variable');
?>

					
var facetly = {
	"key" : "<?php echo $consumer_key ?>",
	"server" : "<?php echo $api_server ?>",
	"file" : "find.php?<?php echo $add_var ?>",
	"baseurl" : "<?php echo __PS_BASE_URI__ ?>modules/facetly/",
	"limit" : <?php echo $limit ?>
}
				
