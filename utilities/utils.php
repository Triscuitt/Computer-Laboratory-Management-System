<?php
    function headto($location) {
        header("Location: " . $location);
        exit;
    }

    function displayMessage(){
        if(isset($_SESSION['alertMessage'])) {
            echo $_SESSION['alertMessage'] ;
            unset($_SESSION['alertMessage']);
        }
    }

    function clearPost() {
        if (!empty($_POST)) {
            headto($_SERVER["PHP_SELF"]);
            exit;
    }
}
    
?>