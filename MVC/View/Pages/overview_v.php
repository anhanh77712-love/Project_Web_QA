<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="cursor: pointer;" onclick="window.location.href='/web_qlsp/revenue'">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">DOANH THU THÁNG</p>
                    <h4 class="fw-bold mb-0 text-success" id="val_monthly_revenue">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </h4>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                    <i class="fas fa-coins fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="cursor: pointer;" onclick="window.location.href='/web_qlsp/orders?status=pending'">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">ĐƠN HÀNG MỚI</p>
                    <h4 class="fw-bold mb-0 text-primary" id="val_pending_orders">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </h4>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <i class="fas fa-shopping-bag fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="cursor: pointer;" onclick="window.location.href='/web_qlsp/users'">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">KHÁCH HÀNG</p>
                    <h4 class="fw-bold mb-0 text-warning" id="val_total_customers">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </h4>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                    <i class="fas fa-users fa-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="cursor: pointer;" onclick="window.location.href='/web_qlsp/product_list'">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">SẢN PHẨM</p>
                    <h4 class="fw-bold mb-0 text-danger" id="val_total_products">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </h4>
                </div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                    <i class="fas fa-tshirt fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">Biểu đồ doanh thu</span>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" id="revFrom" class="form-control form-control-sm" style="width: 140px;">
                    <span class="text-muted small">đến</span>
                    <input type="date" id="revTo" class="form-control form-control-sm" style="width: 140px;">
                    <button class="btn btn-sm btn-dark" onclick="applyRevenueFilters()">Lọc</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadRevenueChart()">7 Ngày qua</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
                Trạng thái đơn hàng
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const API_BASE = '/web_qlsp/overview';

    // Hàm format tiền tệ
    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    const formatNumber = (num) => new Intl.NumberFormat('vi-VN').format(num || 0);

    // ==========================================
    // 1. TẢI SỐ LIỆU TỔNG QUAN & BIỂU ĐỒ TRÒN
    // ==========================================
    function loadSummaryData() {
        fetch(`${API_BASE}/api_get_summary`)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const data = res.data;
                    // Điền số liệu vào 4 card
                    document.getElementById('val_monthly_revenue').textContent = formatMoney(data.monthly_revenue);
                    document.getElementById('val_pending_orders').textContent = formatNumber(data.pending_orders);
                    document.getElementById('val_total_customers').textContent = formatNumber(data.total_customers);
                    document.getElementById('val_total_products').textContent = formatNumber(data.total_products);

                    // Vẽ biểu đồ tròn trạng thái đơn hàng
                    drawStatusChart(data.status_counts);
                }
            })
            .catch(err => console.error("Lỗi tải dữ liệu tổng quan:", err));
    }

    let statusChart = null;
    function drawStatusChart(statusData) {
        const statusCtx = document.getElementById('orderStatusChart');
        if (!statusCtx) return;

        if(statusChart) statusChart.destroy(); // Hủy biểu đồ cũ nếu có

        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Chờ xử lý', 'Đã xác nhận', 'Đang giao hàng', 'Hoàn thành', 'Đã hủy'],
                datasets: [{
                    data: statusData.length > 0 ? statusData : [0,0,0,0,0],
                    backgroundColor: [
                        'rgb(255, 205, 86)', 'rgb(54, 162, 235)', 'rgb(75, 192, 192)', 
                        'rgb(75, 192, 75)', 'rgb(255, 99, 132)'
                    ]
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed || 0;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} đơn (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ==========================================
    // 2. TẢI VÀ VẼ BIỂU ĐỒ DOANH THU (ĐƯỜNG)
    // ==========================================
    let revenueChart = null;

    function drawRevenueChart(labels, data) {
        const revenueCtx = document.getElementById('revenueChart');
        if(!revenueCtx) return;

        if (revenueChart) revenueChart.destroy();
        
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4, fill: true, pointRadius: 5, pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return 'Doanh thu: ' + formatMoney(context.parsed.y); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    }

    function loadRevenueChart(from = '', to = '') {
        let url = `${API_BASE}/api_get_revenue`;
        if (from && to) {
            url += `?from=${from}&to=${to}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    drawRevenueChart(data.labels, data.values);
                }
            })
            .catch(err => console.error("Lỗi tải biểu đồ doanh thu:", err));
    }

    function applyRevenueFilters() {
        const from = document.getElementById('revFrom').value;
        const to = document.getElementById('revTo').value;
        if (!from || !to) {
            alert('Vui lòng chọn đầy đủ ngày bắt đầu và kết thúc'); return;
        }
        if (new Date(from) > new Date(to)) {
            alert('Ngày bắt đầu phải trước hoặc bằng ngày kết thúc'); return;
        }
        loadRevenueChart(from, to);
    }

    // Khởi chạy ngay khi trang load xong
    document.addEventListener('DOMContentLoaded', () => {
        loadSummaryData();
        loadRevenueChart(); // Mặc định tải 7 ngày qua
    });

    // Đồng bộ max/min cho 2 ô input ngày
    const f = document.getElementById('revFrom');
    const t = document.getElementById('revTo');
    if (f && t) {
        const sync = () => {
            if (f.value) t.min = f.value; else t.removeAttribute('min');
            if (t.value) f.max = t.value; else f.removeAttribute('max');
        };
        f.addEventListener('change', sync);
        t.addEventListener('change', sync);
    }
</script>