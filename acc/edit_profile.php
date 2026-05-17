<?php
    session_start();
    include "connect.php";

    if(!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $message = "";
    $message_type = "";


    $query = "SELECT name, email, username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if(!$user) {
        header("Location: login.php");
        exit();
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $username = trim($_POST["username"] ?? "");

  
        if(empty($name) || empty($email) || empty($username)) {
            $message = "All fields are required.";
            $message_type = "error";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
            $message_type = "error";
        } elseif(strlen($username) < 3) {
            $message = "Username must be at least 3 characters long.";
            $message_type = "error";
        } else {

            $check_email = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $email_result = $stmt->get_result();
            $stmt->close();

            if($email_result->num_rows > 0) {
                $message = "Email is already in use.";
                $message_type = "error";
            } else {
  
                $check_username = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
                $stmt = $conn->prepare($check_username);
                $stmt->bind_param("si", $username, $user_id);
                $stmt->execute();
                $username_result = $stmt->get_result();
                $stmt->close();

                if($username_result->num_rows > 0) {
                    $message = "Username is already taken.";
                    $message_type = "error";
                } else {

                    $update_query = "UPDATE users SET name = ?, email = ?, username = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("sssi", $name, $email, $username, $user_id);
                    
                    if($stmt->execute()) {
                        $message = "Profile updated successfully!";
                        $message_type = "success";
  
                        $user["name"] = $name;
                        $user["email"] = $email;
                        $user["username"] = $username;
                    } else {
                        $message = "Error updating profile. Please try again.";
                        $message_type = "error";
                    }
                    $stmt->close();
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Profile</title>
        <link rel="stylesheet" href="../style.css">
        <link rel='icon' href='../assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

            <div class="edit-profile-container">
                <h1>Edit Profile</h1>

                <?php if(!empty($message)): ?>
                    <div class= "message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="<?php echo htmlspecialchars($user["name"] ?? ""); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($user["email"] ?? ""); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($user["username"] ?? ""); ?>" 
                            required
                        >
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-save">Save Changes</button>
                        <button type="button" class="btn btn-cancel" onclick="window.location.href='../profile.php'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script src="../js/navbar.js" defer></script>
    </body>
</html>
