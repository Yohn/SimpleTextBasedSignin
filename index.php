<?php
session_start();

/**
		PHP Class for login info
		text file based, simple login to get the cookie, and everyone can add users.
		Written by Yohn @ http://www.skem9.com Personal Blod = http://imyohn.tumblr.com
		Uses a SignIn class to do all the dirty work
		
######	YOU NEED TO   ##################################################################
		Make sure you CHMOD users.dat.php to 666
		Edit mySignIn.class.php so it has your domain at the top instead of testing.me 
		If you do not edit the mySignin.class.php domain name, you will not be able to signin.

############
		For more examples of some of my work check out the following sites
		http://www.skem9.com -- built years ago, and does need some updating, but works great. Built with MooTools
		http://www.yohns.com -- recently built with my own control panel to updating the contents. Built with jQuery
		
		
**/

include('mySignIn.class.php');

$Signin = new mySignIn;

// they're trying to login.
if(isset($_POST['logMeIn'])){
	if($_POST['Uname'] != '' && $_POST['theDP'] != ''){
		// log the user in..
		$login = $Signin->logMeIn($_POST['Uname'], $_POST['theDP']);
		if($login == 'one'){ header("Location: index.php?show=admin"); } else { header("Location: index.php"); }
	} else {
		$errors[] = 'Password or Username was not found';
	}
}

// check if the user is signed in
$check = $Signin->checkCookie($_COOKIE);
// if $check is > 0 then they're signed in, and $check will return the number they are for the group the user is in
// if $check == 1 their a user
// if $check == 2 their a Mod
// if $check == 10 their an Admin

// admin pages submits
if($check > 0){
	if(isset($_POST['AddUser'])){
		if($_POST['Uname'] != '' && $_POST['theDP'] != '' && $_POST['group'] != ''){
			$sub = $Signin->addNewUser($_POST['Uname'], $_POST['theDP'], $_POST['group']);
			$UserText = 'New User has been Added!';
		}
	}
	if($check == 10 && isset($_POST['DeleteUser'])){
		// delete the user..
		$delete = $Signin->deleteUser($_POST['EditUser']);
		$UserText = 'User has been Deleted!';
	}
	if($check == 10 && isset($_POST['EditU'])){
		// delete the user..
		if($_POST['Uname'] != '' && $_POST['theDP'] != '' && $_POST['group'] != ''){
			$edit = $Signin->editUser($_POST['EditUser'], $_POST['Uname'], $_POST['theDP'], $_POST['group']);
			$UserText = 'User has been Edited!';
		} else {
			$UserText = 'You have to fill in all forms to be able to edit the user (Including the passwor)';
		}
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Sign in Script</title>
</head>
<body>
<h2>Simple text based signin script</h2>
<?php

if($check > 0){
	echo '<a href="index.php">Admin</a> <br />';
	// put whatever you want to see when you're signed in here.
	$show = isset($_GET['show']) ? $_GET['show'] : 'admin';
	switch($show){
		case 'addNewUser':
		// the form to add a new user.
		echo $Signin->addNewUserForm();
		break;
		case 'Users':
		echo '<h3>Current Users</h3>';
		// showing all the current users
		$users = $Signin->getCurUsers();
		echo '<ul>';
		foreach($users as $kk){
			echo '<li>'.$kk['name'].' - <a href="index.php?show=Edit&amp;User='.$kk['id'].'">Edit user</a></li>';
		} echo '</ul>';
		break;
		case 'Edit':
		// lets make it so only admins can edit users.
		if($check == 10){
			if(isset($_GET['User'])){
				$User = $Signin->getUserInfo($_GET['User']); 
				if(isset($User['group'])){
					echo $Signin->addNewUserForm($User);
				}
			} else {
				echo '<h3>There was an error getting the user info</h3>';
			}
		} else {
			echo '<h3>Sorry only admins can edit users</h3>';
		}
		break;
		default:
		// show=admin
		if(isset($UserText)){
			echo '<h2>'.$UserText.'</h2>';
		}
		?>
		<h3>You are signed in!</h3>
		<ul>
			<li><a href="index.php?show=Users">Show Current Users</a></li>
			<li><a href="index.php?show=addNewUser">Add New User</a></li>
		</ul>
		<?php
	}
} else {
	// user is not signed in, so show the form..
	$form = new mySignIn;
	echo $form->signinForm();
	?>
	<p>
	Username = test<br />
	Password = test
	</p>
	<?php
}
?>
</body>
</h3>