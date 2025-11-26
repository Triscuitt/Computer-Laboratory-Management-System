<?php 
    function validateEmptyField($field, $field1, $field2, $field3, $field4, $field5, $field6) {
        if(empty($field) || empty($field1) || empty($field2) || empty($field3) || empty($field4 )|| empty($field5) || empty($field6)) {
            return "This field is required.";
        }

        return true;
    }

    function validateLength($length, $length1) {
        if(strlen($length) < 2 ||strlen($length1) < 2) {
            return "Your names must be at least 2 Characters.";
        }
 
        return true;
    }

    function validateStudentNumber($number) {
        $number = trim($number);
        if (!preg_match('@^\d{4}-\d{5}$@', $number)) {
            return "Invalid student number format";
        } else if(strlen($number) !== 10) {
            return "Number must be exactly 10 characters (including hyphen).";
        } else if(!preg_match('@^[0-9-]+$@', $number)) {
            return "Only digits and one hyphen are allowed.";
        } else if (strpos($number, '-') !== 4) {
            return "Hyphen must be after the 4th digit.";
        }  else if(checkStudentNumber($number)) {
            return "Student number already exists.";
        } 

        return true;
    }

    function validateEmail($email) {
        $email = strtolower($email);

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "please enter a valid email address";
        } /*else if(!str_ends_with($email, '@dyci.edu.ph')) {
            return "Registration requires institutional email";
        } */else if (checkEmail($email)) {
            return "Email is already Taken";
        }
        return true;
        
    }

    function validatePassword($password, $confirmPassword) {
        $check =  preg_match('@[A-Z]@', $password) &&
        preg_match('@[a-z]@', $password) &&
        preg_match('@[0-9]@', $password) &&
        preg_match('@[^a-zA-Z0-9]@', $password) &&
        strlen($password) >= 8;
       

        if(!$check) {
            return 'Your password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.';
        } else if ($password !== $confirmPassword){
            return "Passwords do not match.";
        }
        return true;
}


?>