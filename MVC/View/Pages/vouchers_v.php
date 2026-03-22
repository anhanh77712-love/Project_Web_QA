

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Quản lý Mã giảm giá</h4>
</div>

<div class="toolbar-container d-flex align-items-center gap-2"> <form method="POST" action="/web_qlsp/vouchers/search"
          class="d-flex align-items-center flex-grow-1"> 

        <div class="search-wrapper me-auto">
            <i class="fas fa-search search-icon"></i>
             <input type="text"
                name="txtSearch"
                class="form-control form-search"
                placeholder="Tìm kiếm vouchers..."
                value="<?php echo isset($data['search']) ? $data['search'] : ''; ?>">
        </div>

        <div class="d-flex gap-2">
            
            <button type="submit" class="btn btn-dark-blue" name="btnTimkiem">
                <i class="fas fa-search"></i> Tìm
            </button>

            <button type="button" class="btn btn-light-gray" onclick="window.location.href='/web_qlsp/vouchers/reset'">
                <i class="fas fa-undo-alt"></i> Làm mới
            </button>

            <button type="submit" class="btn btn-green" name="btnXuatExcel">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </button>
            
            <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="fas fa-file-import"></i> Nhập Excel
            </button>

            <button type="button" class="btn btn-dark-blue" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
                <i class="fas fa-plus"></i> Thêm Voucher
            </button>
        </div>

    </form>

    <form method="GET" action="" class="d-inline-block m-0">
        <div class="input-group">
            <span class="input-group-text bg-light fw-bold text-secondary">
                <i class="fas fa-filter me-1"></i> Trạng thái
            </span>
            
            <select name="filter" class="form-select w-auto focus-ring-none" onchange="this.form.submit()" style="min-width: 130px;">
                <option value="all">Tất cả</option>
                <option value="running" <?= (isset($_GET['filter']) && $_GET['filter'] == 'running') ? 'selected' : '' ?>>Đang chạy</option>
                <option value="upcoming" <?= (isset($_GET['filter']) && $_GET['filter'] == 'upcoming') ? 'selected' : '' ?>>Sắp diễn ra</option>
                <option value="expired" <?= (isset($_GET['filter']) && $_GET['filter'] == 'expired') ? 'selected' : '' ?>>Đã kết thúc</option>
                <option value="empty" <?= (isset($_GET['filter']) && $_GET['filter'] == 'empty') ? 'selected' : '' ?>>Hết lượt</option>
                <option value="hidden" <?= (isset($_GET['filter']) && $_GET['filter'] == 'hidden') ? 'selected' : '' ?>>Đang ẩn (DB)</option>
            </select>
        </div>
    </form>

</div>

<div class="bg-white rounded-3 shadow-sm overflow-auto">
    <table class="table table-hover align-middle mb-0 text-nowrap">
        <thead class="bg-light">
            <tr>
                <th class="fw-bold ps-4">Mã Code</th>
                <th class="fw-bold text-center">Trạng thái</th> 
                <th class="fw-bold">Thời gian</th>
                <th class="fw-bold">Loại giảm</th>
                <th class="fw-bold">Giá trị</th>
                <th class="fw-bold">Điều kiện</th>
                <th class="fw-bold text-center">Đã dùng</th>
                <th class="fw-bold text-center">Còn lại</th>
                <th class="fw-bold text-end pe-4">Hành động</th>
            </tr>
        </thead>
        
        <tbody id="loading-skeleton">
            <?php for($i=0; $i<5; $i++): // Giả lập 5 dòng đang load ?>
            <tr>
                <td class="ps-4">
                    <div class="skeleton" style="width: 80px; height: 26px;"></div>
                </td>
                
                <td class="text-center">
                    <div class="skeleton" style="width: 100px; height: 24px; border-radius: 12px;"></div>
                </td>
                
                <td>
                    <div class="skeleton skeleton-text" style="width: 120px;"></div>
                    <div class="skeleton skeleton-text" style="width: 120px;"></div>
                </td>
                
                <td><div class="skeleton" style="width: 70px; height: 20px;"></div></td>
                
                <td><div class="skeleton" style="width: 50px; height: 20px;"></div></td>
                
                <td>
                    <div class="skeleton skeleton-text" style="width: 90px;"></div>
                    <div class="skeleton skeleton-text" style="width: 60px;"></div>
                </td>
                
                <td class="text-center"><div class="skeleton" style="width: 30px; height: 20px;"></div></td>
                
                <td class="text-center"><div class="skeleton" style="width: 30px; height: 20px;"></div></td>
                
                <td class="text-end pe-4">
                    <div class="skeleton skeleton-btn me-1"></div>
                    <div class="skeleton skeleton-btn"></div>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>

       <tbody id="actual-content" style="display: none;">
    <?php
    // 1. Lấy giá trị bộ lọc từ URL
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    if (isset($data['vouchers_list']) && mysqli_num_rows($data['vouchers_list']) > 0) {
        foreach ($data['vouchers_list'] as $v) {
         
            $con_lai = $v['usage_limit'] - $v['used_count'];
            $now = time();
            $start = strtotime($v['start_date']);
            $end = strtotime($v['end_date']);
            
            
            $status_badge = ''; 
            $status_text = ''; 
            $row_opacity = '';
            $real_status = ''; 
            // ƯU TIÊN 1: Kiểm tra tắt/bật trong DB (0 = Ẩn)
            if ($v['status'] == 0) {
                $real_status = 'hidden';
                $status_text = 'Đang ẩn'; 
                $status_badge = 'bg-secondary'; 
                $row_opacity = 'opacity-50';
            
            // ƯU TIÊN 2: Hết lượt (Chỉ check nếu status != 0)
            } elseif ($v['used_count'] >= $v['usage_limit']) {
                $real_status = 'empty';
                $status_text = 'Hết lượt'; 
                $status_badge = 'bg-dark'; 
                $row_opacity = 'opacity-75';
            
            // ƯU TIÊN 3: Đã kết thúc (Quá hạn)
            } elseif ($now > $end) {
                $real_status = 'expired';
                $status_text = 'Đã kết thúc'; 
                $status_badge = 'bg-danger'; 
                $row_opacity = 'opacity-50';
            
            // ƯU TIÊN 4: Sắp diễn ra
            } elseif ($now < $start) {
                $real_status = 'upcoming';
                $status_text = 'Sắp diễn ra'; 
                $status_badge = 'bg-warning text-dark';
            
            // CÒN LẠI: Đang chạy
            } else {
                $real_status = 'running';
                $status_text = 'Đang chạy'; 
                $status_badge = 'bg-success';
            }

            // --- QUAN TRỌNG: BỎ QUA NẾU KHÔNG KHỚP BỘ LỌC ---
            if ($filter != 'all' && $filter != $real_status) {
                continue; 
            }
    ?>
        <tr class="<?php echo $row_opacity; ?>">
            <td class="ps-4">
                <span class="badge bg-dark fs-6"><?php echo $v['code']; ?></span>
            </td>
            <td class="text-center">
                <span class="badge rounded-pill <?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
            </td>
            <td>
                <div class="d-flex flex-column text-muted" style="font-size: 13px;">
                    <span><i class="fas fa-play me-1 text-success"></i> <?php echo date('d/m/y H:i', $start); ?></span>
                    <span><i class="fas fa-stop me-1 text-danger"></i> <?php echo date('d/m/y H:i', $end); ?></span>
                </div>
            </td>
            <td><?php echo ($v['discount_type'] == 'percent') ? 'Theo %' : 'Tiền mặt'; ?></td>
            <td class="fw-bold text-primary">
                <?php echo ($v['discount_type'] == 'percent') ? $v['discount_value'] . '%' : number_format($v['discount_value']) . 'đ'; ?>
            </td>
            <td>
                <small>
                    Min: <b><?php echo number_format($v['min_order_value']); ?>đ</b>
                    <?php if($v['max_discount_amount'] > 0): echo '<br>Max: '.number_format($v['max_discount_amount']).'đ'; endif; ?>
                </small>
            </td>
            <td class="text-center"><?php echo $v['used_count']; ?></td>
            <td class="text-center">
                <span class="badge <?php echo ($con_lai > 0) ? 'bg-info' : 'bg-secondary'; ?>"><?php echo $con_lai; ?></span>
            </td>
            <td class="text-end pe-4">
                <button type="button" class="btn btn-sm btn-outline-primary border-0 me-1" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editVoucherModal"
                    data-id="<?php echo $v['id']; ?>"
                    data-code="<?php echo $v['code']; ?>"
                    data-usage-limit="<?php echo $v['usage_limit']; ?>"
                    data-start-date="<?php echo date('Y-m-d\TH:i', strtotime($v['start_date'])); ?>"
                    data-end-date="<?php echo date('Y-m-d\TH:i', strtotime($v['end_date'])); ?>"
                    data-value="<?php echo $v['discount_value']; ?>"
                    data-min-order="<?php echo $v['min_order_value']; ?>"
                    data-max-discount="<?php echo ($v['max_discount_amount'] > 0) ? $v['max_discount_amount'] : ''; ?>"
                    data-status="<?php echo $v['status']; ?>"
                    title="Sửa">
                    <i class="fas fa-edit"></i>
                </button>
                
                <button class="btn btn-sm btn-outline-danger border-0"
                    onclick="confirmDelete('/web_qlsp/vouchers/delete/<?php echo $v['id']; ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    <?php 
        } // Kết thúc foreach
    } else { 
        echo '<tr><td colspan="9" class="text-center py-4 text-muted">Chưa có mã giảm giá nào.</td></tr>';
    } 
    ?>
</tbody>
    </table>
</div>
<!-- ADD MODAL -->
<div class="modal fade custom-modal-style" id="addVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo mã giảm giá mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/web_qlsp/vouchers/add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control uppercase-input" placeholder="VD: SALE50" name="code" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới hạn số lượt dùng</label>
                            <input type="number" class="form-control" value="100" name="usage_limit">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả chương trình</label>
                        <textarea class="form-control" rows="2" placeholder="VD: Giảm giá ngày đôi, áp dụng cho toàn bộ sản phẩm..." name="description"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ngày bắt đầu</label>
                            <input type="datetime-local" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày kết thúc</label>
                            <input type="datetime-local" class="form-control" name="end_date">
                        </div>
                    </div>

                    <hr> 
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại giảm giá</label>
                            <select class="form-select" name="type" id="discountType">
                                <option value="fixed">Trừ tiền mặt (VNĐ)</option>
                                <option value="percent">Trừ theo %</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá trị giảm</label>
                            <input type="number" class="form-control" placeholder="VD: 50000 hoặc 10" name="value" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Đơn hàng tối thiểu (VNĐ)</label>
                            <input type="number" class="form-control" value="0" name="min_order">
                            <small class="text-muted mt-1 d-block">Nhập 0 nếu không yêu cầu.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giảm tối đa (VNĐ)</label>
                            <input type="number" class="form-control" value="" name="max_discount" placeholder="Bỏ trống nếu không giới hạn">
                            <small class="text-muted mt-1 d-block">Chỉ áp dụng khi giảm theo %.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="1" selected>Kích hoạt ngay</option>
                            <option value="0">Tạm ẩn (Chưa kích hoạt)</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" name="btnAddVoucher">
                            <i class="fas fa-save me-2"></i> Lưu Mã Giảm Giá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-dark">Cập nhật Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/web_qlsp/vouchers/update">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label>Mã Code</label>
                        <input type="text" class="form-control" id="edit_code" name="code" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Tổng giới hạn lượt dùng</label>
                        <input type="number" class="form-control" id="edit_usage_limit" name="usage_limit">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label>Ngày bắt đầu</label>
                            <input type="datetime-local" class="form-control" id="edit_start_date" name="start_date" readonly>
                        </div>
                        <div class="col-6">
                            <label>Ngày kết thúc</label>
                            <input type="datetime-local" class="form-control" id="edit_end_date" name="end_date">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <label>Giá trị giảm</label>
                            <input type="number" class="form-control" id="edit_value" name="value" disabled>
                        </div>
                        <div class="col-4">
                            <label>Đơn tối thiểu</label>
                            <input type="number" class="form-control" id="edit_min_order" name="min_order">
                        </div>
                        <div class="col-4">
                            <label>Giảm tối đa</label>
                            <input type="number" class="form-control" id="edit_max_discount" name="max_discount">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="1">Đang hoạt động (Active)</option>
                            <option value="0">Tạm ẩn</option>
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning fw-bold" name="btnUpdateVoucher">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-file-upload me-2"></i>Nhập Vouchers từ Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form method="POST" action="/web_qlsp/vouchers/import" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file Excel (.xlsx)</label>
                        <input type="file" name="fileImport" class="form-control" accept=".xlsx, .xls" required>
                        <small class="text-muted">
                            * Vui lòng sử dụng file có định dạng giống file Xuất.<br>
                            * Hệ thống sẽ bỏ qua dòng tiêu đề đầu tiên.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="btnImport" class="btn btn-primary-custom fw-bold">
                        <i class="fas fa-upload me-1"></i> Tiến hành Nhập
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/Css/vouchers.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/web_qlsp/Public/js/vouchers.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>
<script src="/web_qlsp/Public/js/loading.js"></script>


<?php if(isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>