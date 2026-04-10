<?php
class reviews_m extends connectDB {
    function __construct() {
        parent::__construct();
    }

    // Lấy danh sách đánh giá của 1 sản phẩm
    function reviews_selectByProduct($product_id) {
        $product_id = intval($product_id);
        $sql = "SELECT r.*, u.full_name, u.avatar 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = $product_id AND r.status = 1 
        ORDER BY r.review_date DESC";
        return mysqli_query($this->con, $sql);
    }

    // Kiểm tra khách hàng đủ điều kiện đánh giá không
    function reviews_checkEligible($user_id, $product_id) {
        $user_id = intval($user_id);
        $product_id = intval($product_id);
        
        $sql = "SELECT o.id 
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = $user_id 
                  AND oi.product_id = $product_id 
                  AND o.status = 'completed'
                  AND o.id NOT IN (
                      SELECT order_id FROM reviews 
                      WHERE user_id = $user_id AND product_id = $product_id
                  )
                LIMIT 1";
        
        $result = mysqli_query($this->con, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['id'];
        }
        return false;
    }

    // Thêm đánh giá mới (Hoàn toàn sạch bóng edit_count)
    function reviews_insert($user_id, $product_id, $order_id, $rating, $comment) {
        $user_id = intval($user_id);
        $product_id = intval($product_id);
        $order_id = intval($order_id);
        $rating = intval($rating);
        $comment_safe = mysqli_real_escape_string($this->con, $comment);
        
        $sql = "INSERT INTO reviews (user_id, product_id, order_id, rating, comment, status) 
                VALUES ($user_id, $product_id, $order_id, $rating, '$comment_safe', 1)";
        return mysqli_query($this->con, $sql);
    }

    // Lấy 1 đánh giá cụ thể (dùng để check quyền)
    function reviews_getById($id) {
        return mysqli_query($this->con, "SELECT * FROM reviews WHERE id = " . intval($id));
    }

    // Khách hàng tự cập nhật đánh giá (Hoàn toàn sạch bóng edit_count)
    function reviews_update_by_user($id, $user_id, $rating, $comment) {
        $id = intval($id); 
        $user_id = intval($user_id); 
        $rating = intval($rating);
        $comment_safe = mysqli_real_escape_string($this->con, $comment);
        
        $sql = "UPDATE reviews 
                SET rating = $rating, comment = '$comment_safe' 
                WHERE id = $id AND user_id = $user_id";
        return mysqli_query($this->con, $sql);
    }

    // Khách hàng tự xóa đánh giá
    function reviews_delete_by_user($id, $user_id) {
        $id = intval($id); 
        $user_id = intval($user_id);
        $sql = "DELETE FROM reviews WHERE id = $id AND user_id = $user_id";
        return mysqli_query($this->con, $sql);
    }

    // DÀNH CHO ADMIN
    function reviews_selectAllAdmin() {
        $sql = "SELECT r.*, u.full_name, p.name as product_name, p.slug, p.thumbnail 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                JOIN products p ON r.product_id = p.id
                ORDER BY r.review_date DESC";
        return mysqli_query($this->con, $sql);
    }

    function reviews_toggleStatus($id) {
        $id = intval($id);
        $sql = "UPDATE reviews SET status = NOT status WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }

    function reviews_delete($id) {
        $id = intval($id);
        $sql = "DELETE FROM reviews WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
}
?>