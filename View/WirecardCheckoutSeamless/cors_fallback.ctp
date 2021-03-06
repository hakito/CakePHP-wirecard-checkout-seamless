<?php
  //--------------------------------------------------------------------------------//
  //                                                                                //
  // Wirecard Checkout Seamless Example                                             //
  //                                                                                //
  // Copyright (c)                                                                  //
  // Wirecard Central Eastern Europe GmbH                                           //
  // www.wirecard.at                                                                //
  //                                                                                //
  // THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY         //
  // KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE            //
  // IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A                     //
  // PARTICULAR PURPOSE.                                                            //
  //                                                                                //
  //--------------------------------------------------------------------------------//
  // THIS EXAMPLE IS FOR DEMONSTRATION PURPOSES ONLY!                               //
  //--------------------------------------------------------------------------------//
  // Please read the integration documentation before modifying this file.          //
  //--------------------------------------------------------------------------------//

	$response = isset($_POST['response']) ? $_POST['response'] : '';

  // Workaround for demo mode which already quotes the response string
    if (substr($response, 1, 1) != '\\')
      $response = addslashes($response);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <script type="text/javascript">
      function setResponse(response) {
        if (typeof parent.WirecardCEE_Fallback_Request_Object == 'object') {
          try {
            parent.WirecardCEE_Fallback_Request_Object.setResponseText(response);
          } catch (err)
          {
            var json = {
              error: [
                { errorCode: -1,
                  message: "CORS fallback failed: " + err,
                  consumerMessage: "An unexpected CORS error occurred."
                }
              ],
              errors: 1
            };
            parent.WirecardCEE_Fallback_Request_Object.setResponseText(JSON.stringify(json));
          }
        }
        else {
          console.log('Not a valid fallback call.');
        }
      }
    </script>
  </head>
  <body onload='setResponse("<?php echo $response; ?>");'>
  </body>
</html>
