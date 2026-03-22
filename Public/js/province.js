$(document).ready(function() {
    // 1. Khi chọn Tỉnh
    $('#province').change(function() {
        var p_code = $(this).val();
        if(p_code != "") {
            $.ajax({
                url: "/web_qlsp/home/get_districts/" + p_code,
                method: "GET",
                success: function(data) {
                    $('#district').html(data); // Đổ dữ liệu vào ô Huyện
                    $('#ward').html('<option value="">Chọn Phường/Xã</option>'); // Reset ô Xã
                }
            });
        }
    });

    // 2. Khi chọn Huyện
    $('#district').change(function() {
        var d_code = $(this).val();
        if(d_code != "") {
            $.ajax({
                url: "/web_qlsp/home/get_wards/" + d_code,
                method: "GET",
                success: function(data) {
                    $('#ward').html(data); // Đổ dữ liệu vào ô Xã
                }
            });
        }
    });
});