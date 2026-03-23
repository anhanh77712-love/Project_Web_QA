<?php
class profile_m extends connectDB {
	function __construct() {
		parent::__construct();
	}

	function user_getById($id) {
		$id = mysqli_real_escape_string($this->con, $id);
		$sql = "SELECT * FROM users WHERE id = '$id'";
		$result = mysqli_query($this->con, $sql);
		return $result ? mysqli_fetch_assoc($result) : null;
	}
		// Thêm hàm update có avatar
	function user_updateProfile($user_id, $full_name, $phone, $province_code, $district_code, $ward_code, $address_detail, $avatar) {
		$user_id = mysqli_real_escape_string($this->con, $user_id);
		$full_name = mysqli_real_escape_string($this->con, $full_name);
		$phone = mysqli_real_escape_string($this->con, $phone);
		$province_code = mysqli_real_escape_string($this->con, $province_code);
		$district_code = mysqli_real_escape_string($this->con, $district_code);
		$ward_code = mysqli_real_escape_string($this->con, $ward_code);
		$address_detail = mysqli_real_escape_string($this->con, $address_detail);
		$avatar = mysqli_real_escape_string($this->con, $avatar);

		$sql = "UPDATE users
				SET full_name = '$full_name',
					phone = '$phone',
					province_code = '$province_code',
					district_code = '$district_code',
					ward_code = '$ward_code',
					address_detail = '$address_detail',
					avatar = '$avatar'
				WHERE id = '$user_id'";

		return mysqli_query($this->con, $sql);
	}
	

	function user_changePassword($user_id, $current_password, $new_password) {
		$user_id = mysqli_real_escape_string($this->con, $user_id);
		$current_password = mysqli_real_escape_string($this->con, $current_password);
		$new_password = mysqli_real_escape_string($this->con, $new_password);

		// Verify current password (project currently uses plain-text password comparison)
		$checkSql = "SELECT id FROM users WHERE id = '$user_id' AND password = '$current_password'";
		$checkResult = mysqli_query($this->con, $checkSql);
		if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
			return false;
		}

		$updateSql = "UPDATE users SET password = '$new_password' WHERE id = '$user_id'";
		return mysqli_query($this->con, $updateSql);
	}
	
	// Thêm vào trong file MVC/Models/profile_m.php
public function get_user_info($user_id)
{
    // Giả sử bảng người dùng của bạn tên là 'users'
    // và biến kết nối CSDL là $this->con
    $sql = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
    return mysqli_query($this->con, $sql);
}

}
?>