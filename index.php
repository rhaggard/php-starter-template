<?php

require __DIR__.'/lib/base.php';
require __DIR__.'/avro.php';
require __DIR__.'/guid_gen.php';

//F3::set('CACHE',TRUE);
F3::set('DEBUG',1);
F3::set('UI','ui/');

//set initial variables for a demo application
//this can be done through session variables too instead of changing the global scope
//changing the global scope is intentional for this demo app

F3::set('SESSION.cart_topic',"https://api.sandbox.x.com/fabric/"."com.x.example.v1/QuickStartSample/OrderCreated");
F3::set('SESSION.cart_uri', "http://api.x.com/ocl/com.x.example.v1/QuickStartSample/OrderCreated/1.2.4/");
F3::set('SESSION.cart_ver', "1.2.4");


F3::set('SESSION.ship_order_topic',"https://api.sandbox.x.com/fabric/"."com.x.example.v1/QuickStartSample/ShipOrder");
F3::set('SESSION.ship_order_uri', "http://api.x.com/ocl/com.x.example.v1/QuickStartSample/ShipOrder/1.2.2/");
F3::set('SESSION.ship_order_ver', "1.2.4");


F3::set('SESSION.ship_topic',"https://api.sandbox.x.com/fabric/"."com.x.example.v1/QuickStartSample/ShipOrderSucceeded");
F3::set('SESSION.ship_uri', "http://api.x.com/ocl/com.x.example.v1/QuickStartSample/ShipOrderSucceeded/1.2.0/");
F3::set('SESSION.ship_ver', "1.2.4");

//common stuff
F3::set('transaction_id', guid());
F3::set('wf_id', guid());

F3::route('GET /',
	function() {

		if(!isset($_SESSION['cart_token'])){
			F3::set('SESSION.cart_token',"Bearer EmD5+rI5JWO0w4DEev8+HQMYOszwD6M8FoXG9J/50GBSE8LDeOCjOFqf4XwGKHmfS+y13vd4");
			F3::set('SESSION.ship_order_token',"Bearer EmD5+rI5JWO0w4DEev8+HQMYOszwD6M8FoXG9J/50GBSE8LDeOCjOFqf4XwGKHmfS+y13vd4");
		}
		if(!isset($_SESSION['ship_token'])){
			F3::set('SESSION.ship_token',"Bearer DTnGqOEPTffZyQ/byw/IwTedDIhtL+m3P+EsCI4br/dtNc5njBCx217Imh3QPGyBH2cFa5XE");
		}
		
		echo Template::serve('welcome.htm');
	}
);



F3::route('GET /test',
	function() {
		echo "Hello world";
	}

);

F3::route('GET com.x.example.v1/QuickStartSample/OrderCreated',
	//handle your business logic for order created
	function() {
		echo "com.x.example.v1/QuickStartSample/OrderCreated";
	}

);

F3::route('GET com.x.example.v1/QuickStartSample/ShipOrder',
	//handle your business logic for shipOrder
	function() {
		echo "/com.x.example.v1/QuickStartSample/ShipOrder";
	}

);

F3::route('GET com.x.example.v1/QuickStartSample/ShipOrderSucceeded',
	//handle your business logic for shipOrderSucceeded
	function() {
		echo "com.x.example.v1/QuickStartSample/ShipOrderSucceeded";
	}

);


F3::route('POST /cart_order',
	function() {
		
		$json_schema = Web::http('GET '.F3::get('SESSION.cart_uri'));
		error_log($json_schema);
		$avro_schema  = AvroSchema::parse($json_schema);
		$datum_writer = new AvroIODatumWriter($avro_schema);
		$write_io     = new AvroStringIO();
		$encoder      = new AvroIOBinaryEncoder($write_io);

		$ch = curl_init();
		
		//get around php magic quotes if needed
		//for json_decode to work
		if(get_magic_quotes_gpc()){
		  $d = stripslashes($_POST['json_message']);
		}else{
		  $d = $_POST['json_message'];
		}
		$message = json_decode($d, true);

		// $message = array("order" => array("orderID" => "123", "orderType" => "InHouse"));
		$datum_writer->write($message, $encoder);

		curl_setopt($ch, CURLOPT_URL, F3::get('SESSION.cart_topic') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('SESSION.cart_token')
		    	 ,"X-XC-MESSAGE-GUID-CONTINUATION: "
		    	 ,"X-XC-WORKFLOW-ID: "   . F3::get('wf_id')
		    	 ,"X-XC-TRANSACTION-ID: " . F3::get('transaction_id')
		       //  ,"X-XC-DESTINATION-ID: " . "destination id of the capability (not needed here)"
		         ,"X-XC-SCHEMA-VERSION: " . F3::get('SESSION.cart_ver')));

		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string());
		$response = curl_exec($ch);
		echo $response;
	}
);

F3::route('POST /shipper_order',
	function() {
		$json_schema = Web::http('GET '.F3::get('SESSION.ship_order_uri'));
		$avro_schema  = AvroSchema::parse($json_schema);
		$datum_writer = new AvroIODatumWriter($avro_schema);
		$write_io     = new AvroStringIO();
		$encoder      = new AvroIOBinaryEncoder($write_io);

		$ch = curl_init();
		
		//get around php magic quotes if needed
		//for json_decode to work
		if(get_magic_quotes_gpc()){
		  $d = stripslashes($_POST['json_message']);
		}else{
		  $d = $_POST['json_message'];
		}
		$message = json_decode($d, true);
		error_log(print_r($message,true));
		//$message = array("order" => array("orderID" => "123", "orderType" => "InHouse"));
		$datum_writer->write($message, $encoder);

		curl_setopt($ch, CURLOPT_URL, F3::get('SESSION.ship_order_topic') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('SESSION.ship_order_token')
		    	 ,"X-XC-MESSAGE-GUID-CONTINUATION: "
		    	 ,"X-XC-WORKFLOW-ID: "   . F3::get('wf_id')
		    	 ,"X-XC-TRANSACTION-ID: " . F3::get('transaction_id')
		       //  ,"X-XC-DESTINATION-ID: " . "destination id of the capability (not needed here)"
		         ,"X-XC-SCHEMA-VERSION: " . F3::get('SESSION.ship_order_ver')));

		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string());
		$response = curl_exec($ch);
		echo $response;

		//handle the message like following.

		// list($headers, $content) = explode("\r\n\r\n", $response, 2);

		//  $schema_uri = F3::get('host_uri')."/sync_schema";

		// // // Deserialize the data in the Ping message's body
		// $content = Web::http('GET '.$schema_uri);
		// $schema = AvroSchema::parse($content);
		// $datum_reader = new AvroIODatumReader($schema);
		// $read_io = new AvroStringIO($content);
		// $decoder = new AvroIOBinaryDecoder($read_io);
		// $message = $datum_reader->read($decoder);
		// echo $message;
	}
);

F3::route('POST /shipper_to_cart',
	function() {
		$json_schema = Web::http('GET '.F3::get('SESSION.ship_uri'));
		$avro_schema  = AvroSchema::parse($json_schema);
		$datum_writer = new AvroIODatumWriter($avro_schema);
		$write_io     = new AvroStringIO();
		$encoder      = new AvroIOBinaryEncoder($write_io);

		$ch = curl_init();
		
		//get around php magic quotes if needed
		//for json_decode to work
		if(get_magic_quotes_gpc()){
		  $d = stripslashes($_POST['json_message']);
		}else{
		  $d = $_POST['json_message'];
		}
		$message = json_decode($d, true);
		
		//$message = array("order" => array("orderID" => "123", "orderType" => "InHouse"));
		$datum_writer->write($message, $encoder);

		curl_setopt($ch, CURLOPT_URL, F3::get('SESSION.ship_topic') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('SESSION.ship_token')
		    	 ,"X-XC-MESSAGE-GUID-CONTINUATION: "
		    	 ,"X-XC-WORKFLOW-ID: "   . F3::get('wf_id')
		    	 ,"X-XC-TRANSACTION-ID: " . F3::get('transaction_id')
		       //  ,"X-XC-DESTINATION-ID: " . "destination id of the capability (not needed here)"
		         ,"X-XC-SCHEMA-VERSION: " . F3::get('SESSION.ship_ver')));

		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string());
		$response = curl_exec($ch);
		echo $response;
	}
);

F3::route('POST /reset',
	function(){
		F3::set('SESSION.cart_token',"Bearer EmD5+rI5JWO0w4DEev8+HQMYOszwD6M8FoXG9J/50GBSE8LDeOCjOFqf4XwGKHmfS+y13vd4");
		F3::set('SESSION.ship_order_token',"Bearer EmD5+rI5JWO0w4DEev8+HQMYOszwD6M8FoXG9J/50GBSE8LDeOCjOFqf4XwGKHmfS+y13vd4");
		F3::set('SESSION.ship_token',"Bearer DTnGqOEPTffZyQ/byw/IwTedDIhtL+m3P+EsCI4br/dtNc5njBCx217Imh3QPGyBH2cFa5XE");
		$return_array = array(
			"ctoken" => F3::get('SESSION.cart_token'),
			"stoken" => F3::get('SESSION.ship_token')
			);
		echo json_encode($return_array);
	}
);

F3::route('POST /update', 
	function(){

		if(!empty($_POST["cart_token"])){
			F3::set('SESSION.cart_token',$_POST["cart_token"]);
			error_log(F3::get('SESSION.cart_token')); 
		}
		if(!empty($_POST["cart_token"])){
			F3::set('SESSION.ship_order_token',$_POST["cart_token"]);
		}
		if(!empty($_POST["ship_token"])){
			F3::set('SESSION.ship_token',$_POST["ship_token"]);
		}
		error_log(print_r($_POST,true));
		// F3::reroute('/');
		$return_array = array(
			"ctoken" => F3::get('SESSION.cart_token'),
			"stoken" => F3::get('SESSION.ship_token')
			);
		echo json_encode($return_array);
	}
);

F3::run();

?>
