<?php

	require('stripe/Stripe.php');

		$params = array(
		"testmode"   => "on",

		"private_live_key" => "sk_live_9tYO0K6VEIPZvwhOeNDJ5xXU",
		"public_live_key"  => "pk_live_LFbZ5VCjRaUYSUBvq4MzKOeo",

		"private_test_key" => "sk_test_wspOD9ZI2AJzUamQTfAIYKyo",
		"public_test_key"  => "pk_test_vtGolww0ipzq1sy8xQ8WeN2B"
	);

    if ($params['testmode'] == "on") {
	    Stripe::setApiKey($params['private_test_key']);
	    $pubkey = $params['public_test_key'];
	} else {
	    Stripe::setApiKey($params['private_live_key']);
	    $pubkey = $params['public_live_key'];
  	}	

  	//if stripepayment method
  	if(isset($_POST['stripeToken']))
  	{

	    $description = "Production Purchase";
	    
    	try {

      		$charge = Stripe_Charge::create(
      					array(
      						"amount" => 1000,
                            "currency" => "usd",
                            "card" => $_POST['stripeToken']
                        )
                    );

      		if ($charge->card->address_zip_check == "fail") {
    			throw new Exception("zip_check_invalid");
	      	} else if ($charge->card->address_line1_check == "fail") {
	    	    throw new Exception("address_check_invalid");
		    } else if ($charge->card->cvc_check == "fail") {
	        	throw new Exception("cvc_check_invalid");
		    }      
		    // Payment has succeeded, no exceptions were thrown or otherwise caught

      		$result = "success";

      		
	    } catch(Stripe_CardError $e) {

	      $error = $e->getMessage();
	      $result = "declined";
	    } catch (Stripe_InvalidRequestError $e) {
	      $result = "declined-Stripe_InvalidRequestError";
	    } catch (Stripe_AuthenticationError $e) {
	      $result = "declined-Stripe_AuthenticationError";
	    } catch (Stripe_ApiConnectionError $e) {
	      $result = "declined-ApiConnectionError";
	    } catch (Stripe_Error $e) {
	      $result = "declined-Stripe_Error";
	    } catch (Exception $e) {

      		if ($e->getMessage() == "zip_check_invalid") {
	        	$result = "declined-zip_check_invalid";
	      	} else if ($e->getMessage() == "address_check_invalid") {
	        	$result = "declined-address_check_invalid";
	      	} else if ($e->getMessage() == "cvc_check_invalid") {
    			$result = "declined-cvc_check_invalid";
			} else {
    			$result = "declined";
  			}

      		$result = "declined";
  		}
  		echo "<BR>Stripe Payment Status : ".$result;
	}

?>

<html>
	<head>
		<title>Integraing Stripe PHP sdk example</title>		
	</head>

	<body>

		<form action="" method="POST" id="payment-form">
		  <span class="payment-errors"></span>

		  <div class="form-row">
		    <label>
		      <span>Card Number</span>
		      <input type="text" size="20" data-stripe="number">
		    </label>
		  </div>

		  <div class="form-row">
		    <label>
		      <span>Expiration (MM/YY)</span>
		      <input type="text" size="2" data-stripe="exp_month">
		    </label>
		    <span> / </span>
		    <input type="text" size="2" data-stripe="exp_year">
		  </div>

		  <div class="form-row">
		    <label>
		      <span>CVC</span>
		      <input type="text" size="4" data-stripe="cvc">
		    </label>
		  </div>

		  <div class="form-row">
		    <label>
		      <span>Billing ZIP Code</span>
		      <input type="text" size="6" data-stripe="address_zip">
		    </label>
		  </div>

		  <input type="submit" class="submit" value="Submit Payment">
		</form>

		<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

		<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

		<script type="text/javascript">
	        Stripe.setPublishableKey('<?php echo $pubkey; ?>');
		</script>

		<script>
			$(function() {
			  var $form = $('#payment-form');
			  $form.submit(function(event) {
			    // Disable the submit button to prevent repeated clicks:
			    $form.find('.submit').prop('disabled', true);

			    // Request a token from Stripe:
			    Stripe.card.createToken($form, stripeResponseHandler);

			    // Prevent the form from being submitted:
			    return false;
			  });
			});

			function stripeResponseHandler(status, response) {
			  // Grab the form:
			  var $form = $('#payment-form');

			  if (response.error) { // Problem!

			    // Show the errors on the form:
			    $form.find('.payment-errors').text(response.error.message);
			    $form.find('.submit').prop('disabled', false); // Re-enable submission

			  } else { // Token was created!

			    // Get the token ID:
			    var token = response.id;

			    // Insert the token ID into the form so it gets submitted to the server:
			    $form.append($('<input type="hidden" name="stripeToken">').val(token));

			    // Submit the form:
			    $form.get(0).submit();
			  }
			};

		</script>
	</body>

</html>