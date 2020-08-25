<?php
header('Content-Type: application/json');
$received_data_from_client = [
    'user_uid' => ''
    ];
        
$send_data_to_client = [
    'error' => '0',
    'IDOfLastMessage' => 0,
    'APIVersion' => 'v0.1'
    ];    

function setError($errorCode) {
    global $send_data_to_client;
    if ($send_data_to_client['error'] == "0") {
        $send_data_to_client['error'] = (string)$errorCode;
    }
}

###получение данных от клиента
if($_SERVER['REQUEST_METHOD'] == "GET") {
	if (isset($_GET['user_uid'])) {
        if(strlen($_GET['user_uid']) == 40) {
            $received_data_from_client['user_uid'] = $_GET['user_uid'];
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

###поиск клиента
try {
	$sql_query_find_client = "SELECT `user_id` FROM `profile_users_data` WHERE `user_uid` = '".$received_data_from_client['user_uid']."'";
	$result_find_client = mysqli_query($bd_link, $sql_query_find_client);
    
	if(!$result_find_client) {
		setError(201);
	}
}
catch (Exception $e) {
	setError("101.".mysqli_connect_errno());
}

###поиск id последнего сообщения
if ($send_data_to_client['error'] == "0") {
    try {
        $id_of_last_message = mysqli_fetch_array(mysqli_query($bd_link, "SELECT MAX(message_id) FROM `messages_data`"), MYSQLI_ASSOC);

	    if($id_of_last_message) {
	    	$send_data_to_client['IDOfLastMessage'] = $id_of_last_message['MAX(message_id)'];
	    }
	    else {
            setError("101.".mysqli_connect_errno());
        }
    }
    catch (Exception $e) {
        setError("101.".mysqli_connect_errno());
    }
}


$ext = (string)json_encode($send_data_to_client);
die($ext);
?>
