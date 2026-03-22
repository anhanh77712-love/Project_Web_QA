<?php
class categories_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    public function checkDuplicate($slug) {
        $sql = "SELECT id FROM categories WHERE slug = '$slug'";
        $result = mysqli_query($this->con, $sql);
        return mysqli_num_rows($result);
    }
    function categories_selectAll() {
        $sql = "SELECT * FROM categories ORDER BY id DESC";
        return mysqli_query($this->con, $sql);
    }
    function categories_insert($name, $slug, $thumbnail) {
        $sql = "INSERT INTO categories (name, slug, thumbnail) VALUES ('$name', '$slug', '$thumbnail')";
        return mysqli_query($this->con, $sql);
    }
    function categories_update($id, $name, $slug, $thumbnail) {
        $sql = "UPDATE categories SET name='$name', slug='$slug', thumbnail='$thumbnail' WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    function categories_delete($id) {
        $sql = "DELETE FROM categories WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    function categories_select($name) {
        $sql = "SELECT * FROM categories WHERE name LIKE '%$name%' ORDER BY id DESC";
        return mysqli_query($this->con, $sql);
    }

}