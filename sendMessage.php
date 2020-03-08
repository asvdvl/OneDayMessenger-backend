<?php
header('Content-Type: application/json');
$received_data_from_client = [
    'user_id' => '',
    'user_uid' => '',
    'messageBody' => ''
    ];
        
$send_data_to_client = [
    'error' => '0',
    'APIVersion' => 'v0.1'
    ];    

function setError($errorCode) {
    global $send_data_to_client;
    if ($send_data_to_client['error'] == "0") {
        $send_data_to_client['error'] = (string)$errorCode;
    }
}

###получение данных от клиента
if($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isset($_POST['user_uid'])) {
        if(strlen($_POST['user_uid']) == 40) {
            $received_data_from_client['user_uid'] = $_POST['user_uid'];
        }
        else {
            setError(3);
        }
	}
	else {
		setError(1);
    }


    if (isset($_POST['messageBody'])) {
        if(strlen($_POST['messageBody']) <= 256) {
            $received_data_from_client['messageBody'] = $_POST['messageBody'];
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
	$sql_query_find_client = "SELECT `user_id` FROM `profile_users_data` WHERE `user_uid` = '".$received_data_from_client['user_uid']."'";
	$result_find_client = mysqli_query($bd_link, $sql_query_find_client);
	$result_parsing_add_message = mysqli_fetch_array($result_find_client, MYSQLI_ASSOC);

	if($result_parsing_add_message) {
		$received_data_from_client['user_id'] = $result_parsing_add_message['user_id'];
	}
	else {
        setError(201);
	}
}
catch (Exception $e) {
	setError("101.".mysqli_connect_errno());
}

###Добавление сообщения 
if ($received_data_from_client['error'] == 0) {
    try {
	    $sql_query_add_message = "INSERT INTO `messages_data` (`message_id`, `user_id`, `time`, `message_text`) VALUES (NULL, '".$received_data_from_client['user_id']."', current_timestamp(), '".$received_data_from_client['messadeBody']."')";
	    $result_add_message = mysqli_query($bd_link, $sql_query_add_message);
    
	    if(!$result_add_message) {
		    setError(104);
	    }
    }
    catch (Exception $e) {
	    setError("105.".mysqli_connect_errno());
    }
}

$ext = (string)json_encode($send_data_to_client);
echo $ext;

mysqli_free_result($result_get_messages);
mysqli_free_result($result_find_client);
mysqli_close($bd_link);
 ?>
