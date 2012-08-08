<?php  
 
class facetly_api { 
    function setConsumer($key, $secret) {  
        $this->key = $key;
        $this->secret = $secret;  
    } 
    
    function setServer($server) {  
        $this->server = $server;  
    }
    function setBaseUrl($baseurl) {  
        $this->baseurl = $baseurl;  
    }
 	
 	function templateUpdate( $tplsearch, $tplfacet,$tplpage=''){
 		$key = $this->key;
 		$secret = $this->secret;
 		$data = array(
			"key" => $key,
			"secret" => $secret,
			"tplsearch" => $tplsearch,
			"tplfacet" => $tplfacet,
			"tplpage" => $tplpage,
		);
		//print_r($data);
		//exit();
		$path = "template/update";
		return $this->call($path, $data, 'POST');
 	}
 	
 	function templateSelect(){
 		$key = $this->key;
 		$secret = $this->secret;
 		$data = array(
			"key" => $key,
			"secret" => $secret,
		);
		$path = "template/select";
		return json_decode($this->call($path, $data, 'POST'));
 	}

 	function productDelete($id) {
		$key = $this->key;
 		$secret = $this->secret;
 		$data = array(
			"key" => $key,
			"secret" => $secret,
			"id" => $id
		);
		$path = "product/delete";
		return $this->call($path, $data, 'POST');
 	}
 	
 	function productTruncate() {
 		$key = $this->key;
 		$secret = $this->secret;
 		$data = array(
			"key" => $key,
			"secret" => $secret,
		);
		$path = "product/truncate";
		return $this->call($path, $data, 'POST');
 	}
 	
	function productInsert($items) {
		$key = $this->key;
 		$secret = $this->secret;
		$data = array(
			"key" => $key,
			"secret" => $secret,
		);
		$data = array_merge($data, $items);						
		$path =  "product/insert";
		return $this->call($path, $data, 'POST');
	}
	
    function searchProduct($query, $filter, $searchtype) {
		$baseurl = $this->baseurl;
		$key = $this->key;
		$data = array(
				"key" => $key,
				"limit" => 3,
				"searchtype" => $searchtype,
				"baseurl" => $baseurl,
		);
	
		if (!empty($query)) {
		  $data['query'] = $query;
		}		
		$data = array_merge($data, $filter);
		$path =  "search/product";
		return json_decode($this->call($path, $data, 'GET'));
	}
	
	function searchHtml($query, $filter) {
		$baseurl = $this->baseurl;
		$key = $this->key;
		$data = array(
				"key" => $key,
				"limit" => 3,
				"baseurl" => $baseurl,
		);
	
		if (!empty($query)) {
		  $data['query'] = $query;
		}
		$data = array_merge($data, $filter);
		$path =  "search/html";
		return $this->call($path, $data, 'GET');
	}
	
	function searchAutoComplete($query) {
		$key = $this->key;
 		$secret = $this->secret;
		$data = array(
			"key" => $key,
			//"secret" => $secret,
			"query" => $query,
		);
		
		$path =  "search/autocomplete";
		return json_decode($this->call($path, $data, 'GET'));
	}
	
	function reportQuery($from, $to, $query = "") {
		$key = $this->key;
 		$secret = $this->secret;
		$data = array(
			"consumer_key" => $key,
			"consumer_secret" => $secret,
			"fromdate" => $from,
			"todate" => $to,
			"query" => "keywords_token:". $query,
		);
		$path =  "report/query";
		return json_decode($this->call($path, $data, 'POST'));
	}
	
	function reportTrend($from, $to, $query = "", $field = "keywords_token") {
		$key = $this->key;
 		$secret = $this->secret;
		$data = array(
			"consumer_key" => $key,
			"consumer_secret" => $secret,
			"fromdate" => $from,
			"todate" => $to,
			"query" => $query,
			"size" => 5,        // size of facets
			"field" => $field,  //selected field for facets
		);
		$path =  "report/trend";
		return json_decode($this->call($path, $data, 'POST'));
	}
	
	function reportStats(){
		$path =  "report/stats";
		return json_decode($this->call($path, "", 'POST'));
	}

    function call($path, $data, $method ){
    	if (!$this->server) throw new Exception('$this->server needs a value');     	    	
    	$data = http_build_query($data,'','&'); 
    	//replace multiple values [0]..[n] to [], thanks to http://www.php.net/manual/en/function.http-build-query.php#78603
    	$data = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '[]=', $data);
    	$path = $this->server. "/" . $path;				
		
		if( $method == 'POST' ){			
			$Curl_Session = curl_init($path);
			curl_setopt ($Curl_Session, CURLOPT_POST, 1); 
			curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, $data);
    	} else if ($method == 'GET') {    				
    		$Curl_Session = curl_init($path . '?' . $data);
    	}
		
		//curl_setopt ($Curl_Session, CURLOPT_HEADER, TRUE);
		curl_setopt($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($Curl_Session, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($Curl_Session, CURLOPT_ENCODING, 1);
		curl_setopt($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($Curl_Session, CURLOPT_HTTPHEADER, array("Accept-Encoding: gzip"));
		$output = curl_exec ($Curl_Session);
		//print_r($output); exit();
		//$header = curl_getinfo($Curl_Session);
		curl_close ($Curl_Session);
		
		//$var = json_decode($output);
		
		$this->output = $output;
		
		return $this->output;
    }
    
}  
  
// Create a new object  
//$facetly = new Facetly;  
//these settings is required
//$facetly->setConsumer("y37fdeti", "ebhqnbjrgwhqwgalhgymezpg3hq3aqsl");  
//$facetly->setConsumer("xwfb2pnq", "qhx2cgnucrb7temugrdvw2aelpmvxrd9");  
//$facetly->setServer("http://us2.beta.facetly.com/1");
//$facetly->setBaseUrl("/tes/facetly.php");
 
//$json = $facetly->searchHtml("acer",array());  
//print_r($json);
/*$items = array(
	'id' => '4986',
	'title' => 'tesdemo1',
	'body' => 'tesdemo1',
);*/
//print_r($facetly->productInsert($items));
//key=y37fdeti&secret=ebhqnbjrgwhqwgalhgymezpg3hq3aqsl&body=tesdemo1&brand=&created=1337935107000&id=4986&imageurl=http%3A%2F%2Fdemo1.beta.facetly.com%2Fsites%2Fdemo1.beta.facetly.com%2Ffiles%2Fimagecache%2Fproduct%2F&price=1&specification=&title=tesdemo1&url=http%3A%2F%2Fdemo1.beta.facetly.com%2Fnode%2F4986&category[]=Hardisk%3BHardisk+PC+Desktop&category_map[]=Computer%3BDrives+%26+Storage

//print_r(json_decode($json));
  
//$tplsearch = "";
//$tplfacet = "";
//print_r($facetly->templateUpdate($tplsearch, $tplfacet));
//print_r($facetly->productDelete('4986'));
//print_r($facetly->searchAutoComplete("bali"));

//$from = -30; //means 30 days ago
//$to = 0; // means today
//print_r($facetly->reportQuery($from,$to));
//print_r($facetly->reportTrend($from,$to));
//print_r($facetly->reportStats());

?>










  
