<?php
    // require_once dbconnection.php;

    function createRequest($conn, $user_id, $title, $type, $priority, $description) {
        $sql = "INSERT INTO request (submitter_id, request_title, request_type, request_priority, request_description) VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $title, $type, $priority, $description);
        return $stmt->execute();
    }

    function getRequest($conn, $request_id) {
        $stmt = $conn->prepare("SELECT * FROM request WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function getAllRequests($conn) {
        $sql = "SELECT r.*, u.first_name, u.last_name
                FROM request r
                JOIN users u ON r.submitter_id = u.id
                ORDER BY request_id DESC";
        return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    function deleteRequest($conn, $request_id) {
        $stmt = $conn->prepare("DELETE FROM request WHERE request_id=?");
        $stmt->bind_param("i", $request_id);
        return $stmt->execute();
    }
?>