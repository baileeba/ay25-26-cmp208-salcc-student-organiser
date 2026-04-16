<?php
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

        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $row = $result->fetch_assoc();

            if(password_verify($password, $row["password"])){
                echo "Login successful!";
                
                session_start();
                $_SESSION["username"] = $row["username"];
                
            } else {
                $loginErr = "Invalid password";
            }

        } else {
            $loginErr = "User not found";
        }
    }
}
?>