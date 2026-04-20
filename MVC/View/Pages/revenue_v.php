<link rel="stylesheet" href="/web_qlsp/Public/Css/revenue.css?v=1.1">
<link rel="stylesheet" href="/web_qlsp/Public/Css/chart.css?v=1.1">

<div id="full-page-loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    <h5 class="mt-3 text-muted">Đang phân tích dữ liệu...</h5>
</div>

<div id="main-content" style="display: none;">

    <form id="formFilterRevenue">
        <div class="revenue_two">
            <div class="time-wrapper">
                <label class="time-label">Thời gian lọc</label>
                <div class="time-inputs">
                    <input type="date" class="date-input" id="fromDate" max="<?= date('Y-m-d'); ?>">
                    <span class="separator">đến</span>
                    <input type="date" class="date-input" id="toDate" max="<?= date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i></button>
                <button type="button" class="btn-reset" onclick="resetFilters()"><i class="fa-solid fa-arrow-rotate-left"></i></button>
                <button type="button" class="btn btn-success" onclick="exportExcel()">
                    <i class="fas fa-file-excel me-2"></i> Xuất Excel
                </button>
            </div>
        </div>
    </form>

    <div class="revenue_one">
        <div class="revenue__item">
            <p class="text-sm text-gray-500 font-medium">TỔNG DOANH THU</p>
            <h3 class="text-2xl font-bold text-gray-800 mt-1" id="lbl_revenue">0đ</h3>
            <p class="text-xs text-gray-400 mt-2 italic">Thực nhận (Đã trừ khuyến mãi)</p>
        </div>
        <div class="revenue__item">
            <p class="text-sm text-gray-500 font-medium">TỔNG VỐN TỒN KHO</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1" id="lbl_stock_capital">0đ</h3>
            <p class="text-xs text-gray-400 mt-2">Giá trị hàng chưa bán</p>
        </div>
        <div class="revenue__item highlight-green">
            <p class="text-sm text-green-700 font-bold">LỢI NHUẬN THỰC TẾ</p>
            <h3 class="text-2xl font-bold text-green-800 mt-1" id="lbl_profit">0đ</h3>
            <p class="text-xs text-green-600 mt-2 pill-green">Doanh thu - Giá vốn hàng bán</p>
        </div>
        <div class="revenue__item">
            <p class="text-sm text-gray-500 font-medium">SỐ LƯỢNG ĐÃ BÁN</p>
            <h3 class="text-2xl font-bold text-orange-500 mt-1" id="lbl_sold_qty">0</h3>
            <p class="text-xs text-gray-400 mt-2">Sản phẩm bán thành công</p>
        </div>
    </div>

    <style>
        .analytics-container { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #334155; }
        .analytics-wrapper { display: flex; gap: 24px; flex-wrap: wrap; margin-top: 20px; align-items: flex-start; }
        .chart-section, .table-section { background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #f1f5f9; flex: 1; min-width: 350px; }
        .block-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid #f8fafc; }
        .block-title { font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px; }
        .bar-chart { display: flex; align-items: flex-end; justify-content: space-around; gap: 12px; height: 320px; padding-top: 30px; }
        .bar-item { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; position: relative; }
        .bar-wrapper { width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; }
        .bar { width: 45px; background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); border-radius: 6px 6px 0 0; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); position: relative; min-height: 4px; }
        .bar:hover { background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%); transform: scaleY(1.02); }
        .bar-value { position: absolute; top: -24px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px; white-space: nowrap; opacity: 0; transition: opacity 0.3s; }
        .bar:hover .bar-value { opacity: 1; top: -30px;}
        .product-label-chart { margin-top: 12px; font-size: 11px; color: #64748b; text-align: center; height: 32px; line-height: 1.3; overflow: hidden; font-weight: 500; }
        .table-scroll-container { max-height: 320px; overflow-y: auto; padding-right: 5px; }
        .table-scroll-container::-webkit-scrollbar { width: 6px; }
        .table-scroll-container::-webkit-scrollbar-track { background: #f1f5f9; }
        .table-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .profit-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
        .profit-table th { position: sticky; top: 0; background: #fff; z-index: 10; text-align: left; padding: 10px 12px; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #e2e8f0; }
        .profit-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
        .profit-table tr:last-child td { border-bottom: none; }
        .profit-table tr:hover td { background-color: #f8fafc; }
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .fw-bold { font-weight: 600; }
        .badge-id { background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; color: #475569; font-weight: 700; border: 1px solid #e2e8f0; }
        .badge-qty { background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .money-highlight { color: #059669; font-weight: 700; font-size: 0.95rem; }
        @media (max-width: 768px) { .chart-section, .table-section { flex: 100%; min-width: 100%; } .bar { width: 30px; } }
    </style>

    <div class="section_two analytics-wrapper">
        <div class="chart-section">
            <div class="block-header">
                <div style="background: #eff6ff; padding: 8px; border-radius: 8px; color: #3b82f6;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="block-title">Top 5 Lợi Nhuận</h2>
            </div>
            <div class="bar-chart" id="chart_top_profit"></div>
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
                    <tbody id="table_all_profit"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="box-analysis box-product mt-4">
        <div class="box-header"><h4 class="title">Phân tích thuộc tính hàng bán</h4></div>
        <div class="box-content split-grid" style="display: flex; gap: 40px;">
            <div class="sub-col" style="flex: 1;">
                <span class="label-muted">TỶ TRỌNG THEO SIZE</span>
                <div class="progress-list" style="margin-top: 15px;" id="list_size"></div>
            </div>
            <div class="sub-col border-left" style="flex: 1; border-left: 1px solid #e2e8f0; padding-left: 40px;">
                <span class="label-muted">TỶ TRỌNG THEO MÀU</span>
                <div class="color-boxes" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;" id="list_color"></div>
            </div>
        </div>
    </div>

    <div class="dashboard-overview-row mt-4">
        <div class="overview-card">
            <div class="ov-header">
                <svg width="24" height="24" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h3 class="ov-title">Tình trạng đơn hàng</h3>
            </div>
            <div class="card-inner-flex" id="box_order_stats"></div>
        </div>

        <div class="overview-card">
            <div class="ov-header">
                <svg width="24" height="24" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <h3 class="ov-title">Kênh thanh toán</h3>
            </div>
            <div class="card-inner-flex" style="margin-top: 0px;" id="box_payment_stats"></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4 mx-2">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list me-2"></i>Danh sách đơn hàng chi tiết</h5>
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
                    <tbody id="table_orders_list"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/revenue';

    // Hàm tiện ích
    const formatNum = (num) => new Intl.NumberFormat('vi-VN').format(num || 0);
    const formatMoney = (amount) => formatNum(amount) + 'đ';
    function formatDateStr(dateStr) {
        if (!dateStr) return { d: '', t: '' };
        const p = dateStr.split(/[- :]/);
        if(p.length >= 5) return { d: `${p[2]}/${p[1]}/${p[0]}`, t: `${p[3]}:${p[4]}` };
        return { d: dateStr, t: '' };
    }

    // 1. GỌI API LẤY DỮ LIỆU
    function loadData() {
        document.getElementById('full-page-loading').style.display = 'block';
        document.getElementById('main-content').style.display = 'none';

        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        
        let url = `${API_BASE}/api_get_data`;
        if (from || to) url += `?from_date=${from}&to_date=${to}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    renderData(res.data);
                    
                    document.getElementById('full-page-loading').style.display = 'none';
                    document.getElementById('main-content').style.display = 'block';

                    // Re-init DataTable với config ngôn ngữ và phân trang mũi tên
                    if ($.fn.DataTable.isDataTable('#ordersTable')) {
                        $('#ordersTable').DataTable().destroy();
                    }
                    $('#ordersTable').DataTable({
                        language: { 
                            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json",
                            paginate: {
                                previous: '<i class="fa-solid fa-chevron-left"></i>',
                                next: '<i class="fa-solid fa-chevron-right"></i>'
                            }
                        },
                        pageLength: 10,
                        order: [[1, "desc"]]
                    });
                }
            })
            .catch(err => {
                Swal.fire('Lỗi', 'Không thể kết nối đến máy chủ', 'error');
                console.error(err);
            });
    }

    document.addEventListener("DOMContentLoaded", loadData);

    // 2. VẼ DỮ LIỆU LÊN GIAO DIỆN
    function renderData(data) {
        // --- 4 Ô Tổng quan ---
        document.getElementById('lbl_revenue').textContent = formatMoney(data.stats.revenue);
        document.getElementById('lbl_stock_capital').textContent = formatMoney(data.stats.stock_capital);
        document.getElementById('lbl_profit').textContent = formatMoney(data.stats.profit);
        document.getElementById('lbl_sold_qty').textContent = formatNum(data.stats.sold_quantity);

        // --- Biểu đồ Top Lợi Nhuận ---
        const topChartBox = document.getElementById('chart_top_profit');
        let maxVal = 0;
        data.top_products_profit.forEach(p => { if (p.total_profit > maxVal) maxVal = p.total_profit; });
        
        let chartHtml = '';
        if (data.top_products_profit.length > 0) {
            data.top_products_profit.forEach(p => {
                let h = maxVal > 0 ? (p.total_profit / maxVal) * 100 : 0;
                h = Math.max(2, h); // Tối thiểu 2%
                const valK = formatNum(Math.round(p.total_profit / 1000)) + 'K';
                const shortName = p.product_name ? p.product_name.substring(0, 12) + (p.product_name.length > 12 ? '..' : '') : 'SP';
                
                chartHtml += `
                    <div class="bar-item">
                        <div class="bar-wrapper">
                            <div class="bar" style="height: ${h}%;">
                                <span class="bar-value">${valK}</span>
                            </div>
                        </div>
                        <label class="product-label-chart" title="${p.product_name}">${shortName}</label>
                    </div>`;
            });
        } else {
            chartHtml = `<div style="width:100%; text-align:center; padding-top:100px; color:#94a3b8;"><i class="fa-solid fa-chart-simple" style="font-size: 40px; margin-bottom: 10px; display:block;"></i>Chưa có dữ liệu</div>`;
        }
        topChartBox.innerHTML = chartHtml;

        // --- Bảng All Products Profit ---
        const tableProfit = document.getElementById('table_all_profit');
        let tblHtml = '';
        if (data.all_products_profit.length > 0) {
            data.all_products_profit.forEach(p => {
                const shortName = p.product_name ? p.product_name.substring(0, 25) + (p.product_name.length > 25 ? '...' : '') : 'Unknown';
                tblHtml += `
                    <tr>
                        <td><span class="badge-id">#${p.product_id || p.id || '?'}</span></td>
                        <td><span class="fw-bold" style="display:block; font-size: 0.9rem;">${shortName}</span></td>
                        <td class="text-center"><span class="badge-qty">${formatNum(p.total_sold_qty)}</span></td>
                        <td class="text-end"><span class="money-highlight">${formatNum(p.total_profit)}</span></td>
                    </tr>`;
            });
        } else {
            tblHtml = `<tr><td colspan="4" class="text-center" style="padding: 40px; color: #94a3b8;">Không có dữ liệu đơn hàng hoàn thành.</td></tr>`;
        }
        tableProfit.innerHTML = tblHtml;

        // --- Phân tích Size & Màu ---
        let totalS = 0; data.stats_size.forEach(s => totalS += parseInt(s.tong_so_luong));
        let sizeHtml = '';
        if (data.stats_size.length > 0) {
            data.stats_size.forEach(s => {
                let pct = totalS > 0 ? Math.round((s.tong_so_luong / totalS) * 100) : 0;
                sizeHtml += `
                    <div class="p-item" style="margin-bottom: 12px;">
                        <div class="p-info" style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span>Size ${s.size}</span> <strong>${pct}% (${s.tong_so_luong})</strong>
                        </div>
                        <div class="p-track" style="background: #f1f5f9; height: 8px; border-radius: 4px;">
                            <div class="p-bar" style="width: ${pct}%; height: 100%; background: #4f46e5; border-radius: 4px;"></div>
                        </div>
                    </div>`;
            });
        } else sizeHtml = '<p class="text-muted" style="padding: 20px 0;">Không có dữ liệu size</p>';
        document.getElementById('list_size').innerHTML = sizeHtml;

        let totalC = 0; data.stats_color.forEach(c => totalC += parseInt(c.tong_so_luong));
        let colorHtml = '';
        const mColor = {'Đen':'#000','Trắng':'#fff','Nâu':'#92400e','Đỏ':'#ef4444','Hồng':'#ec4899','Xám':'#6b7280'};
        if (data.stats_color.length > 0) {
            data.stats_color.forEach(c => {
                let pct = totalC > 0 ? Math.round((c.tong_so_luong / totalC) * 100) : 0;
                let bg = mColor[c.color] || '#cbd5e1';
                colorHtml += `
                    <div class="c-card" style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 10px; border-radius: 6px;">
                        <div class="c-swatch" style="width: 14px; height: 14px; background: ${bg}; border: 1px solid #e2e8f0; border-radius: 3px;"></div>
                        <span style="font-size: 13px; flex: 1;">${c.color}</span>
                        <strong style="font-size: 12px;">${pct}%</strong>
                    </div>`;
            });
        } else colorHtml = '<p class="text-muted" style="padding: 20px 0;">Không có dữ liệu màu sắc</p>';
        document.getElementById('list_color').innerHTML = colorHtml;

        // --- Order Stats ---
        const os = data.order_stats || {};
        const pending = parseInt(os.pending || 0); const confirmed = parseInt(os.confirmed || 0);
        const shipping = parseInt(os.shipping || 0); const completed = parseInt(os.completed || 0);
        const cancelled = parseInt(os.cancelled || 0); const totalOs = parseInt(os.total || 0);
        
        const pctSuccess = totalOs > 0 ? ((completed / totalOs) * 100).toFixed(1) : 0;
        const pctCancel = totalOs > 0 ? ((cancelled / totalOs) * 100).toFixed(1) : 0;
        const orderGradient = `conic-gradient(#16a34a 0% ${pctSuccess}%, #dc2626 ${pctSuccess}% ${parseFloat(pctSuccess)+parseFloat(pctCancel)}%, #e2e8f0 ${parseFloat(pctSuccess)+parseFloat(pctCancel)}% 100%)`;

        document.getElementById('box_order_stats').innerHTML = `
            <div class="content-left-col">
                <table class="mini-table">
                    <tr><td>Chờ xử lý</td><td class="text-end font-bold">${pending.toString().padStart(2, '0')}</td></tr>
                    <tr><td>Đã xác nhận</td><td class="text-end font-bold">${confirmed.toString().padStart(2, '0')}</td></tr>
                    <tr><td>Đang giao</td><td class="text-end font-bold">${shipping.toString().padStart(2, '0')}</td></tr>
                    <tr><td style="color:#16a34a">Hoàn thành</td><td class="text-end font-bold text-success">${completed.toString().padStart(2, '0')}</td></tr>
                    <tr><td style="color:#dc2626">Đã hủy</td><td class="text-end font-bold text-danger">${cancelled.toString().padStart(2, '0')}</td></tr>
                </table>
            </div>
            <div class="content-right-col">
                <div class="donut-chart" style="background: ${orderGradient};">
                    <div class="donut-inner">
                        <span class="chart-total-label">TỔNG</span><span class="chart-total-val">${totalOs}</span>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><span class="dot" style="background:#16a34a"></span>Ok</div>
                    <div class="legend-item"><span class="dot" style="background:#dc2626"></span>Hủy</div>
                </div>
            </div>`;

        // --- Payment Stats ---
        let totalPay = 0; let codData = {qty:0, revenue:0}; let vnData = {qty:0, revenue:0};
        data.payment_stats.forEach(p => {
            totalPay += parseInt(p.qty);
            if(p.payment_method.toLowerCase() == 'cod') codData = p;
            if(p.payment_method.toLowerCase() == 'vnpay') vnData = p;
        });

        const pctCod = totalPay > 0 ? ((codData.qty / totalPay) * 100).toFixed(1) : 0;
        const payGradient = `conic-gradient(#10b981 0% ${pctCod}%, #005ba3 ${pctCod}% 100%)`;

        document.getElementById('box_payment_stats').innerHTML = `
            <div class="content-left-col pay-list-vertical">
                <div class="pay-row-compact">
                    <div class="pay-icon-sm" style="background:#d1fae5; color:#059669;"><i class="fa-solid fa-truck-fast"></i></div>
                    <div class="pay-detail-sm"><span class="pay-lbl">COD</span><div class="pay-val"><span>${codData.qty} đơn</span><span style="font-weight:700; color:#10b981">${formatNum(Math.round(codData.revenue/1000))}K</span></div></div>
                </div>
                <div class="pay-row-compact mt-2">
                    <div class="pay-icon-sm" style="background:#e0f2fe; color:#0284c7;"><i class="fa-solid fa-qrcode"></i></div>
                    <div class="pay-detail-sm"><span class="pay-lbl">VNPAY</span><div class="pay-val"><span>${vnData.qty} đơn</span><span style="font-weight:700; color:#005ba3">${formatNum(Math.round(vnData.revenue/1000))}K</span></div></div>
                </div>
            </div>
            <div class="content-right-col">
                <div class="donut-chart" style="background: ${payGradient};">
                    <div class="donut-inner">
                        <span class="chart-total-label">TỔNG ĐƠN</span><span class="chart-total-val">${totalPay}</span>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><span class="dot" style="background:#10b981"></span>COD</div>
                    <div class="legend-item"><span class="dot" style="background:#005ba3"></span>VNPay</div>
                </div>
            </div>`;

        // --- Bảng Orders List Chi Tiết ---
        let ordersHtml = '';
        data.orders_list.forEach(r => {
            const total = parseFloat(r.total_money);
            const cost = parseFloat(r.total_cost || 0);
            const profit = total - cost;
            const margin = total > 0 ? ((profit / total) * 100).toFixed(1) : 0;
            
            const profClass = profit > 0 ? 'text-success' : (profit < 0 ? 'text-danger' : 'text-muted');
            let mBadge = '';
            if (profit < 0) mBadge = 'bg-danger text-white';
            else if (margin >= 30) mBadge = 'bg-success text-white';
            else if (margin >= 10) mBadge = 'bg-warning text-dark';
            else mBadge = 'bg-danger bg-opacity-75 text-white';

            let stClass = '', stText = '';
            switch(r.status) {
                case 'completed': stClass = 'bg-success bg-opacity-10 text-success'; stText = 'Hoàn thành'; break;
                case 'shipping': stClass = 'bg-primary bg-opacity-10 text-primary'; stText = 'Đang giao'; break;
                case 'confirmed': stClass = 'bg-info bg-opacity-10 text-info'; stText = 'Đã xác nhận'; break;
                case 'cancelled': stClass = 'bg-danger bg-opacity-10 text-danger'; stText = 'Đã hủy'; break;
                default: stClass = 'bg-secondary bg-opacity-10 text-secondary'; stText = 'Chờ xử lý';
            }

            const dt = formatDateStr(r.created_at);

            ordersHtml += `
                <tr>
                    <td><div class="d-flex flex-column"><span class="fw-bold text-primary">#${r.id}</span><span class="small fw-bold text-dark">${r.customer_name}</span><span class="small text-muted" style="font-size: 0.75rem;">${r.customer_phone}</span></div></td>
                    <td class="text-center"><span class="d-block">${dt.d}</span><small class="text-muted">${dt.t}</small></td>
                    <td class="text-end fw-bold">${formatNum(total)}đ</td>
                    <td class="text-end text-muted">${formatNum(cost)}đ</td>
                    <td class="text-end fw-bold ${profClass}">${formatNum(profit)}đ</td>
                    <td class="text-center"><span class="badge ${mBadge}" style="font-weight: 500; font-size: 0.75rem; min-width: 50px;">${margin}%</span></td>
                    <td class="text-center"><span class="badge bg-light text-dark border border-secondary border-opacity-25">${(r.payment_method || '').toUpperCase()}</span></td>
                    <td class="text-center"><span class="badge ${stClass} px-2 py-1">${stText}</span></td>
                </tr>`;
        });
        document.getElementById('table_orders_list').innerHTML = ordersHtml;
    }

    // 3. TÌM KIẾM & RESET
    document.getElementById('formFilterRevenue').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    function resetFilters() {
        document.getElementById('fromDate').value = '';
        document.getElementById('toDate').value = '';
        loadData();
    }

    // 4. XUẤT EXCEL
    function exportExcel() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        let url = `${API_BASE}/export_excel`;
        if (from || to) url += `?from_date=${from}&to_date=${to}`;
        window.location.href = url;
    }
</script>