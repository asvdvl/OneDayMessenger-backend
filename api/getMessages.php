<?php
header('Content-Type: application/json');
$received_data_from_client = [
    'user_id' => '',
    'user_uid' => '',
    'limit' => 50,
    'get_type' => '',
    'request_message_id' => 0
    ];
        
$send_data_to_client = [
    'error' => '0',
    'messages' => [],
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

    if (isset($_POST['limit'])) {
        if($_POST['limit'] <= 250) {
            $received_data_from_client['limit'] = (int)$_POST['limit'];
        }
        else {
            setError(3);
        }
    }
    
    if (isset($_POST['before']) || isset($_POST['after'])) {
        if (isset($_POST['before'])) {
            $received_data_from_client['get_type'] = "before";
            $received_data_from_client['request_message_id'] = (int)$_POST['before'];
            $sql_query_get_messages = "SELECT * FROM (
                SELECT 
                    pud.user_id,
                    pud.user_nickname AS nickname,
                    pud.URL_avatarImage,
                    md.message_id AS mes_id,
                    md.message_text AS mes_text,
                    md.time AS mes_time
                FROM 
                    profile_users_data AS pud 
                    inner JOIN messages_data AS md 
                        ON pud.user_id=md.user_id
                WHERE
                    md.message_id < ".$received_data_from_client['request_message_id']."
                ORDER BY mes_id DESC
                LIMIT ".$received_data_from_client['limit'].") as T1
                ORDER BY T1.mes_id ASC;";
        }
        elseif (isset($_POST['after'])) {
            $received_data_from_client['get_type'] = "after";
            $received_data_from_client['request_message_id'] = (int)$_POST['after'];

            $sql_query_get_messages = "SELECT * FROM (
                SELECT 
                    pud.user_id,
                    pud.user_nickname AS nickname,
                    pud.URL_avatarImage,
                    md.message_id AS mes_id,
                    md.message_text AS mes_text,
                    md.time AS mes_time
                FROM 
                    profile_users_data AS pud 
                    inner JOIN messages_data AS md 
                        ON pud.user_id=md.user_id
                WHERE
                    md.message_id > ".$received_data_from_client['request_message_id']."
                ORDER BY mes_id ASC
                LIMIT ".$received_data_from_client['limit'].") as T1
                ORDER BY T1.mes_id ASC;";
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
    if($bd_link == false)
	{
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
	$result_parsing = mysqli_fetch_array($result_find_client, MYSQLI_ASSOC);

	if(!$result_parsing) {
		setError(201);
	}
}
catch (Exception $e) {
	setError("101.".mysqli_connect_errno());
}

###выборка сообщений
if ($send_data_to_client['error'] == "0") {
    try {
        $result_get_messages = mysqli_query($bd_link, $sql_query_get_messages);

        while($row = mysqli_fetch_array($result_get_messages, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }
    
        $send_data_to_client['messages'] = $rows;
    }
    catch (Exception $e) {
        setError("101.".mysqli_connect_errno());
    }
} 

$ext = (string)json_encode($send_data_to_client);
echo $ext;

mysqli_free_result($result_get_messages);
mysqli_free_result($result_find_client);
mysqli_close($bd_link);
?>
