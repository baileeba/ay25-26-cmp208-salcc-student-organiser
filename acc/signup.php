<?php
	include "acc/connect.php";

	$full_name = $nameErr = '';
	$username = $usernameErr = '';
	$email = $emailErr = '';
	$password = $confirm_password = $passwordErr = '';
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(($_POST['password']) != ($_POST['confirm_password'])){
			$passwordErr = 'Password incorrect';
		}else{
			$password = ($_POST['password']);
		}
		if(empty($_POST['full_name'])) {
			$nameErr = 'Name is required';
		}else{
			$full_name = ($_POST['full_name']);
		}
		if(empty($_POST['username'])) {
			$usernameErr = 'Username is required';
		}else{
			$username = ($_POST['username']);
		}
		if(empty($_POST['email'])) {
			$emailErr = 'Email is required';
		}else{
			$email = ($_POST['email']);
		}
		
		$full_name = ($_POST['full_name']);
		$username = ($_POST['username']);
		$email = ($_POST['email']);
		$password = $confirm_password = ($_POST['password']);
	
	if ($nameErr == "" && $emailErr == "" && $usernameErr == "" && $passwordErr == "")  {
			$stmt = $conn -> prepare("INSERT INTO users (full_name, username, email,
			password) VALUES (?,?,?,?)");
			$stmt -> bind_param("ssss",$full_name,$username,$email,$password);
	
	if ($stmt->execute() ) {
				$successMsg = "Record saved successfully!";
				$full_name = $email = $password = $username = "";
				echo $successMsg;
			}
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
</head>
<body>

    <h1>Sign Up</h1>

    <form action = "signup.php" method = "post">
        <label>Full Name:</label><br>
        <input type = "text" name = "full_name"><br><br>

        <label>Username:</label><br>
        <input type="text" name = "username"><br><br>

        <label>Email:</label><br>
        <input type = "email" name = "email"><br><br>

        <label>Password:</label><br>
        <input type="password" name = "password"><br><br>

        <label>Confirm Password:</label><br>
        <input type = "password" name = "confirm_password"><br><br>

        <input type = "submit" value = "Sign Up">
    </form>

</body>
</html>