<?php
class ProductModel {
    private $db;
    public function __construct() {
        // Thay đổi thông tin theo database của bạn
        $this->db = new PDO("mysql:host=localhost;dbname=ten_db;charset=utf8", "root", "");
    }

    public function getAll() {
        $query = "SELECT * FROM products";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}