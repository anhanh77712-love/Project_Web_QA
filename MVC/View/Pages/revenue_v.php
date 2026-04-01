

<link rel="stylesheet" href="/web_qlsp/Public/Css/revenue.css?v=1.1">
<link rel="stylesheet" href="/web_qlsp/Public/Css/chart.css?v=1.1">
<?php /** @var array $data */ ?>
<form method="POST" action="/web_qlsp/api/revenue_api/Get_data">
    <div class="revenue_two">
        <div class="time-wrapper">
        <label class="time-label">Thời gian lọc</label>
        <div class="time-inputs">
            <input type="date" class="date-input" id="fromDate" name="from_date" 
                max="<?= date('Y-m-d'); ?>"
                value="<?= isset($_GET['from']) ? htmlspecialchars($_GET['from']) : (isset($data['filter_from']) ? htmlspecialchars($data['filter_from']) : ''); ?>">
            
            <span class="separator">đến</span>
            
            <input type="date" class="date-input" id="toDate" name="to_date" 
                max="<?= date('Y-m-d'); ?>"
                value="<?= isset($_GET['to']) ? htmlspecialchars($_GET['to']) : (isset($data['filter_to']) ? htmlspecialchars($data['filter_to']) : ''); ?>">
        </div>
    </div>

        <div class="btn-group">
            <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i></button>
            <button type="button" class="btn-reset" onclick="window.location.href='/web_qlsp/api/revenue_api/Get_data'"><i class="fa-solid fa-arrow-rotate-left"></i></button>

            <button type="submit" class="btn btn-success" name="export" value="1">
                <i class="fas fa-file-excel me-2"></i> Xuất Excel
            </button>
            
        </div>
    </div>
</form>

<div class="revenue_one">
    
    <div class="revenue__item">
        <p class="text-sm text-gray-500 font-medium">TỔNG DOANH THU</p>
        <h3 class="text-2xl font-bold text-gray-800 mt-1">
            <?= number_format($data['stats']['revenue'], 0, ',', '.') ?>đ
        </h3>
        <p class="text-xs text-gray-400 mt-2 italic">Thực nhận (Đã trừ khuyến mãi)</p>
    </div>

    <div class="revenue__item">
        <p class="text-sm text-gray-500 font-medium">TỔNG VỐN TỒN KHO</p>
        <h3 class="text-2xl font-bold text-blue-600 mt-1">
            <?= number_format($data['stats']['stock_capital'], 0, ',', '.') ?>đ
        </h3>
        <p class="text-xs text-gray-400 mt-2">Giá trị hàng chưa bán</p>
    </div>

    <div class="revenue__item highlight-green">
        <p class="text-sm text-green-700 font-bold">LỢI NHUẬN THỰC TẾ</p>
        <h3 class="text-2xl font-bold text-green-800 mt-1">
            <?= number_format($data['stats']['profit'], 0, ',', '.') ?>đ
        </h3>
        <p class="text-xs text-green-600 mt-2 pill-green">Doanh thu - Giá vốn hàng bán</p>
    </div>

    <div class="revenue__item">
        <p class="text-sm text-gray-500 font-medium">SỐ LƯỢNG ĐÃ BÁN</p>
        <h3 class="text-2xl font-bold text-orange-500 mt-1">
            <?= number_format($data['stats']['sold_quantity'], 0, ',', '.') ?>
        </h3>
        <p class="text-xs text-gray-400 mt-2">Sản phẩm bán thành công</p>
    </div>

</div>

<?php
// Nhận dữ liệu
$list = $data['top_products_profit'] ?? [];

// Tìm lợi nhuận cao nhất để tính % chiều cao cột biểu đồ
$max_val = 0;
foreach ($list as $p) {
    $val = $p['total_profit'] ?? 0;
    if ($val > $max_val) {
        $max_val = $val;
    }
}
?>

<style>
    /* --- CSS MỚI: GIAO DIỆN ĐẸP HƠN --- */
    .analytics-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #334155;
    }

    /* Container chính: Flexbox chia cột */
    .analytics-wrapper {
        display: flex;
        gap: 24px; /* Khoảng cách giữa 2 khối */
        flex-wrap: wrap; /* Cho phép xuống dòng */
        margin-top: 20px;
        align-items: flex-start; /* Căn hàng bắt đầu từ trên */
    }
    
    /* Style chung cho 2 khối */
    .chart-section, .table-section {
        background: #fff;
        padding: 24px;
        border-radius: 12px; /* Bo góc mềm mại */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Bóng đổ hiện đại */
        border: 1px solid #f1f5f9;
        
        /* QUAN TRỌNG: Chia đều không gian */
        flex: 1; 
        min-width: 350px; /* Chiều rộng tối thiểu trước khi xuống dòng */
    }

    /* HEADER CỦA TỪNG KHỐI */
    .block-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f8fafc;
    }
    .block-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.5px;
    }

    /* --- KHỐI 1: BIỂU ĐỒ --- */
    .bar-chart {
        display: flex;
        align-items: flex-end;
        justify-content: space-around; /* Căn đều các cột */
        gap: 12px;
        height: 320px; /* Chiều cao cố định */
        padding-top: 30px;
    }
    .bar-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        position: relative;
    }
    .bar-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
    }
    .bar {
        width: 45px;
        background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); /* Màu gradient đẹp */
        border-radius: 6px 6px 0 0;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        min-height: 4px;
    }
    .bar:hover {
        background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%);
        transform: scaleY(1.02); /* Hiệu ứng nhún nhẹ */
    }
    .bar-value {
        position: absolute;
        top: -24px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        font-weight: 700;
        color: #2563eb;
        background: #eff6ff;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .bar:hover .bar-value { opacity: 1; top: -30px;} /* Hiện số khi hover */
    
    .product-label-chart {
        margin-top: 12px;
        font-size: 11px;
        color: #64748b;
        text-align: center;
        height: 32px;
        line-height: 1.3;
        overflow: hidden;
        font-weight: 500;
    }

    /* --- KHỐI 2: BẢNG --- */
    .table-scroll-container {
        max-height: 320px; /* Chiều cao bằng biểu đồ */
        overflow-y: auto; /* Có thanh cuộn nếu dài */
        padding-right: 5px;
    }
    /* Tùy chỉnh thanh cuộn cho đẹp */
    .table-scroll-container::-webkit-scrollbar { width: 6px; }
    .table-scroll-container::-webkit-scrollbar-track { background: #f1f5f9; }
    .table-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .profit-table {
        width: 100%;
        border-collapse: separate; 
        border-spacing: 0;
        font-size: 0.9rem;
    }
    .profit-table th {
        position: sticky; /* Cố định tiêu đề khi cuộn */
        top: 0;
        background: #fff;
        z-index: 10;
        text-align: left;
        padding: 10px 12px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .profit-table td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    .profit-table tr:last-child td { border-bottom: none; }
    .profit-table tr:hover td {
        background-color: #f8fafc; /* Hiệu ứng hover dòng */
    }

    /* Utilities */
    .text-center { text-align: center !important; }
    .text-end { text-align: right !important; }
    .fw-bold { font-weight: 600; }
    
    .badge-id {
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        color: #475569;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }
    .badge-qty {
        background: #eff6ff;
        color: #2563eb;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .money-highlight {
        color: #059669;
        font-weight: 700;
        font-size: 0.95rem;
    }

    /* RESPONSIVE: Mobile dưới 768px */
    @media (max-width: 768px) {
        .chart-section, .table-section {
            flex: 100%; /* Chiếm hết dòng */
            min-width: 100%;
        }
        .bar { width: 30px; } /* Cột nhỏ lại */
    }
</style>

<div class="section_two">
        
        <div class="chart-section">
            <div class="block-header">
                <div style="background: #eff6ff; padding: 8px; border-radius: 8px; color: #3b82f6;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="block-title">Top 5 Lợi Nhuận</h2>
            </div>
            
            <div class="bar-chart">
                <?php if (!empty($list)): ?>
                    <?php 
                    $chart_list = array_slice($list, 0, 5);
                    foreach ($chart_list as $item): 
                        $profit = $item['total_profit'] ?? 0;
                        $h = ($max_val > 0) ? ($profit / $max_val) * 100 : 0;
                        $h = max(2, $h); // Tối thiểu 2% để hiển thị
                    ?>
                        <div class="bar-item">
                            <div class="bar-wrapper">
                                <div class="bar" style="height: <?= $h ?>%;">
                                    <span class="bar-value"><?= number_format($profit / 1000, 0) ?>K</span>
                                </div>
                            </div>
                            <label class="product-label-chart" title="<?= $item['product_name'] ?? '' ?>">
                                <?= mb_strimwidth($item['product_name'] ?? 'SP', 0, 12, "..") ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="width:100%; text-align:center; padding-top:100px; color:#94a3b8;">
                        <i class="fa-solid fa-chart-simple" style="font-size: 40px; margin-bottom: 10px; display:block;"></i>
                        Chưa có dữ liệu
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-section">
    <div class="block-header">
        <div style="background: #ecfdf5; padding: 8px; border-radius: 8px; color: #10b981;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
        </div>
        <h2 class="block-title">Danh sách sản phẩm</h2>
    </div>

    <div class="table-scroll-container">
        <table class="profit-table">
            <thead>
                <tr>
                    <th width="15%">ID</th>
                    <th width="40%">Tên SP</th>
                    <th width="15%" class="text-center">SL</th>
                    <th width="30%" class="text-end">Lãi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // SỬ DỤNG DỮ LIỆU TỪ HÀM MỚI (Lấy tất cả)
                $full_list = $data['all_products_profit'] ?? []; 
                ?>

                <?php if (!empty($full_list)): ?>
                    <?php foreach ($full_list as $item): 
                         $profit = $item['total_profit'] ?? 0;
                         $qty = $item['total_sold_qty'] ?? 0; 
                         $p_id = $item['product_id'] ?? $item['id'] ?? '?';
                         $p_name = $item['product_name'] ?? 'Unknown';
                    ?>
                        <tr>
                            <td><span class="badge-id">#<?= $p_id ?></span></td>
                            <td>
                                <span class="fw-bold" style="display:block; font-size: 0.9rem;"><?= mb_strimwidth($p_name, 0, 25, "...") ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge-qty"><?= number_format($qty) ?></span>
                            </td>
                            <td class="text-end">
                                <span class="money-highlight">
                                    <?= number_format($profit, 0, ',', '.') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 40px; color: #94a3b8;">
                            Không có dữ liệu đơn hàng hoàn thành.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<?php
// Kiểm tra và lấy dữ liệu trực tiếp
$list_s = $stats_size ?? $data['stats_size'] ?? [];
$list_c = $stats_color ?? $data['stats_color'] ?? [];

// TÍNH TỔNG (Dùng ép kiểu (int) để chắc chắn không bị lỗi phép tính)
$total_s = 0;
foreach($list_s as $s) { $total_s += (int)$s['tong_so_luong']; }

$total_c = 0;
foreach($list_c as $c) { $total_c += (int)$c['tong_so_luong']; }
?>

<div class="box-analysis box-product">
    <div class="box-header">
        <h4 class="title">Phân tích thuộc tính hàng bán</h4>
    </div>
    <div class="box-content split-grid" style="display: flex; gap: 40px;">
        <div class="sub-col" style="flex: 1;">
            <span class="label-muted">TỶ TRỌNG THEO SIZE</span>
            <div class="progress-list" style="margin-top: 15px;">
                <?php if (!empty($list_s)): ?>
                    <?php foreach ($list_s as $item): 
                        $percent = ($total_s > 0) ? round(((int)$item['tong_so_luong'] / $total_s) * 100) : 0;
                    ?>
                    <div class="p-item" style="margin-bottom: 12px;">
                        <div class="p-info" style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span>Size <?= htmlspecialchars($item['size']) ?></span> 
                            <strong><?= $percent ?>% (<?= $item['tong_so_luong'] ?>)</strong>
                        </div>
                        <div class="p-track" style="background: #f1f5f9; height: 8px; border-radius: 4px;">
                            <div class="p-bar" style="width: <?= $percent ?>%; height: 100%; background: #4f46e5; border-radius: 4px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="padding: 20px 0;">Không có dữ liệu size (SQL có <?= count($list_s) ?> dòng)</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="sub-col border-left" style="flex: 1; border-left: 1px solid #e2e8f0; padding-left: 40px;">
            <span class="label-muted">TỶ TRỌNG THEO MÀU</span>
            <div class="color-boxes" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                <?php if (!empty($list_c)): ?>
                    <?php foreach ($list_c as $item): 
                        $c_percent = ($total_c > 0) ? round(((int)$item['tong_so_luong'] / $total_c) * 100) : 0;
                        $m_color = ['Đen'=>'#000','Trắng'=>'#fff','Nâu'=>'#92400e','Đỏ'=>'#ef4444','Hồng'=>'#ec4899','Xám'=>'#6b7280'];
                        $bg = $m_color[$item['color']] ?? '#cbd5e1';
                    ?>
                    <div class="c-card" style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 10px; border-radius: 6px;">
                        <div class="c-swatch" style="width: 14px; height: 14px; background: <?= $bg ?>; border: 1px solid #e2e8f0; border-radius: 3px;"></div>
                        <span style="font-size: 13px; flex: 1;"><?= htmlspecialchars($item['color']) ?></span>
                        <strong style="font-size: 12px;"><?= $c_percent ?>%</strong>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="padding: 20px 0;">Không có dữ liệu màu sắc</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?php
// --- 1. XỬ LÝ SỐ LIỆU ĐƠN HÀNG (Order Stats) ---
$os = $data['order_stats'] ?? [];
$filter_from = $data['filter_from'] ?? '';
$filter_to = $data['filter_to'] ?? '';

$pending   = (int)($os['pending'] ?? 0);
$confirmed = (int)($os['confirmed'] ?? 0);
$shipping  = (int)($os['shipping'] ?? 0);
$completed = (int)($os['completed'] ?? 0);
$cancelled = (int)($os['cancelled'] ?? 0);
$total_os  = (int)($os['total'] ?? 0);

// % Biểu đồ đơn hàng
$pct_success = ($total_os > 0) ? round(($completed / $total_os) * 100, 1) : 0;
$pct_cancel  = ($total_os > 0) ? round(($cancelled / $total_os) * 100, 1) : 0;

$order_gradient = "conic-gradient(
    #16a34a 0% {$pct_success}%, 
    #dc2626 {$pct_success}% " . ($pct_success + $pct_cancel) . "%, 
    #e2e8f0 " . ($pct_success + $pct_cancel) . "% 100%
)";

// --- 2. XỬ LÝ SỐ LIỆU THANH TOÁN (Payment Stats) ---
$payments = $data['payment_stats'] ?? [];
$total_orders_pay = 0;
// Map dữ liệu để dễ lấy
$data_pay_mapped = [];
foreach($payments as $p) { 
    $total_orders_pay += (int)$p['qty'];
    $data_pay_mapped[strtolower($p['payment_method'])] = $p;
}

// Lấy số liệu COD và VNPAY
$cod_data   = $data_pay_mapped['cod'] ?? ['qty'=>0, 'revenue'=>0];
$vnpay_data = $data_pay_mapped['vnpay'] ?? ['qty'=>0, 'revenue'=>0];

// Tính % cho biểu đồ thanh toán (Tỷ lệ COD)
$qty_cod = (int)$cod_data['qty'];
$pct_cod = ($total_orders_pay > 0) ? round(($qty_cod / $total_orders_pay) * 100, 1) : 0; // % COD
// Gradient: Xanh lá (COD) chạy từ 0 -> %COD, Xanh biển (VNPAY) chạy tiếp theo
$pay_gradient = "conic-gradient(
    #10b981 0% {$pct_cod}%, 
    #005ba3 {$pct_cod}% 100%
)";

// Cấu hình hiển thị
$pay_map = [
    'cod' => ['label' => 'COD', 'color' => '#10b981', 'bg_icon' => '#d1fae5', 'text_icon' => '#059669', 'icon' => '<i class="fa-solid fa-truck-fast"></i>'],
    'vnpay' => ['label' => 'VNPAY', 'color' => '#005ba3', 'bg_icon' => '#e0f2fe', 'text_icon' => '#0284c7', 'icon' => '<i class="fa-solid fa-qrcode"></i>']
];
?>

<div class="dashboard-overview-row">
    
    <div class="overview-card">
        <div class="ov-header">
            <svg width="24" height="24" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <h3 class="ov-title">Tình trạng đơn hàng</h3>
        </div>
        
        <div class="card-inner-flex">
            <div class="content-left-col">
                <table class="mini-table">
                    <tr><td>Chờ xử lý</td><td class="text-end font-bold"><?= sprintf("%02d", $pending) ?></td></tr>
                    <tr><td>Đã xác nhận</td><td class="text-end font-bold"><?= sprintf("%02d", $confirmed) ?></td></tr>
                    <tr><td>Đang giao</td><td class="text-end font-bold"><?= sprintf("%02d", $shipping) ?></td></tr>
                    <tr><td style="color:#16a34a">Hoàn thành</td><td class="text-end font-bold text-success"><?= sprintf("%02d", $completed) ?></td></tr>
                    <tr><td style="color:#dc2626">Đã hủy</td><td class="text-end font-bold text-danger"><?= sprintf("%02d", $cancelled) ?></td></tr>
                </table>
            </div>
            
            <div class="content-right-col">
                <div class="donut-chart" style="background: <?= $order_gradient ?>;">
                    <div class="donut-inner">
                        <span class="chart-total-label">TỔNG</span>
                        <span class="chart-total-val"><?= $total_os ?></span>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><span class="dot" style="background:#16a34a"></span>Ok</div>
                    <div class="legend-item"><span class="dot" style="background:#dc2626"></span>Hủy</div>
                </div>
            </div>
        </div>
    </div>

    <div class="overview-card">
        <div class="ov-header">
            <svg width="24" height="24" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            <h3 class="ov-title">Kênh thanh toán</h3>
        </div>

        <div class="card-inner-flex" style="margin-top: 0px;">
            <div class="content-left-col pay-list-vertical">
                <?php 
                $display_list = ['cod', 'vnpay'];
                foreach ($display_list as $method_code):
                    $cfg = $pay_map[$method_code];
                    
                    // Lấy dữ liệu
                    $item_data = ($method_code == 'cod') ? $cod_data : $vnpay_data;
                    $qty = (int)($item_data['qty'] ?? 0);
                    $rev = (float)($item_data['revenue'] ?? 0);
                    $percent = ($total_orders_pay > 0) ? round(($qty / $total_orders_pay) * 100, 1) : 0;
                ?>
                <div class="pay-row-compact">
                    <div class="pay-icon-sm" style="background: <?= $cfg['bg_icon'] ?>; color: <?= $cfg['text_icon'] ?>;">
                        <?= $cfg['icon'] ?>
                    </div>
                    <div class="pay-detail-sm">
                        <span class="pay-lbl"><?= $cfg['label'] ?></span>
                        <div class="pay-val">
                            <span><?= $qty ?> đơn</span>
                            <span style="font-weight:700; color:<?= $cfg['color'] ?>"><?= number_format($rev/1000, 0) ?>K</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="content-right-col">
                <div class="donut-chart" style="background: <?= $pay_gradient ?>;">
                    <div class="donut-inner">
                        <span class="chart-total-label">TỔNG ĐƠN</span>
                        <span class="chart-total-val"><?= $total_orders_pay ?></span>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><span class="dot" style="background:#10b981"></span>COD</div>
                    <div class="legend-item"><span class="dot" style="background:#005ba3"></span>VNPay</div>
                </div>
            </div>
        </div>
    </div>

</div>



<div class="card border-0 shadow-sm mt-4" style="margin-left: 10px; margin-right: 10px;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list me-2"></i>Danh sách đơn hàng chi tiết
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ordersTable" class="table table-hover align-middle w-100" style="font-size: 0.9rem;">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th style="min-width: 120px;">Mã đơn / Khách</th>
                        <th class="text-center">Ngày đặt</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Giá vốn</th>
                        <th class="text-end">Lợi nhuận</th>
                        <th class="text-center" title="Tỷ suất lợi nhuận">% Lãi</th>
                        <th class="text-center">Thanh toán</th>
                        <th class="text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($data['orders_list']) && mysqli_num_rows($data['orders_list']) > 0): ?>
                        <?php while ($row = mysqli_fetch_array($data['orders_list'])): 
                            // --- 1. TÍNH TOÁN LOGIC ---
                            $total = (float)$row['total_money'];
                            $cost  = (float)($row['total_cost'] ?? 0);
                            $profit = $total - $cost;
                            
                            // Tính % Tỷ suất lợi nhuận (Profit Margin)
                            // Công thức: (Lợi nhuận / Doanh thu) * 100
                            $margin = ($total > 0) ? round(($profit / $total) * 100, 1) : 0;
                            
                            // --- 2. XỬ LÝ MÀU SẮC GIAO DIỆN ---
                            // Màu cho số tiền lợi nhuận (Xanh = Lãi, Đỏ = Lỗ)
                            $profitTextClass = $profit > 0 ? 'text-success' : ($profit < 0 ? 'text-danger' : 'text-muted');

                            // Màu badge cho % (Lãi dày > 30%, Trung bình > 10%, Lãi mỏng/Lỗ < 10%)
                            if ($profit < 0) {
                                $marginBadgeClass = 'bg-danger text-white'; // Lỗ
                            } elseif ($margin >= 30) {
                                $marginBadgeClass = 'bg-success text-white'; // Lãi tốt
                            } elseif ($margin >= 10) {
                                $marginBadgeClass = 'bg-warning text-dark'; // Trung bình
                            } else {
                                $marginBadgeClass = 'bg-danger bg-opacity-75 text-white'; // Lãi mỏng
                            }

                            // Text trạng thái & Màu sắc badge trạng thái
                            $st = $row['status'];
                            $stClass = ''; $stText = '';
                            switch($st) {
                                case 'completed': $stClass = 'bg-success bg-opacity-10 text-success'; $stText = 'Hoàn thành'; break;
                                case 'shipping':  $stClass = 'bg-primary bg-opacity-10 text-primary'; $stText = 'Đang giao'; break;
                                case 'confirmed': $stClass = 'bg-info bg-opacity-10 text-info';       $stText = 'Đã xác nhận'; break;
                                case 'cancelled': $stClass = 'bg-danger bg-opacity-10 text-danger';   $stText = 'Đã hủy'; break;
                                default:          $stClass = 'bg-secondary bg-opacity-10 text-secondary'; $stText = 'Chờ xử lý';
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-primary">#<?= $row['id'] ?></span>
                                    <span class="small fw-bold text-dark"><?= $row['customer_name'] ?></span>
                                    <span class="small text-muted" style="font-size: 0.75rem;"><?= $row['customer_phone'] ?></span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="d-block"><?= date('d/m/Y', strtotime($row['created_at'])) ?></span>
                                <small class="text-muted"><?= date('H:i', strtotime($row['created_at'])) ?></small>
                            </td>

                            <td class="text-end fw-bold"><?= number_format($total, 0, ',', '.') ?>đ</td>
                            <td class="text-end text-muted"><?= number_format($cost, 0, ',', '.') ?>đ</td>
                            <td class="text-end fw-bold <?= $profitTextClass ?>">
                                <?= number_format($profit, 0, ',', '.') ?>đ
                            </td>

                            <td class="text-center">
                                <span class="badge <?= $marginBadgeClass ?>" style="font-weight: 500; font-size: 0.75rem; min-width: 50px;">
                                    <?= $margin ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border border-secondary border-opacity-25">
                                    <?= strtoupper($row['payment_method']) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge <?= $stClass ?> px-2 py-1">
                                    <?= $stText ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/web_qlsp/Public/Js/revenue.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>