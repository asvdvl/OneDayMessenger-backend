<?php
#header('Content-Type: application/json');
#переменныне
$received_data = [
'imei' => ''
];
$send_data_array = [ 
'error' => 0,
'new_profile' => false,
'user_uid' => '',
'user_nickname' => '',
'URL_image' => ''
];

#получение данных от клиента
if($_SERVER['REQUEST_METHOD'] == "GET")
{
	if (isset($_GET['imei']))
	{
		$received_data['imei'] = $_GET['imei'];
		$send_data_array['user_uid'] = $received_data['user_uid'] = sha1($received_data['imei']);
	}
	else
	{
		$send_data_array['error'] = 1;
	}
}
else
{
	$send_data_array['error'] = 2;
}

#подкл. к бд
$bd_link = mysqli_connect("localhost", "messenger_backend_worker", "B9Z1VPNuvljoGTcm", "messenger_db");
if (!isset($bd_link) || $bd_link == false)
	{
		$send_data_array['error'] = 100;
	}

#поиск 
$sql_query = "SELECT `user_id`, `user_uid`, `user_nickname`, `URL_image` FROM `profile_users_data` WHERE `user_uid` = ".$received_data['user_uid'];
echo($sql_query);
$result = mysqli_query($bd_link, $sql_query);
if($result)
{
	while($row = mysqli_fetch_array($result)) {
    $rows[] = $row;
	}

	print_r($rows);
	#var_dump(mysqli_fetch_array($result, MYSQLI_ASSOC));
}
else
{
	$send_data_array['new_profile'] = true;

	#добавление клиента
}


#обнуляем отправляемые данные клиенту в случае ошибки при обработке запроса. (просто является копией значений по умолчанию)
/*
if($send_data_array['error'] != 0)
{
	$send_data_array['new_profile'] = false;
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



/*							мини документация

	требуемые данные от клиента
	imei - imei устройства
	!!!	данные отдаем серверу через GET запрос. 

	отправляемые данные клиенту
	error 			[int]		- сообщаем клиенту статус "работы бекэнда". 0 - все хорошо. остальные коды описаны в следующем блоке. WARNING!!! коды могут менятся/добавлятся/удалятся к каждой новой верии этого документа WARNING!!!
	new_profile		[bool]		- имеет значение true если профиль только создан и нуждается в редактировании.
	user_uid		[int]		- пользовательсний id. также используется для аунтификации при большинстве запросов
	user_nickname	[string]	- пользовательский никнейм
	URL_image		[string]	- ссылка на картинку аватара пользователя

	коды ошибок
	0 		-- все ок
	##	1-99 ошибки при приеме данных
	1 		-- отсутствуют требуемые параметры необходимые для обработки.
	2		-- неправильно отдан запрос. проверьте какой метод отправки вы используете.
	##	100-199 ошибки при работе с бд.
	100 	-- бд не доступно или произошла ошибка при аунтификации.


*/
?>
