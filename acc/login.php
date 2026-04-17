<?php
session_start();
include "connect.php";

$username = $password = "";
$usernameErr = $passwordErr = $loginErr = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty($_POST["username"])){
        $usernameErr = "Username is required";
    } else {
        $username = $_POST["username"];
    }

    if(empty($_POST["password"])){
        $passwordErr = "Password is required";
    } else {
        $password = $_POST["password"];
    }

    if($usernameErr == "" && $passwordErr == ""){

        $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $row = $result->fetch_assoc();

            if(password_verify($password, $row["password"])){
                
                $_SESSION["username"] = $row["username"];
                
                header("Location: ../index.php");
                exit();
                
            } else {
                $loginErr = "Invalid password";
            }

        } else {
            $loginErr = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang = 'en'> 
	<head>
		<meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
		<title>Login</title>
        <link rel = "stylesheet" href = "../style.css">
        <link rel = 'icon' href = '../assets/GREEN_FOLDER.png'>
	</head>
	
	<body id = 'login'>
        <br><br>
        <div class = 'folder'>
            <br><br>
		    <h1>Welcome!</h1>
		    <form action = "login.php" method = "post">
			    <label>Username:</label><br>
			    <input type="text" name = "username"><br>
			    <label>Email:</label><br>
			    <input type = "email" name = "email"><br>
			    <label>Password:</label><br>
			    <input type="password" name = "password"><br><br>
			    <input type = "submit" value = "Log In" class = "login">
		    </form>
        </div>

        <a href="../acc/signup.php">make an account</a>

    <?php
        if(isset($usernameErr)) echo $usernameErr."<br>";
        if(isset($passwordErr)) echo $passwordErr."<br>";
        if(isset($loginErr)) echo $loginErr."<br>";
    ?>
	</body>
	</html>