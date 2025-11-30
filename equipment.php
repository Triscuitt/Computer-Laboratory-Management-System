<?php 
    class Equipment{
        private $name;
        private $category;
        private $status;
        private $last_updated;
        private $borrowed;
        private $returned;

        function __construct($equipment_name, $equipment_category, $equipment_status){
            $name = $equipment_name;
            $category = $equipment_category;
            $status = $equipment_status;
        }

    }
?>