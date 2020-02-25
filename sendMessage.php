<?php
//header('Content-Type: application/json');
$received_data_from_client = [
    'user_uid' => '',
    'messadeBody' => ''
    ];
        
$send_data_to_client = [
    'error' => '0',
    'APIVersion' => 'v0.0'
    ];    

function setError($errorCode)
{
    global $send_data_to_client;
    if ($send_data_to_client['error'] == "0")
    {
        $send_data_to_client['error'] = (string)$errorCode;
    }
}


?>
