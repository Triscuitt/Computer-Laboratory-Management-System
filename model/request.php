<?php
require_once '../dbconnection.php';
    class Request{
        private $title;
        private $type;
        private $priority;
        private $status;
        private $description;

        private $requestedBy;
        private $requestDate;

        public function __construct($title, $type, $priority, $description, $requestBy){
            $this->title = $title;
            $this->type = $type;
            $this->priority = $priority;
            $this->description = $description;
            $this->requestedBy = $requestBy;
        }

        public function create(){
            $conn = getConnection();

            $stmt = $conn->prepare("INSERT INTO request (request_title, request_type, request_priority, status, request_description, submitter_id) VALUES (?, ?, ?, 'Pending', ?, ?)");
            $stmt->bind_param('ssssi', $this->title, $this->type, $this->priority, $this->description, $this->requestedBy);

            $result = $stmt->execute();

            if (!$result) {
                $error_msg = $stmt->error;
                $error_code = $stmt->errno;
                error_log("Session creation failed: Error #$error_code - $error_msg");
                $stmt->close();
                $conn->close();
                throw new Exception("Database error: $error_msg (Code: $error_code)");
            }

            $request_id = $conn->insert_id;
            $this->requestDate = date('Y-m-d H:i:s');

            $stmt->close();
            $conn->close();
            return $request_id;
        }

        public static function getAllRequestById($faculty_id){
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT * FROM request WHERE submitter_id = ? ORDER BY submitted_at DESC;");
            $stmt->bind_param("i", $faculty_id);
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                array_push($requests, $row);
            }
            
            $stmt->close();
            $conn->close();
            
            return $requests;
        }
    }
?>