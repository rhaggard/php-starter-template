//var updated
$('a#resettokens').on('click', function(e){
	$.post('reset', {}, function(data) {
		d = $.parseJSON(data);
		$('#ctoken').attr("placeholder",d["ctoken"] );
		$('#stoken').attr("placeholder",d["stoken"]);
		$('#stoken').val("");
		$('#ctoken').val("");
		$('label.carttoken').text('cart token: '+ d["ctoken"] );
		$('label.shiptoken').text('ship token: '+ d["stoken"] );
		$('#myModal').modal('hide');
	}
	);
});

$('#modal-form-submit').on('click', function(e){
    // We don't want this to act as a link so cancel the link action

    $.post('update', {"cart_token" : $('#ctoken').val(), "ship_token" : $('#stoken').val() }, 
		function(data) {
			console.log(data);
			d = $.parseJSON(data);
			$('#ctoken').attr("placeholder",d["ctoken"] );
			$('#stoken').attr("placeholder",d["stoken"]);
			$('label.carttoken').text('cart token: '+ d["ctoken"] );
			$('label.shiptoken').text('ship token: '+ d["stoken"] );
			$('#stoken').val("");
			$('#ctoken').val("");
  			$('#myModal').modal('hide');
		}
	);
    e.preventDefault();

    // Find form and submit it
    
  });

//button 1 clicked
$('a#btn1').click(function(e){
	//cart sends message to shipper

	$.post('cart_order', {"json_message" : $('#ta1').val()}, 
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

	$.post('shipper_order', {"json_message" : $('#ta2').val()}, 
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

	$.post('shipper_to_cart', {"json_message" : $('#ta3').val()}, 
		function(data) {
  			$('#pre3').html(data);
  			$('#donebutton').show();
  			$('#btn3').hide();
		}
	);
	e.preventDefault();
});

$('a#donebutton').click(function(e){
	$('#myModal2').modal("hide");
	$('#pre3').html("");
	$('#pre2').html("");
	$('#pre1').html("");
	$('#step1').show();
	$('#step3').hide();

});