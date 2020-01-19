<?php
#header('Content-Type: application/json');
header('Content-Type: text/plain'); 
#переменныне
$received_data = [
'imei' => '',
'user_uid' => ''
];
$send_data_array = [ 
'error' => "0",
'new_profile' => false,
'user_id' => '',
'user_uid' => '',
'user_nickname' => '',
'URL_image' => ''
];

#функция не позволяющая перезаписать повторно номер ошибки, нужна для предотвращения 
function setError($errorCode)
{
	global $send_data_array;
	if ($send_data_array['error'] == "0")
	{
		$send_data_array['error'] = (string)$errorCode;
	}
}


###получение данных от клиента
if($_SERVER['REQUEST_METHOD'] == "GET")
{
	if (isset($_GET['imei']))
	{
		$received_data['imei'] = $_GET['imei'];
		$send_data_array['user_uid'] = $received_data['user_uid'] = sha1($received_data['imei']);
	}
	else
	{
		setError(1);
	}
}
else
{
	setError(2);
}

###подкл. к бд
try
{
$bd_link = mysqli_connect("localhost", "messenger_backend_worker", "B9Z1VPNuvljoGTcm", "messenger_db");
}
catch (Exception $e)
{
	setError("100.".mysqli_connect_errno());
}

###поиск 
try
{
	#$sql_query = "SELECT `user_id`, `user_uid`, `user_nickname`, `URL_image` FROM `profile_users_data` WHERE `user_uid` = '"."6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2"."'";	#work
	$sql_query = "SELECT `user_id`, `user_nickname`, `URL_image` FROM `profile_users_data` WHERE `user_uid` = '".$received_data['user_uid']."'";	#finaly work variant
	$result = mysqli_query($bd_link, $sql_query);
	$result_parsing = mysqli_fetch_array($result, MYSQLI_ASSOC);

	if($result_parsing)
	{

		$send_data_array['user_id'] = $result_parsing['user_id'];
		$send_data_array['user_nickname'] = $result_parsing['user_nickname'];
		$send_data_array['URL_image'] = $result_parsing['URL_image'];
		#var_dump($result_parsing);
	}
	else
	{
		$send_data_array['new_profile'] = true;
	
		#добавление клиента
	}
}
catch (Exception $e)
{
	setError("101.".mysqli_connect_errno());
}



#обнуляем отправляемые данные клиенту в случае ошибки при обработке запроса. (просто является копией значений по умолчанию)
/*
if($send_data_array['error'] != 0)
{
	$send_data_array['new_profile'] = false;
	$send_data_array['user_id'] = '';
	$send_data_array['user_uid'] = '';
	$send_data_array['user_nickname'] = '';
	$send_data_array['URL_image'] = '';
}
*/

$ext = (string)json_encode($send_data_array);
echo $ext;


#жсон декодер
#$ext = json_decode($ext, true);
#var_dump($ext);
?>
