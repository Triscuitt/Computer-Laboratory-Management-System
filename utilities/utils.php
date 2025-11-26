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

    
?>