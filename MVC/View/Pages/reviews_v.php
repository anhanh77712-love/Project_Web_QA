<div id="product-list-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Quản lý Đánh giá</h4>
            <p class="text-muted small mb-0">Tổng hợp đánh giá của khách hàng theo từng sản phẩm</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form class="d-flex align-items-center" id="formSearch">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="txtSearch" class="form-control border-start-0" placeholder="Tìm tên sản phẩm..." />
                </div>
                <button type="submit" class="btn btn-primary ms-2 shadow-sm">Tìm</button>
            </form>
            
            <button type="button" onclick="loadAdminData()" class="btn btn-light border shadow-sm ms-2">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background-color: #f0f7ff;">
                        <tr>
                            <th width="8%" class="text-center py-3 text-secondary small fw-bold">ID SP</th>
                            <th width="40%" class="py-3 text-secondary small fw-bold">SẢN PHẨM</th>
                            <th width="15%" class="text-center py-3 text-secondary small fw-bold">SỐ LƯỢNG</th>
                            <th width="15%" class="text-center py-3 text-secondary small fw-bold">ĐIỂM TRUNG BÌNH</th>
                            <th width="22%" class="text-center py-3 text-secondary small fw-bold">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    
                    <tbody id="loading-skeleton-prod">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                        <tr>
                            <td class="text-center"><div class="skeleton mx-auto" style="width: 30px; height: 15px;"></div></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="skeleton rounded me-3" style="width: 45px; height: 45px;"></div>
                                    <div class="skeleton" style="width: 200px; height: 15px;"></div>
                                </div>
                            </td>
                            <td class="text-center"><div class="skeleton mx-auto" style="width: 40px; height: 40px; border-radius: 50%;"></div></td>
                            <td class="text-center"><div class="skeleton mx-auto" style="width: 80px; height: 20px;"></div></td>
                            <td class="text-center">
                                <div class="skeleton d-inline-block rounded" style="width: 100px; height: 35px;"></div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>

                    <tbody id="adminProductTable" style="display: none;"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="review-detail-section" class="d-none">
    <div class="mb-4">
        <button class="btn btn-sm btn-light border fw-bold px-3 mb-3 shadow-sm" onclick="backToProductList()" style="border-radius: 8px;">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </button>
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h3 class="fw-bold mb-1" id="detailTitle" style="letter-spacing: -0.5px;">Tên sản phẩm</h3>
                <p class="text-muted mb-0" id="detailSubtitle" style="font-size: 0.9rem;">Mã SP: #... | Tổng 0 đánh giá</p>
            </div>
            <a href="#" id="viewLiveBtn" target="_blank" class="btn btn-success fw-bold shadow-sm px-4" style="border-radius: 10px;">
                <i class="fas fa-external-link-alt me-2"></i>Xem trang Sản phẩm
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background-color: #f0f7ff;">
                        <tr>
                            <th width="7%" class="text-center py-3 text-secondary small fw-bold">ID</th>
                            <th width="20%" class="py-3 text-secondary small fw-bold">KHÁCH HÀNG</th>
                            <th width="15%" class="py-3 text-secondary small fw-bold">ĐÁNH GIÁ</th>
                            <th width="33%" class="py-3 text-secondary small fw-bold">NỘI DUNG</th>
                            <th width="10%" class="text-center py-3 text-secondary small fw-bold">TRẠNG THÁI</th>
                            <th width="15%" class="text-center py-3 text-secondary small fw-bold">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody id="adminReviewTable"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .star-warning { color: #ffc107; }
    .badge-pill { padding: 0.5em 1.2em; border-radius: 50rem; font-weight: 600; }
    .comment-box { 
        font-size: 14.5px; color: #333; line-height: 1.6; 
        background: #f8f9fa; padding: 10px 15px; border-radius: 10px; border: 1px solid #f1f1f1;
    }
    /* Skeleton Loading Effect */
    .skeleton { background: #eee; background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%); border-radius: 5px; background-size: 200% 100%; animation: 1.5s shine linear infinite; }
    @keyframes shine { to { background-position-x: -200%; } }
</style>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let groupedByProduct = {};

    document.addEventListener('DOMContentLoaded', function() {
        loadAdminData();
    });

    // Thêm hàm xử lý Search và Export dựa trên file users_v (Giữ logic cũ)
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault();
        const searchVal = document.getElementById('txtSearch').value.toLowerCase();
        renderProductTable(searchVal);
    });

    function exportExcel() {
        window.location.href = `/web_qlsp/reviews/export_excel`;
    }

    // --- LOGIC GỐC CỦA BẠN (KHÔNG THAY ĐỔI) ---

    function loadAdminData(keepOpenProductId = null) {
        document.getElementById('loading-skeleton-prod').style.display = 'table-row-group';
        document.getElementById('adminProductTable').style.display = 'none';

        fetch('/web_qlsp/reviews/api_get_all')
            .then(res => res.json())
            .then(data => {
                groupedByProduct = {};
                
                if(data.success && data.data && data.data.length > 0) {
                    data.data.forEach(r => {
                        if(!groupedByProduct[r.product_id]) {
                            groupedByProduct[r.product_id] = {
                                product_id: r.product_id,
                                product_name: r.product_name,
                                slug: r.slug,
                                thumbnail: r.thumbnail,
                                reviews: [],
                                total_score: 0
                            };
                        }
                        groupedByProduct[r.product_id].reviews.push(r);
                        groupedByProduct[r.product_id].total_score += parseInt(r.rating);
                    });
                    
                    renderProductTable();
                    
                    if(keepOpenProductId && groupedByProduct[keepOpenProductId]) {
                        openReviewDetail(keepOpenProductId);
                    } else if (keepOpenProductId) {
                        backToProductList();
                    }
                } else {
                    document.getElementById('adminProductTable').innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-comment-slash fs-1 mb-3 d-block opacity-25"></i>Hệ thống chưa có đánh giá nào.</td></tr>`;
                    document.getElementById('loading-skeleton-prod').style.display = 'none';
                    document.getElementById('adminProductTable').style.display = 'table-row-group';
                }
            })
            .catch(err => {
                console.error("Lỗi:", err);
                Swal.fire('Lỗi', 'Không thể tải dữ liệu từ máy chủ', 'error');
            });
    }

    function renderProductTable(filter = '') {
        const tbody = document.getElementById('adminProductTable');
        tbody.innerHTML = '';
        
        const products = Object.values(groupedByProduct).filter(p => p.product_name.toLowerCase().includes(filter));

        products.forEach(prod => {
            const count = prod.reviews.length;
            const avg = (prod.total_score / count).toFixed(1);
            const img = prod.thumbnail ? `/web_qlsp/Public/Picture/${prod.thumbnail}` : 'https://via.placeholder.com/45';
            
            let starsHtml = '';
            for(let i = 1; i <= 5; i++) {
                if(i <= Math.round(avg)) starsHtml += '<i class="fas fa-star text-warning"></i>';
                else starsHtml += '<i class="far fa-star text-muted opacity-50"></i>';
            }

            tbody.innerHTML += `
                <tr>
                    <td class="text-center fw-bold text-muted">#${prod.product_id}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${img}" class="rounded border shadow-sm me-3" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/45'">
                            <div class="fw-bold text-dark">${prod.product_name}</div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">${count}</span>
                    </td>
                    <td class="text-center">
                        <div class="fw-bold mb-1">${avg} / 5.0</div>
                        <div style="font-size: 12px;">${starsHtml}</div>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info me-1 shadow-sm fw-bold px-3" onclick="openReviewDetail(${prod.product_id})" style="border-radius: 8px;">
                            <i class="fas fa-tasks me-1"></i> Quản lý
                        </button>
                        <a href="/web_qlsp/product_detail?slug=${prod.slug}" target="_blank" class="btn btn-sm btn-outline-success shadow-sm" title="Xem trên Web">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </td>
                </tr>
            `;
        });

        document.getElementById('loading-skeleton-prod').style.display = 'none';
        tbody.style.display = 'table-row-group';
    }

    function openReviewDetail(productId) {
        const prod = groupedByProduct[productId];
        if(!prod) return;

        document.getElementById('product-list-section').classList.add('d-none');
        document.getElementById('review-detail-section').classList.remove('d-none');
        
        document.getElementById('detailTitle').innerText = prod.product_name;
        document.getElementById('detailSubtitle').innerHTML = `Mã sản phẩm: #${prod.product_id} | Tổng: ${prod.reviews.length} đánh giá`;
        document.getElementById('viewLiveBtn').href = `/web_qlsp/product_detail?slug=${prod.slug}`;

        const tbody = document.getElementById('adminReviewTable');
        tbody.innerHTML = '';

        prod.reviews.forEach(r => {
            const rate = parseInt(r.rating);
            let starHtml = '';
            for(let i=1; i<=5; i++) {
                starHtml += i <= rate ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted opacity-50"></i>';
            }

            const isActive = parseInt(r.status) === 1;
            const statusBadge = isActive 
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Đang hiển thị</span>' 
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">Đã ẩn</span>';
            
            const toggleBtnClass = isActive ? 'btn-outline-warning' : 'btn-outline-success';
            const toggleIcon = isActive ? 'fa-eye-slash' : 'fa-eye';

            tbody.innerHTML += `
                <tr>
                    <td class="text-center text-muted small">#${r.id}</td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 15px;">${r.full_name}</div>
                        <div class="text-muted small"><i class="far fa-clock me-1"></i>${r.review_date}</div>
                    </td>
                    <td><div class="mb-1">${starHtml}</div></td>
                    <td>
                        <div class="comment-box">${r.comment}</div>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm ${toggleBtnClass} me-1 shadow-sm" onclick="toggleStatus(${r.id}, ${prod.product_id})">
                            <i class="fas ${toggleIcon}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger shadow-sm" onclick="deleteReview(${r.id}, ${prod.product_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    function backToProductList() {
        document.getElementById('review-detail-section').classList.add('d-none');
        document.getElementById('product-list-section').classList.remove('d-none');
    }

    function toggleStatus(id, productId) {
        fetch(`/web_qlsp/reviews/toggle/${id}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Đã cập nhật', text: data.message, timer: 1000, showConfirmButton: false });
                    loadAdminData(productId);
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
    }

    function deleteReview(id, productId) {
        Swal.fire({
            title: 'Bạn chắc chắn chứ?',
            text: "Bình luận này sẽ bị xóa vĩnh viễn khỏi hệ thống!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Vâng, Xóa ngay!',
            cancelButtonText: 'Hủy bỏ'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/web_qlsp/reviews/delete/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire({ icon: 'success', title: 'Đã xóa!', text: data.message, timer: 1500, showConfirmButton: false });
                            loadAdminData(productId);
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }
</script>