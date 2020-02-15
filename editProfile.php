<?php
//header('Content-Type: application/json');
$received_data_from_client = [
'newNickname' => '',
'editType' => 0,
'newAvatar' => '',
'user_uid' => '',
];
    
$send_data_to_client = [
'error' => '0',
'newNickname' => '',
'URL_avatarImage' => '',
'APIVersion' => 'v0.0'
];

$uploaddir = '/web/vservers/messenger.asvdev.com/htdocs/data/avatars/';

function setError($errorCode)
{
	global $send_data_to_client;
	if ($send_data_to_client['error'] == "0")
	{
		$send_data_to_client['error'] = (string)$errorCode;
	}
}

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    phpinfo(32);
    //get uid
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

    //get edit type
	if (isset($_POST['editType']))
	{
        $_POST['editType'] = (integer)$_POST['editType']; 
        if($_POST['editType'] > 0 && $_POST['editType'] <= 3)
        {
            $received_data_from_client['editType'] = $_POST['editType'];
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
    
    //get new nickname
    if (isset($_POST['newNickname']) 
        && ($received_data_from_client['editType'] == 1 
        || $received_data_from_client['editType'] == 3))
	{
        if(strlen($_POST['newNickname']) <= 64)
        {
            $received_data_from_client['newNickname'] = $_POST['newNickname'];
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

    //get new avatar image
    if (isset($_FILES['newAvatar'])
        && ($received_data_from_client['editType'] == 1 
        || $received_data_from_client['editType'] == 3))
    {
        
    }
    else
    {
        setError(1);
    }
   // && ($received_data_from_client['editType'] == 2 || $received_data_from_client['editType'] == 3))

}
else
{
	setError(2);
}


$ext = (string)json_encode($send_data_to_client);
echo $ext;

    //dock
    //edittype - int 0-3
    //0 - nothing
    //1 - only nickname
    //2 - only avatar
    //3 - all
/*
///////todo
yes: add in and out arrays
yes: add seterror function
 do: POST data in work array
 no: connect to db
 no: change nickname
 no: resize upload avatar
 no: upload avatar file
 no: errace all data if error
yes: send all data to client
*/
?>
