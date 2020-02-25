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

###получение данных от клиента
if($_SERVER['REQUEST_METHOD'] == "POST")
{
	if (isset($_POST['user_uid']))
	{
        if(strlen($_POST['user_uid']) == 40)
        {
            $received_data_from_client['user_uid'] = $_POST['user_uid'];
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


    if (isset($_POST['messadeBody']))
	{
        if(strlen($_POST['messadeBody']) <= 256)
        {
            $received_data_from_client['messadeBody'] = $_POST['messadeBody'];
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

/*
yes:parse post data 
 no:connect to bd 
 no:check valid user
 no:add message in db 
*/
 ?>
