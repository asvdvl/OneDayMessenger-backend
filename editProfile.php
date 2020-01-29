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
    
function setError($errorCode)
{
	global $send_data_to_client;
	if ($send_data_to_client['error'] == "0")
	{
		$send_data_to_client['error'] = (string)$errorCode;
	}
}

parse_str(file_get_contents("php://input"),$PUT_vars);
#var_dump($PUT_vars);

if($_SERVER['REQUEST_METHOD'] == "PUT")
{
	if (isset($PUT_vars['editType']))
	{
        $PUT_vars['editType'] = (integer)$PUT_vars['editType']; 
        if($PUT_vars['editType'] > 0 && $PUT_vars['editType'] <= 3)
        {
            $received_data_from_client['editType'] = $PUT_vars['editType'];
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
 
    if (isset($PUT_vars['newNickname']))
	{
        if(strlen($PUT_vars['newNickname']) <= 64)
        {
            $received_data_from_client['newNickname'] = $PUT_vars['newNickname'];
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
    
    if (isset($PUT_vars['user_uid']))
	{
        if(strlen($PUT_vars['user_uid']) == 40)
        {
            $received_data_from_client['user_uid'] = $PUT_vars['user_uid'];
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




$ext = (string)json_encode($send_data_to_client);
echo $ext;

    //dock
    //edittype - int 0-3
    //0 - nothing
    //1 - only nickname
    //2 - only avatar
    //3 - all


?>