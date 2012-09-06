<?php
/**
		PHP Class for login info
		text file based, simple login to get the cookie, and everyone can add users.
		Written by Yohn @ http://www.skem9.com Personal Blog = http://www.yohns.com
		Uses a SignIn class to do all the dirty work
		
######	YOU NEED TO   ##################################################################
		Make sure you CHMOD users.dat.php to 666
		Edit mySignIn.class.php so it has your domain at the top instead of testing.me 
		If you do not edit the mySignin.class.php domain name, you will not be able to signin.
		
**/
class mySignIn{

	public function __construct(){
		// path to the users text file
		$this->users = 'users.dat.php';
		// secret password included within the cookie.
		$this->SecretPW = 'SecretPW';
		// your domain
		$this->domain = 'testing.me';
	}
	
	public function addNewUser($name, $pass, $group){
		if(!is_file($this->users)){
			$line = "<?php die('missing data..'); ?>\n"; 
		} else {
			$line = '';
			// check
				$data = file($this->users);
				$fows = count($data);
				for($i=0;$i< $fows;$i++){
					$parts = explode("|", $data[$i]);
					if(isset($parts[1]) && urldecode($parts[1]) == $name){
						$nono = true;
						break;
					}
				}
		}
		if(!isset($nono)){
			$line .= uniqid($name)."|".urlencode($name)."|".md5($pass)."|".$group."\n";
			return $this->saveFile($line, "a");
		} else {
			return 'Username is already taken';
		}
	}
	
	public function deleteUser($Uid){
		$data = file($this->users);
		$fows = count($data);
		$write = '';
		for($i=0;$i< $fows;$i++){
			$parts = explode("|", $data[$i]);
			if($parts[0] != $Uid){
				$write .= trim($data[$i])."\n"; 
			}
		}
		return $this->saveFile($write);
	}
	
	public function editUser($Uid, $Uname, $pass, $group){
		// function to edit user
		// if you use this function make sure you know you have to put a password in
		// 		so you would be actually changing the users password.
		$data = file($this->users);
		$fows = count($data);
		$write = '';
		for($i=0;$i< $fows;$i++){
			$parts = explode("|", $data[$i]);
			if($parts[0] == $Uid){
				$write .= $parts[0]."|".urlencode($Uname)."|".md5($pass)."|".$group."\n";
			} else { $write .= trim($data[$i])."\n"; }
		}
		return $this->saveFile($write);
	}
	
	public function saveFile($lines, $mode="w+"){
		$fp = fopen($this->users, $mode);
			flock($fp, 2);
			fwrite($fp, $lines);
			flock($fp, 3);
			fclose($fp);
			return 'saved';
	}
	
	public function getCurUsers(){
		// loads a list of all the users
		$data = file($this->users);
		$fows = count($data);
		for($i=0;$i< $fows;$i++){
			$parts = explode("|", $data[$i]);
			if(isset($parts[1]) && $parts[1] != ''){
				$ret[$parts[0]]['id'] = $parts[0];
				$ret[$parts[0]]['name'] = urldecode($parts[1]);
				$ret[$parts[0]]['pass'] = $parts[2];
				$ret[$parts[0]]['group'] = $parts[3];
			}
		}
		return $ret;
	}
	
	public function getUserInfo($Uid){
		$data = file($this->users);
		$fows = count($data);
		for($i=0;$i< $fows;$i++){
			$parts = explode("|", $data[$i]);
			if(urldecode($parts[0]) == $Uid){
				$ret['id'] = $parts[0];
				$ret['name'] = urldecode($parts[1]);
				$ret['pass'] = $parts[2];
				$ret['group'] = $parts[3];
				return $ret;
				break;
			}
		}
	}
	
	public function logMeIn($name, $pass){
		$data = file($this->users);
		$fows = count($data);
		for($i=0;$i< $fows;$i++){
			$parts = explode("|", $data[$i]);
			if(urldecode($parts[1]) == $name){
				if($parts[2] == md5($pass) && $pass != ''){
					// login them in..
					if(@setcookie("User", md5($this->SecretPW).'_'.base64_encode($parts[3]).'_'.md5($parts[0]), time()+2952000, '/', '.'.$this->domain)){
						return 'one';
					} else {return'two';}
				} else {
					return 'two';
				}
				break;
			}
		}
		return 'three';
	}
	
	public function checkCookie($cookie){
		// returns the group they're in..
		if(isset($cookie['User'])){
			$ex = explode('_', $cookie['User']);
			if($ex[0] == md5($this->SecretPW)){
				return base64_decode($ex[1]);
			}
		}
		 return false;
	}
	
	public function signinForm(){
		return '<form action="index.php" method="post" id="editLay">
				<div class="form"><input type="hidden" name="do" value="Login" />
				<label for="Uname">Username: </label><input type="text" size="22" name="Uname" id="Uname" /><br />
				<label for="theDP">Password: </label><input type="password" size="22" name="theDP" id="theDP" /><br />
				<div class="butts"><input type="submit" value="Login" name="logMeIn" /></div>
				</div></form>';
	}
	
	public function addNewUserForm($arr=array('id'=>'', 'name'=>'', 'pass'=>'', 'group'=>'')){
		$groups = '<select name="group" id="group">';
		$val = array(1 => 'User', 2 => 'Mod', 10 => 'Admin');
		foreach($val as $k => $v){
			$add = ($arr['group'] == $k) ? '" selected="selected' : ''; 
			$groups .= '<option value="'.$k.$add.'">'.$v.'</option>';
		}
		$groups .= '</select>';
		
		if($arr['group'] > 0){
			$HiddenForm = '<input type="hidden" name="EditUser" value="'.$arr['id'].'" />';
			$subValue = 'EditU';
			$AddDelete = '<input type="submit" name="DeleteUser" value="Delete User" />';
		} else {
			$subValue = 'AddUser';
			$HiddenForm = '';
			$AddDelete = '';
		}
		
		return '<form action="index.php" method="post" id="addUser">
				<div class="form">'.$HiddenForm.'
					<label for="Uname">Username: </label><input type="text" id="Uname" name="Uname" value="'.$arr['name'].'" /><br />
					<label for="theDP">Password: </label><input type="text" id="theDP" name="theDP" value="" /><br />
					<label for="group">Group: </label>'.$groups.'
					<div class="butts"><input type="submit" name="'.$subValue.'" value="Save" />'.$AddDelete.'</div>
				</div></form>';
	}
}
?>