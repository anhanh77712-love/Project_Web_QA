<?php
class master_customer_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    function categories_selectAll() {
        $sql = "SELECT * FROM categories ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
}