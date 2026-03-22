<?php
class provinces_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
   function provinces_selectAll() {
    $sql = "SELECT * FROM provinces ORDER BY name ASC";
    return mysqli_query($this->con, $sql);
}
// Lấy Quận/Huyện theo mã Tỉnh
function districts_selectByProvince($p_code) {
    $sql = "SELECT * FROM districts WHERE province_code = '$p_code' ORDER BY name ASC";
    return mysqli_query($this->con, $sql);
}

// Lấy Phường/Xã theo mã Quận
function wards_selectByDistrict($d_code) {
    $sql = "SELECT * FROM wards WHERE district_code = '$d_code' ORDER BY name ASC";
    return mysqli_query($this->con, $sql);
}

}