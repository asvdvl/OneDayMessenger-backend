<?php
#header('Content-Type: application/json');
#переменныне
$received_data_from_client = [];
$send_data_array = [ 
'error' => 0,
];

#подкл. к бд
$bd_link = mysqli_connect("localhost", "messenger_backend_worker", "B9Z1VPNuvljoGTcm");
if (!isset($bd_link) || $bd_link == false){$send_data_array['state'] = 100;}

#обработка





$ext = (string)json_encode($send_data_array);
echo $ext;






#$ext = json_decode($ext, true);
#var_dump($ext);

#	поле error - значения
# 	0 -- все ок
#	100 -- бд не доступно
#
#
#
?>