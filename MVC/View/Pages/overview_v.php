<!-- THỐNG KÊ SỐ LIỆU -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="cursor: pointer;" onclick="window.location.href='/web_qlsp/revenue'">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">DOANH THU</p>
                    <h4 class="fw-bold mb-0 text-success">
                        <?php echo number_format($data['monthly_revenue'] ?? 0, 0, ',', '.'); ?>đ
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
                    <h4 class="fw-bold mb-0 text-primary">
                        <?php echo number_format($data['pending_orders'] ?? 0, 0, ',', '.'); ?>
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
                    <h4 class="fw-bold mb-0 text-warning">
                        <?php echo number_format($data['total_customers'] ?? 0, 0, ',', '.'); ?>
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
                    <h4 class="fw-bold mb-0 text-danger">
                        <?php echo number_format($data['total_products'] ?? 0, 0, ',', '.'); ?>
                    </h4>
                </div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                    <i class="fas fa-tshirt fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BIỂU ĐỒ -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">Biểu đồ doanh thu</span>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" id="revFrom" class="form-control form-control-sm" style="width: 170px;" value="<?php echo isset($data['rev_from']) ? htmlspecialchars($data['rev_from']) : ''; ?>">
                    <span class="text-muted small">đến</span>
                    <input type="date" id="revTo" class="form-control form-control-sm" style="width: 170px;" value="<?php echo isset($data['rev_to']) ? htmlspecialchars($data['rev_to']) : ''; ?>">
                    <button class="btn btn-sm btn-dark" onclick="applyRevenueFilters()">Lọc</button>

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
// Biểu đồ doanh thu
let revenueChart = null;
const revenueCtx = document.getElementById('revenueChart');

function createRevenueChart(labels, data) {
    if (revenueChart) {
        revenueChart.destroy();
    }
    
    if (revenueCtx) {
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
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
}

// Khởi tạo biểu đồ ban đầu
createRevenueChart(
    <?php echo $data['revenue_labels'] ?? '[]'; ?>,
    <?php echo $data['revenue_values'] ?? '[]'; ?>
);

// Cập nhật biểu đồ khi thay đổi khoảng thời gian
function updateRevenueChart() {
    const days = document.getElementById('revenuePeriod').value;
    
    fetch('/web_qlsp/overview/get_revenue_data?days=' + days)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                createRevenueChart(data.labels, data.values);
            } else {
                alert('Không thể tải dữ liệu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra');
        });
}

// Lọc theo khoảng ngày
function applyRevenueFilters() {
    const from = document.getElementById('revFrom').value;
    const to = document.getElementById('revTo').value;
    if (!from || !to) {
        alert('Vui lòng chọn đầy đủ ngày bắt đầu và kết thúc');
        return;
    }
    const dFrom = new Date(from);
    const dTo = new Date(to);
    if (dFrom > dTo) {
        alert('Ngày bắt đầu phải trước hoặc bằng ngày kết thúc');
        return;
    }
    const url = '/web_qlsp/overview/get_revenue_data?from=' + from + '&to=' + to;
    fetch(url)
        .then(resp => resp.json())
        .then(data => {
            if (data.success) {
                createRevenueChart(data.labels, data.values);
            } else {
                alert('Không thể tải dữ liệu');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Có lỗi xảy ra');
        });
}

// Sync min/max for better UX
(function(){
    const f = document.getElementById('revFrom');
    const t = document.getElementById('revTo');
    if (!f || !t) return;
    const sync = () => {
        if (f.value) t.min = f.value; else t.removeAttribute('min');
        if (t.value) f.max = t.value; else f.removeAttribute('max');
    };
    f.addEventListener('change', sync);
    t.addEventListener('change', sync);
    sync();
})();

// Biểu đồ trạng thái đơn hàng
const statusCtx = document.getElementById('orderStatusChart');
if (statusCtx) {
    const statusData = <?php echo $data['status_counts'] ?? '[0,0,0,0,0]'; ?>;
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xử lý', 'Đã xác nhận', 'Đang giao hàng', 'Hoàn thành', 'Đã hủy'],
            datasets: [{
                data: statusData,
                backgroundColor: [
                    'rgb(255, 205, 86)',
                    'rgb(54, 162, 235)',
                    'rgb(75, 192, 192)',
                    'rgb(75, 192, 75)',
                    'rgb(255, 99, 132)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' đơn (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}
</script>
