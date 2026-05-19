<?php
header("Content-Type: application/json");

session_start();
include "../acc/connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';


if ($action === 'get_friends') {
    $sql = "SELECT DISTINCT u.user_id, u.name, u.username
        FROM users u
        INNER JOIN friendships f
            ON ((f.user_id_1 = ? AND f.user_id_2 = u.user_id)
                OR
                (f.user_id_2 = ? AND f.user_id_1 = u.user_id))
        WHERE u.user_id != ?
        ORDER BY u.name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iii",
        $user_id,
        $user_id,
        $user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $friends = [];

    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }
    echo json_encode($friends);
    exit;
}



if ($action === 'get_collaborators') {
    $group_id = $_GET['group_id'] ?? null;

    if (!$group_id) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT u.user_id, u.name, u.username, gm.role
        FROM group_members gm
        INNER JOIN users u
            ON gm.user_id = u.user_id
        WHERE gm.group_id = ?
        ORDER BY
            CASE
                WHEN gm.role = 'leader' THEN 0 ELSE 1
            END, u.name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $collaborators = [];

    while ($row = $result->fetch_assoc()) {
        $collaborators[] = $row;
    }

    echo json_encode($collaborators);
    exit;
}



if ($method === 'GET' && (!$action || $action === 'get_all')) {
    $sql = "SELECT DISTINCT a.assignment_id AS id, a.course_id, a.title, a.description, a.due_date,
            a.due_time, a.priority, a.status, a.weight_percentage, a.group_id, a.is_group_assignment,
            c.course_code, c.course_name
        FROM assignments a
        INNER JOIN courses c
        ON a.course_id = c.course_id
        LEFT JOIN group_members gm
            ON a.group_id = gm.group_id
        WHERE
            a.user_id = ? OR gm.user_id = ?
        ORDER BY
            a.due_date ASC, a.due_time ASC";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $user_id,
        $user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $assignments = [];

    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }

    echo json_encode($assignments);
    exit;
}


if ($method === 'POST' && $action === 'create') {
    if (
        empty($_POST['course_id']) ||
        empty($_POST['title']) ||
        empty($_POST['due_date'])
    ) {
        echo json_encode(["error" => "missing_required_fields"]);
        exit;
    }

    $course_id = (int) $_POST['course_id'];

    $title = trim($_POST['title']);

    $description = trim($_POST['description'] ?? '');

    $due_date = $_POST['due_date'];

    $due_time =!empty($_POST['due_time']) ? $_POST['due_time'] : null;

    $priority = $_POST['priority'] ?? 'medium';

    $weight_percentage = $_POST['weight_percentage'] !== '' ? $_POST['weight_percentage'] : null;

    $is_group_assignment = isset($_POST['is_group_assignment']) ? 1 : 0;

    $group_id = null;


    if ($is_group_assignment) {
        $group_name = $title . " Group";

        $group_description = "group assignment";

        $group_sql = "INSERT INTO groups (group_name, description, course_id, user_id)
            VALUES (?, ?, ?, ?)";

        $group_stmt = $conn->prepare($group_sql);

        $group_stmt->bind_param(
            "ssii",
            $group_name,
            $group_description,
            $course_id,
            $user_id
        );

        if (!$group_stmt->execute()) {
            echo json_encode(["error" => $group_stmt->error]);
            exit;
        }

        $group_id = $group_stmt->insert_id;


        $member_sql = "INSERT INTO group_members (group_id, user_id, role)
            VALUES (?, ?, ?)";

        $leader_role = 'leader';

        $leader_stmt = $conn->prepare($member_sql);

        $leader_stmt->bind_param(
            "iis",
            $group_id,
            $user_id,
            $leader_role);

        $leader_stmt->execute();


        if (isset($_POST['collaborators'])) {

            foreach ($_POST['collaborators'] as $collaborator_id) {

                $collaborator_id = (int) $collaborator_id;

                if ($collaborator_id === $user_id) {
                    continue;
                }

                $member_role = 'member';

                $collab_stmt = $conn->prepare($member_sql);

                $collab_stmt->bind_param(
                    "iis",
                    $group_id,
                    $collaborator_id,
                    $member_role);

                $collab_stmt->execute();
            }
        }
    }


    $sql = "INSERT INTO assignments (user_id, course_id, title, description, due_date,
            due_time, priority, status, weight_percentage, is_group_assignment, group_id)
            VALUES (?,?,?,?,?,?,?,'not_started',?,?,?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iisssssiii",
        $user_id,
        $course_id,
        $title,
        $description,
        $due_date,
        $due_time,
        $priority,
        $weight_percentage,
        $is_group_assignment,
        $group_id
    );

    if (!$stmt->execute()) {
        echo json_encode(["error" => $stmt->error]);
        exit;
    }

    echo json_encode(["status" => "created",
    "assignment_id" => $stmt->insert_id]);
    exit;
}


if ($method === 'POST' && $action === 'delete') {
    if (empty($_POST['assignment_id'])) {
        echo json_encode(["error" => "missing_assignment_id"]);
        exit;
    }

    $assignment_id =
        (int) $_POST['assignment_id'];


    $check_sql = "
        SELECT group_id FROM assignments
        WHERE assignment_id = ?
        AND user_id = ?";

    $check_stmt = $conn->prepare($check_sql);

    $check_stmt->bind_param("ii", $assignment_id, $user_id);

    $check_stmt->execute();

    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "assignment_not_found"]);
        exit;
    }

    $assignment = $result->fetch_assoc();

    $group_id = $assignment['group_id'];

    $delete_sql = "
        DELETE FROM assignments
        WHERE assignment_id = ?
        AND user_id = ?";

    $delete_stmt = $conn->prepare($delete_sql);

    $delete_stmt->bind_param("ii", $assignment_id, $user_id);

    if (!$delete_stmt->execute()) {
        echo json_encode(["error" => $delete_stmt->error]);
        exit;
    }

    if ($group_id) {
        $delete_group_sql = "
            DELETE FROM groups
            WHERE group_id = ?";

        $group_stmt = $conn->prepare($delete_group_sql);

        $group_stmt->bind_param("i",$group_id);

        $group_stmt->execute();
    }

    echo json_encode([
        "status" => "deleted"
    ]);

    exit;
}


echo json_encode([
    "error" => "invalid_request"
]);
exit;
?>