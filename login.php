<?php
header('Content-Type: application/json');
#переменныне
$send_data_array = [ 
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
		$regexp = "/^\d\d\d\d\d\d\d\d\d\d\d\d\d\d\d$/";
		$match = [];
		if (preg_match($regexp, $_GET['imei'], $match)) 
		{
			$send_data_array['user_uid'] = sha1($match[0]);
		}
		else
		{
			setError(3);
		}
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
	$sql_query = "SELECT `user_id`, `user_nickname`, `URL_avatarImage` FROM `profile_users_data` WHERE `user_uid` = '".$send_data_array['user_uid']."'";
	$result = mysqli_query($bd_link, $sql_query);
	$result_parsing = mysqli_fetch_array($result, MYSQLI_ASSOC);

	if($result_parsing)
	{
		$send_data_array['user_id'] = $result_parsing['user_id'];
		$send_data_array['user_nickname'] = $result_parsing['user_nickname'];
		$send_data_array['URL_avatarImage'] = $result_parsing['URL_avatarImage'];
	}
	else
	{
		$send_data_array['new_profile'] = true;
	}
}
catch (Exception $e)
{
	setError("101.".mysqli_connect_errno());
}
###добавление клиента
try
{
	if ($send_data_array['new_profile'] == true && $send_data_array['error'] == 0)
	{
		$max_id = mysqli_fetch_array(mysqli_query($bd_link, "SELECT MAX(user_id) FROM `profile_users_data`"), MYSQLI_ASSOC);
		$max_id = $max_id['MAX(user_id)']+1;	
		#
		$send_data_array['user_id'] = $max_id;
		$send_data_array['user_nickname'] = $defaultValuesForRegistraton['nickname'].$max_id;
		$send_data_array['URL_avatarImage'] = $defaultValuesForRegistraton['URL_avatarImage'];

		$sql_query = "INSERT INTO `profile_users_data` (`user_id`, `user_uid`, `user_nickname`, `URL_avatarImage`, `last_update`) VALUES (NULL, '".$send_data_array['user_uid']."', '".$send_data_array['user_nickname']."', '".$send_data_array['URL_avatarImage']."', CURRENT_TIME())";
		$result = mysqli_query($bd_link, $sql_query);
		if(!$result)
		{
			setError("103.".mysqli_connect_errno());
		}	
	}
}
catch (Exception $e)
{
	setError("102.".mysqli_connect_errno());
}

#обнуляем отправляемые данные клиенту в случае ошибки при обработке запроса. (просто является копией значений по умолчанию)
if($send_data_array['error'] != 0)
{
	$send_data_array['new_profile'] = false;
	$send_data_array['user_id'] = '';
	$send_data_array['user_uid'] = '';
	$send_data_array['user_nickname'] = '';
	$send_data_array['URL_avatarImage'] = '';
}

$ext = (string)json_encode($send_data_array);
echo $ext;
?>
