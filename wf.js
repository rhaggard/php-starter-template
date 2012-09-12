
//button 1 clicked
$('a#btn1').click(function(e){
	//cart sends message to shipper

	$.post('cart_to_shipper', {"json_message" : $('#ta1').text()}, 
		function(data) {
  			$('#pre1').html(data);
  			$('#step2_nav').show();
  			$('#btn1').hide();
		}
	);
	e.preventDefault();
});

$('a#step2_nav').click(function(e){
	$('#step1').hide();
	$('#step2').show();
});

$('a#btn2').click(function(e){
	//cart sends message to shipper

	$.post('shipper_order', {"json_message" : $('#ta2').text()}, 
		function(data) {
  			$('#pre2').html(data);
  			$('#step3_nav').show();
  			$('#btn2').hide();
		}
	);
	e.preventDefault();
});
$('a#step3_nav').click(function(e){
	$('#step2').hide();
	$('#step3').show();
});
$('a#btn3').click(function(e){
	//cart sends message to shipper

	$.post('shipper_to_cart', {"json_message" : $('#ta3').text()}, 
		function(data) {
  			$('#pre3').html(data);
  			$('#donebutton').show();
  			$('#btn3').hide();
		}
	);
	e.preventDefault();
});

$('a#donebutton').click(function(e){
	
});