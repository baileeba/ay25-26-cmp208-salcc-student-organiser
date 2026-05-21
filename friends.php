<?php

    session_start();
    include "acc/connect.php";

    if(!isset($_SESSION["user_id"])) {
        header("Location: acc/login.php");
        exit();
    }

    $timeout_duration = 1800;

    if(isset($_SESSION['last_activity'])) {
        $idle_time = time() - $_SESSION['last_activity'];
        if($idle_time > $timeout_duration) {
            session_unset();
            session_destroy();
            header("Location: acc/login.php");
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
    
    $user_id = $_SESSION["user_id"];

    $query = "SELECT name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $user_name = $user_data['name'];

    $pending_requests_query = "
        SELECT fr.request_id, u.user_id, u.name, u.username 
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.user_id
        WHERE fr.receiver_id = ? AND fr.status = 'pending'
        ORDER BY fr.request_id DESC
    ";
    $stmt = $conn->prepare($pending_requests_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pending_requests = $stmt->get_result();

    $friends_query = "
        SELECT u.user_id, u.name, u.username
        FROM friendships f
        JOIN users u ON (
            (f.user_id_1 = ? AND f.user_id_2 = u.user_id) OR
            (f.user_id_2 = ? AND f.user_id_1 = u.user_id)
        )
        ORDER BY u.name
    ";
    $stmt = $conn->prepare($friends_query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $friends = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
        <title>Friends</title>
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>
    <body>
        <div align = "center">
            <div class= "writeHeader"></div>

            <div class="friends-container">
                <div class="back-to-home">
                    <a href="profile.php">← Back to Profile</a>
                </div>

                <h1>Friends</h1>

                <div class="friends-section">
                    <div class="section-title">Search for Users</div>
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by name or username...">
                        <button onclick="searchUsers()">Search</button>
                    </div>
                    <div id="searchResults" class="search-results"></div>
                </div>

                <div class="friends-section">
                    <div class="section-title">Friend Requests (<?php echo $pending_requests->num_rows; ?>)</div>
                    <div id="friendRequests">
                        <?php
                            if ($pending_requests->num_rows > 0) {
                                while ($request = $pending_requests->fetch_assoc()) {
                                    echo "
                                    <div class='friend-item'>
                                        <div class='friend-info'>
                                            <div class='friend-name'>" . htmlspecialchars($request['name']) . "</div>
                                            <div class='friend-username'>@" . htmlspecialchars($request['username']) . "</div>
                                        </div>
                                        <div class='friend-actions'>
                                            <button class='btn-accept' onclick='respondToRequest(" . $request['request_id'] . ", \"accepted\")'>Accept</button>
                                            <button class='btn-decline' onclick='respondToRequest(" . $request['request_id'] . ", \"declined\")'>Decline</button>
                                        </div>
                                    </div>
                                    ";
                                }
                            } else {
                                echo "<div class='empty-message'>No pending friend requests</div>";
                            }
                        ?>
                    </div>
                </div>

                
                <div class="friends-section">
                    <div class="section-title">Your Friends (<?php echo $friends->num_rows; ?>)</div>
                    <div id="friendsList">
                        <?php
                            if ($friends->num_rows > 0) {
                                while ($friend = $friends->fetch_assoc()) {
                                    echo "
                                    <div class='friend-item'>
                                        <div class='friend-info'>
                                            <div class='friend-name'>" . htmlspecialchars($friend['name']) . "</div>
                                            <div class='friend-username'>@" . htmlspecialchars($friend['username']) . "</div>
                                        </div>
                                    </div>
                                    ";
                                }
                            } else {
                                echo "<div class='empty-message'>You don't have any friends yet</div>";
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function searchUsers() {
                const searchTerm = document.getElementById('searchInput').value.trim();
                
                if (!searchTerm) {
                    document.getElementById('searchResults').classList.remove('active');
                    return;
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', './api/search_users.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const results = JSON.parse(xhr.responseText);
                        displaySearchResults(results);
                    }
                };
                
                xhr.send('search=' + encodeURIComponent(searchTerm));
            }

            function displaySearchResults(results) {
                const resultsDiv = document.getElementById('searchResults');
                
                if (results.length === 0) {
                    resultsDiv.innerHTML = '<div class="empty-message">No users found</div>';
                    resultsDiv.classList.add('active');
                    return;
                }

                let html = '';
                results.forEach(user => {
                    html += `
                    <div class="friend-item">
                        <div class="friend-info">
                            <div class="friend-name">${user.name}</div>
                            <div class="friend-username">@${user.username}</div>
                        </div>
                        <div class="friend-actions">
                            <button class="${user.buttonClass}" onclick="handleFriendAction(${user.user_id}, '${user.action}')">${user.buttonText}</button>
                        </div>
                    </div>
                    `;
                });
                
                resultsDiv.innerHTML = html;
                resultsDiv.classList.add('active');
            }

            function handleFriendAction(userId, action) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', './api/manage_friends.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.message);
                            searchUsers();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }
                };
                
                xhr.send('user_id=' + userId + '&action=' + action);
            }

            function respondToRequest(requestId, action) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', './api/respond_friend_request.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }
                };
                
                xhr.send('request_id=' + requestId + '&action=' + action);
            }

            
            document.getElementById('searchInput').addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    searchUsers();
                }
            });
        </script>

        <script src = "js/navbar.js" defer></script>
    </body>
</html>
