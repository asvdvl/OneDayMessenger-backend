<?php
header('Content-Type: application/json');
#переменныне
$allowRegistration = False;

$send_data_to_client = [ 
'error' => "0",
'new_profile' => false,
'user_id' => '',
'user_uid' => '',
'user_nickname' => '',
'URL_avatarImage' => '',
'APIVersion' => 'v0.1'
];

$defaultValuesForRegistraton = [
'nickname' => 'Anon_',
'URL_avatarImage' => 'https://example.com/imageDefault.PNG'
];

#функция не позволяющая перезаписать повторно номер ошибки, нужна для предотвращения 
function setError($errorCode) {
	global $send_data_to_client;
	if ($send_data_to_client['error'] == "0") {
		$send_data_to_client['error'] = (string)$errorCode;
	}
}


###получение данных от клиента
if($_SERVER['REQUEST_METHOD'] == "GET") {
	if (isset($_GET['imei'])) {
		$regexp = "/^\d\d\d\d\d\d\d\d\d\d\d\d\d\d\d$/";
		$match = [];
		if (preg_match($regexp, $_GET['imei'], $match)) {
			$send_data_to_client['user_uid'] = sha1($match[0]);
		}
		else {
			setError(3);
		}
	}
	else {
		setError(1);
	}
}
else {
	setError(2);
}

###подкл. к бд
try {
	$bd_link = mysqli_connect("localhost", "messenger_backend_worker", "B9Z1VPNuvljoGTcm", "messenger_db");
	if($bd_link == false) {
		setError("100.".mysqli_connect_errno());
	}
}
catch (Exception $e) {
	setError("100.".mysqli_connect_errno());
}

###поиск 
try {
	$sql_query_find_client = "SELECT `user_id`, `user_nickname`, `URL_avatarImage` FROM `profile_users_data` WHERE `user_uid` = '".$send_data_to_client['user_uid']."'";
	$result_find_client = mysqli_query($bd_link, $sql_query_find_client);
	$result_parsing_find_client = mysqli_fetch_array($result_find_client, MYSQLI_ASSOC);

	if($result_parsing_find_client)	{
		$send_data_to_client['user_id'] = $result_parsing_find_client['user_id'];
		$send_data_to_client['user_nickname'] = $result_parsing_find_client['user_nickname'];
		$send_data_to_client['URL_avatarImage'] = $result_parsing_find_client['URL_avatarImage'];
	}
	else {
		#запрет регистрации
		if ($allowRegistration) {
			$send_data_to_client['new_profile'] = true;
		} 
		else {
			setError(200);
		}
	}
}
catch (Exception $e) {
	setError("101.".mysqli_connect_errno());
}
###добавление клиента
try {
	if ($send_data_to_client['new_profile'] == true && $send_data_to_client['error'] == 0) {
		$max_id = mysqli_fetch_array(mysqli_query($bd_link, "SELECT MAX(user_id) FROM `profile_users_data`"), MYSQLI_ASSOC);
		$max_id = $max_id['MAX(user_id)']+1;	
		
		$send_data_to_client['user_id'] = $max_id;
		$send_data_to_client['user_nickname'] = $defaultValuesForRegistraton['nickname'].$max_id;
		$send_data_to_client['URL_avatarImage'] = $defaultValuesForRegistraton['URL_avatarImage'];

		$sql_query_add_client = "INSERT INTO `profile_users_data` (`user_id`, `user_uid`, `user_nickname`, `URL_avatarImage`, `last_update`) VALUES (NULL, '".$send_data_to_client['user_uid']."', '".$send_data_to_client['user_nickname']."', '".$send_data_to_client['URL_avatarImage']."', CURRENT_TIME())";
		$result_add_client = mysqli_query($bd_link, $sql_query_add_client);
		if(!$result_add_client)	{
			setError("103.".mysqli_connect_errno());
		}	
	}
}
catch (Exception $e) {
	setError("102.".mysqli_connect_errno());
}

#обнуляем отправляемые данные клиенту в случае ошибки при обработке запроса. (просто является копией значений по умолчанию)
if($send_data_to_client['error'] != 0) {
	$send_data_to_client['new_profile'] = false;
	$send_data_to_client['user_id'] = '';
	$send_data_to_client['user_uid'] = '';
	$send_data_to_client['user_nickname'] = '';
	$send_data_to_client['URL_avatarImage'] = '';
}

$ext = (string)json_encode($send_data_to_client);
echo $ext;

mysqli_free_result($result);
mysqli_close($link);
?>
