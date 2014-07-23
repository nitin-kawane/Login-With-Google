<?php
//require_once '../config.php';
require_once 'config.php';
require_once 'Google_Client.php';
require_once 'contrib/Google_Oauth2Service.php';
require_once('../database/database.class.php');

$conn	= new Database_class();

$client = new Google_Client();

$oauth2 = new Google_Oauth2Service($client);

// Access is denied
if(isset($_GET['error']))
{
	header("Location:../index.php");
}

// 
if (isset($_GET['code']))
{
	$client->authenticate();
	$_SESSION['token'] = $client->getAccessToken();
}

if (isset($_SESSION['token'])) {
	$client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken())
{
	$user = $oauth2->userinfo->get();
	$token = $client->getAccessToken();
	$_SESSION['token']	= $token;
	$acc_token	= json_decode($token,true);
	
	if(!empty($user))
	{
				//print_r($user);
				//die("here");
				//Prepare data to insert
				$access_token	= $acc_token['access_token'];
				$email 			= $user['email'];
				$name 			= $user['name'];
				$user_uid 		= $user['id'];
				$user_image 	= $user['picture'];
				$type			= 'Y';
				$added_date		= date('Y-m-d H:i:s');
				
				
				// Check if user already exists in db.
				$statement  		= $conn->prepare("SELECT id FROM users WHERE user_uid = :user_uid");
				$statement->execute(array(':user_uid' => $user_uid));
				$check_user = $statement->rowCount();
				$row 		= $statement->fetch();	
				
	
			// IF user not exist in database insert its details
				if($check_user == 0)
				{	
					$c=$conn->prepare("insert into users(name, user_uid, email, user_image,   access_token, provider, date_added) 
										values (:name, :user_uid, :email, :user_image,  :access_token, :login_type, :added_date)");
					$c->bindParam(":name",$name);
					$c->bindParam(":user_uid",$user_uid);
					$c->bindParam(":email",$email);
					$c->bindParam(":user_image",$user_image);
					$c->bindParam(":access_token",$access_token);
					$c->bindParam(":login_type",$type);
					$c->bindParam(":added_date",$added_date);
					$c->execute();	
					$user_id	= $conn->lastInsertId('id'); 	
					
					header("Location:".BASE_URL."storesession.php?uid=".base64_encode($user_id));
					
				}
				else
				{
					$user_id	= $row['id'];	
					$statement  = $conn->prepare("UPDATE users SET access_token=:access_token  WHERE user_uid = :user_uid");
					$statement->execute(array(':access_token' => $access_token, ':user_uid' => $user_uid));
					
					header("Location:".BASE_URL."storesession.php?uid=".base64_encode($user_id));	
					
					
				}	
	
	}
		
} 
?>