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
F3::set('cart_token',"Bearer EmD5+rI5JWO0w4DEev8+HQMYOszwD6M8FoXG9J/50GBSE8LDeOCjOFqf4XwGKHmfS+y13vd4");
F3::set('cart_topic',"https://api.sandbox.x.com/fabric/"."com.x.example.v1/OrderFulfillment/OrderCreated");
F3::set('cart_uri', "http://api.x.com/ocl/com.x.example.v1/OrderFulfillment/OrderCreated/1.2.0/");
F3::set('cart_ver', "1.2.0");

F3::set('ship_token',"Bearer DTnGqOEPTffZyQ/byw/IwTedDIhtL+m3P+EsCI4br/dtNc5njBCx217Imh3QPGyBH2cFa5XE");
F3::set('ship_topic',"https://api.sandbox.x.com/fabric/"."com.x.example.v1/OrderShipment/ShipOrderSucceeded");
F3::set('ship_uri', "http://api.x.com/ocl/com.x.example.v1/OrderShipment/ShipOrderSucceeded/1.2.0/");
F3::set('ship_ver', "1.2.0");

//common stuff
F3::set('transaction_id', guid());
F3::set('wf_id', guid());
F3::set('sync_bridge_url',"https://api.sandbox.x.com/xbridge/retrieve");


F3::route('GET /',
	function() {
		echo Template::serve('welcome.htm');
	}
);

F3::route('GET /form',
	function() {
		echo Template::serve('form.html');
	}
);

F3::route('POST /cart_to_shipper',
	function() {

		$json_schema = file_get_contents(F3::get('cart_uri'));
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

		curl_setopt($ch, CURLOPT_URL, F3::get('cart_topic') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('cart_token')
		    	 ,"X-XC-MESSAGE-GUID-CONTINUATION: "
		    	 ,"X-XC-WORKFLOW-ID: "   . F3::get('wf_id')
		    	 ,"X-XC-TRANSACTION-ID: " . F3::get('transaction_id')
		       //  ,"X-XC-DESTINATION-ID: " . "destination id of the capability (not needed here)"
		         ,"X-XC-SCHEMA-VERSION: " . F3::get('cart_ver')));

		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string());
		$response = curl_exec($ch);
		echo $response;
	}
);

F3::route('POST /shipper_bridge',
	function() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, F3::get('sync_bridge_url') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('ship_token')
		    	 ,"X-XB-COUNT: " . "1"
		    	 ));

		curl_setopt($ch, CURLOPT_POSTFIELDS, "");
		$response = curl_exec($ch);
		echo $response;

		//handle the message like following.

		//list($headers, $content) = explode("\r\n\r\n", $response, 2);

		// $schema_uri = $headers['X-XC-SCHEMA-URI'];

		// // Deserialize the data in the Ping message's body
		// $content = file_get_contents($schema_uri);
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
		$json_schema = file_get_contents(F3::get('ship_uri'));
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

		curl_setopt($ch, CURLOPT_URL, F3::get('ship_topic') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('ship_token')
		    	 ,"X-XC-MESSAGE-GUID-CONTINUATION: "
		    	 ,"X-XC-WORKFLOW-ID: "   . F3::get('wf_id')
		    	 ,"X-XC-TRANSACTION-ID: " . F3::get('transaction_id')
		       //  ,"X-XC-DESTINATION-ID: " . "destination id of the capability (not needed here)"
		         ,"X-XC-SCHEMA-VERSION: " . F3::get('ship_ver')));

		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $write_io->string());
		$response = curl_exec($ch);
		echo $response;
	}
);

F3::route('POST /cart_bridge',
	function() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, F3::get('sync_bridge_url') );
		// pure evil! just for the sake of this example.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
		    array("Content-Type: avro/json"
		         ,"Authorization: ".F3::get('cart_token')
		    	 ,"X-XB-COUNT: " . "1"
		    	 ));

		curl_setopt($ch, CURLOPT_POSTFIELDS, "");
		$response = curl_exec($ch);
		echo $response;

		//handle the message like following.

		//list($headers, $content) = explode("\r\n\r\n", $response, 2);

		// $schema_uri = $headers['X-XC-SCHEMA-URI'];

		// // Deserialize the data in the Ping message's body
		// $content = file_get_contents($schema_uri);
		// $schema = AvroSchema::parse($content);
		// $datum_reader = new AvroIODatumReader($schema);
		// $read_io = new AvroStringIO($content);
		// $decoder = new AvroIOBinaryDecoder($read_io);
		// $message = $datum_reader->read($decoder);
		// echo $message;
	}
);

F3::route('POST /update', 
	function(){
		if(!empty($_POST["cart_token"])){
			F3::set('cart_token',$_POST["cart_token"]);
		}
		if(!empty($_POST["cart_topic"])){
			F3::set('cart_topic',$_POST["cart_topic"]);
		}
		if(!empty($_POST["cart_uri"])){
			F3::set('cart_uri',$_POST["cart_uri"]);
		}
		if(!empty($_POST["cart_ver"])){
			F3::set('cart_ver',$_POST["cart_ver"]);
		}

		if(!empty($_POST["ship_token"])){
			F3::set('ship_token',$_POST["ship_token"]);
		}
		if(!empty($_POST["ship_topic"])){
			F3::set('ship_topic',$_POST["ship_topic"]);
		}
		if(!empty($_POST["ship_uri"])){
			F3::set('ship_uri',$_POST["ship_uri"]);
		}
		if(!empty($_POST["cart_ver"])){
			F3::set('ship_ver',$_POST["ship_ver"]);
		}
		F3::reroute('/');

	}
);

F3::run();

?>