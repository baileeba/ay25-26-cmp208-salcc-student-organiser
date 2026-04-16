<?php
include "connect.php";

$name = $username = $email = $password = "";
$nameErr = $usernameErr = $emailErr = $passwordErr = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(empty($_POST['name'])){
        $nameErr = "Name is required";
    } else {
        $name = $_POST['name'];
    }

    if(empty($_POST['username'])){
        $usernameErr = "Username is required";
    } else {
        $username = $_POST['username'];
    }

    if(empty($_POST['email'])){
        $emailErr = "Email is required";
    } else {
        $email = $_POST['email'];
    }

    if(empty($_POST['password']) || empty($_POST['confirm_password'])){
        $passwordErr = "Password is required";
    } elseif($_POST['password'] != $_POST['confirm_password']){
        $passwordErr = "Passwords do not match";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($nameErr == "" && $emailErr == "" && $usernameErr == "" && $passwordErr == "") {

        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $username, $email, $password);

        if ($stmt->execute()) {
            echo "Record saved successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang = 'en'> 
	<head>
		<meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
		<title>Sign Up</title>
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/folder.png'>
	</head>
	
	<body>
		<h1>Sign Up</h1>
		<form action = "signup.php" method = "post">
			<label>Full Name:</label><br>
			<input type = "text" name = "name"><br><br>
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