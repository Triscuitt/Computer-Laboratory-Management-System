<?php
    function headto($location) {
        header("Location: " . $location);
        exit;
    }

    function displayMessage(){
        if(isset($_SESSION['popUpMessage'])) {
            echo $_SESSION['popUpMessage'] ;
            unset($_SESSION['popUpMessage']);
        }
    }

    function cleanEmail($email) {
        $email = trim($email);
        $email = strtolower($email);

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $email;
        
    }
    /*function cleanStudentNumber($student_number){
        if (!preg_match('/^\d{4}-\d{6}$/', $student_number)) {
            return false;
        }
        return $student_number;
    } */
?>