<?php
header('Content-Type: application/json');

// 1. CẤU HÌNH API KEY
$apiKey = 'AIzaSyBxSm5Czd0cezfRfATipXL8HwNEIxVjjIg'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'])) {
    $productName = trim($_POST['product_name']);

    if (empty($productName)) {
        echo json_encode(['success' => false, 'message' => 'Chưa nhập tên sản phẩm']);
        exit;
    }

    // 2. PROMPT (Kịch bản nhắc AI)
    $prompt = "Đóng vai chuyên gia Marketing của Coolmate. " .
              "Viết một đoạn mô tả sản phẩm hấp dẫn cho: '$productName'. " .
              "Yêu cầu: Độ dài trung bình (khoảng 7 - 10 câu), giọng văn nam tính, hiện đại, tập trung vào công năng. " .
              "Định dạng: Trả về mã HTML, sử dụng thẻ <p> cho đoạn văn và <ul><li> cho các gạch đầu dòng tính năng nổi bật. " .
              "Không cần tiêu đề, chỉ cần nội dung mô tả.";

    // 3. HÀM GỌI API VỚI MODEL CỤ THỂ + CẤU HÌNH
    function callGemini($apiKey, $model, $prompt) {
        $payload = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => [
                "temperature" => 0.8,
                "maxOutputTokens" => 512
            ]
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . $apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        return [
            'httpCode' => $httpCode,
            'body' => $result,
            'curlError' => $curlErr,
            'model' => $model
        ];
    }

    // 3b. Lấy danh sách model khả dụng, ưu tiên gemini-3.0-flash nếu có
    function listAvailableModels($apiKey) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $curlErr = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);
        if ($curlErr) return [];
        $decoded = json_decode($result, true);
        if (!isset($decoded['models']) || !is_array($decoded['models'])) return [];
        $names = [];
        foreach ($decoded['models'] as $m) {
            if (isset($m['name'])) $names[] = $m['name'];
        }
        return $names;
    }

    // 3c. Fallback mô tả cục bộ khi hết quota
    function generateFallbackDescription($name) {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $html = "";
        $html .= "<p>" . $safeName . " là lựa chọn lý tưởng cho phong cách ngày thường với chất liệu thoáng mát, bền bỉ và cảm giác mặc tự tin.</p>";
        $html .= "<p>Thiết kế tối giản, hiện đại giúp dễ phối đồ trong nhiều bối cảnh: đi làm, dạo phố hay vận động nhẹ. Các đường may gọn gàng, form chuẩn mang lại sự thoải mái suốt ngày dài.</p>";
        $html .= "<ul>";
        $html .= "<li>Chất liệu: sợi cao cấp, mềm mịn, hạn chế nhăn.</li>";
        $html .= "<li>Thoáng khí tốt, thấm hút mồ hôi, phù hợp khí hậu nhiệt đới.</li>";
        $html .= "<li>Form dáng nam tính, gọn gàng, dễ phối với quần jean, short, jogger.</li>";
        $html .= "<li>Độ bền cao, giữ form sau nhiều lần giặt.</li>";
        $html .= "<li>Màu sắc cơ bản dễ dùng hằng ngày.</li>";
        $html .= "</ul>";
        $html .= "<p>Gợi ý sử dụng: kết hợp cùng sneaker hoặc loafer để hoàn thiện outfit. Bảo quản bằng cách giặt nhẹ, phơi nơi thoáng mát để sản phẩm bền lâu.</p>";
        return $html;
    }

    // 4. GỌI API với danh sách model dự phòng (giảm khả năng lỗi quota)
    // Danh sách ưu tiên: gemini-3.0-flash → 2.0-flash → 2.0-flash-lite
    $preferred = ['gemini-3.0-flash', 'gemini-2.0-flash', 'gemini-2.0-flash-lite'];
    $available = listAvailableModels($apiKey);
    $models = [];
    if (!empty($available)) {
        // Tên từ API thường có dạng 'models/gemini-3.0-flash'; so khớp bằng contains
        foreach ($preferred as $p) {
            foreach ($available as $a) {
                if (stripos($a, $p) !== false) {
                    // Lấy phần sau 'models/' để gọi generateContent
                    $models[] = preg_replace('/^models\//', '', $a);
                    break;
                }
            }
        }
    }
    // Nếu không lấy được danh sách, dùng mặc định
    if (empty($models)) {
        $models = $preferred;
    }
    $lastError = null;
    foreach ($models as $m) {
        $res = callGemini($apiKey, $m, $prompt);
        if ($res['curlError']) {
            $lastError = 'Lỗi kết nối: ' . $res['curlError'];
            continue;
        }
        $decoded = json_decode($res['body'], true);

        // Thành công
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $decoded['candidates'][0]['content']['parts'][0]['text'];
            echo json_encode(['success' => true, 'content' => $content, 'model' => $res['model']]);
            exit;
        }

        // Lỗi: lưu lại message để xử lý tiếp
        $errMsg = $decoded['error']['message'] ?? 'Không nhận được phản hồi từ AI';
        $errStatus = $decoded['error']['status'] ?? null;
        $retryInfo = $decoded['error']['details'][0]['retryDelay'] ?? null; // đôi khi có thông tin retry

        // Nếu bị quota/RESOURCE_EXHAUSTED, thử model tiếp theo
        if ($errStatus === 'RESOURCE_EXHAUSTED' || stripos($errMsg, 'Quota exceeded') !== false) {
            $lastError = $errMsg . ($retryInfo ? (". Vui lòng thử lại sau " . $retryInfo) : '');
            continue;
        }

        // Nếu model không tồn tại/không hỗ trợ, bỏ qua và thử model khác
        if ($errStatus === 'NOT_FOUND' || stripos($errMsg, 'not found') !== false || stripos($errMsg, 'not supported') !== false) {
            $lastError = $errMsg;
            continue;
        }

        // Các lỗi khác: trả về ngay
        echo json_encode(['success' => false, 'message' => $errMsg]);
        exit;
    }

    // Nếu đến đây: tất cả model đều lỗi/quota → trả về fallback cục bộ
    $fallback = generateFallbackDescription($productName);
    echo json_encode([
        'success' => true,
        'content' => $fallback,
        'fallback' => true,
        'message' => $lastError ?: 'Đã dùng mô tả mẫu do hết quota. Bạn có thể bật billing để dùng AI.'
    ]);
}
?>