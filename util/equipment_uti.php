<?php
    // require_once dbconnection.php;

    function createEquipment($conn, $name, $category, $status="Available") {
    $sql = "INSERT INTO equipment (name, category, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $category, $status);
    return $stmt->execute();
    }

    function getEquipment($conn, $equipment_id) {
        $stmt = $conn->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
        $stmt->bind_param("i", $equipment_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function getAllEquipment($conn) {
        $result = $conn->query("SELECT * FROM equipment ORDER BY equipment_id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function updateEquipment($conn, $equipment_id, $name, $category, $status) {
        $sql = "UPDATE equipment SET name=?, category=?, status=? WHERE equipment_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $category, $status, $equipment_id);
        return $stmt->execute();
    }

    function deleteEquipment($conn, $equipment_id) {
        $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id=?");
        $stmt->bind_param("i", $equipment_id);
        return $stmt->execute();
    }
?>