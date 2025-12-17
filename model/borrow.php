<?php
    class Borrow{
        
        // Get all borrowed and returned items
        public static function getAllItems($user_id){
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT b.borrow_id, b.borrow_date, b.return_date, b.status,
            u.id as user_id, u.first_name, u.last_name, u.student_number, u.role,
            e.equipment_id, e.name as equipment_name, e.pc_id, e.lab_location
            FROM borrow b
            JOIN users u ON b.user_id = u.id
            JOIN equipment e ON b.equipment_id = e.equipment_id
            WHERE b.user_id = ?
            ORDER BY b.borrow_date DESC;");
            $stmt->bind_param("i", $user_id);
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                array_push($items, $row);
            }
            
            $stmt->close();
            $conn->close();
            
            return $items;
        }
    }
?>