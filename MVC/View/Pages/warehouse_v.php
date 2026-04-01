<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h3 class="fw-bold mb-1">Quản lý Kho hàng</h3>
		<p class="text-muted small mb-0">Theo dõi tồn kho, cảnh báo thiếu hàng và điều chỉnh nhanh</p>
	</div>
</div>

<div class="row g-3 mb-3">
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Tổng tồn kho</p>
					<h5 class="fw-bold mb-0"><?php echo number_format($data['sum_stock'] ?? 0); ?></h5>
				</div>
				<span class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
					<i class="fas fa-boxes"></i>
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Sắp hết hàng</p>
					<h5 class="fw-bold mb-0 text-warning"><?php echo number_format($data['low_stock_count'] ?? 0); ?></h5>
				</div>
				<span class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
					<i class="fas fa-exclamation-triangle"></i>
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Hết hàng</p>
					<h5 class="fw-bold mb-0 text-danger"><?php echo number_format($data['out_of_stock_count'] ?? 0); ?></h5>
				</div>
				<span class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
					<i class="fas fa-times-circle"></i>
				</span>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Giá trị tồn (ước tính)</p>
					<h5 class="fw-bold mb-0 text-success"><?php echo number_format($data['stock_value'] ?? 0, 0, ',', '.'); ?>đ</h5>
				</div>
				<span class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
					<i class="fas fa-coins"></i>
				</span>
			</div>
		</div>
	</div>
</div>

<form class="d-flex align-items-center gap-2 mb-3" onsubmit="return applyFilters(event)">
	<div class="input-group" style="max-width: 360px;">
		<span class="input-group-text"><i class="fas fa-search"></i></span>
		<input type="text" id="q" class="form-control" placeholder="Tìm theo tên/SPU/SKU" value="<?php echo htmlspecialchars($_GET['q'] ?? ($data['q'] ?? '')); ?>">
	</div>
	<select id="category" class="form-select" style="max-width: 220px;">
		<option value="">Tất cả danh mục</option>
		<?php if (!empty($data['categories']) && is_iterable($data['categories'])): foreach ($data['categories'] as $c): ?>
			<option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'selected' : ''; ?>>
				<?php echo htmlspecialchars($c['name']); ?>
			</option>
		<?php endforeach; endif; ?>
	</select>
	<select id="stockStatus" class="form-select" style="max-width: 220px;">
		<option value="">Tất cả trạng thái</option>
		<option value="ok" <?php echo (($_GET['status'] ?? '') === 'ok') ? 'selected' : ''; ?>>Bình thường</option>
		<option value="low" <?php echo (($_GET['status'] ?? '') === 'low') ? 'selected' : ''; ?>>Sắp hết</option>
		<option value="out" <?php echo (($_GET['status'] ?? '') === 'out') ? 'selected' : ''; ?>>Hết hàng</option>
	</select>
	<button class="btn btn-dark" type="submit"><i class="fas fa-filter me-2"></i>Lọc</button>
	<a class="btn btn-outline-secondary" href="/web_qlsp/api/warehouse_api/"><i class="fas fa-undo me-2"></i>Đặt lại</a>
</form>

<div class="card border-0 shadow-sm">
	<div class="card-body p-0">
		<table class="table table-hover mb-0 align-middle">
			<thead class="bg-light">
				<tr>
					<th>Ảnh</th>
					<th>Sản phẩm</th>
					<th>Biến thể</th>
					<th>Giá nhập</th>
					<th>Tồn kho</th>
					<th>Sẵn có</th>
					<th>Ngưỡng cảnh báo</th>
					<th class="text-end">Hành động</th>
				</tr>
			</thead>
			<tbody id="loading-skeleton">
				<?php for ($i=0;$i<6;$i++): ?>
				<tr>
					<td><div class="skeleton" style="width:60px;height:60px;border-radius:8px;"></div></td>
					<td><div class="skeleton" style="width:140px;height:16px;margin-bottom:6px;"></div><div class="skeleton" style="width:90px;height:12px;"></div></td>
					<td><div class="skeleton" style="width:90px;height:14px;"></div></td>
					<td><div class="skeleton" style="width:80px;height:14px;"></div></td>
					<td><div class="skeleton" style="width:50px;height:18px;"></div></td>
					<td><div class="skeleton" style="width:60px;height:18px;"></div></td>
					<td><div class="skeleton" style="width:80px;height:14px;"></div></td>
					<td class="text-end"><div class="skeleton" style="width:110px;height:32px;border-radius:6px;"></div></td>
				</tr>
				<?php endfor; ?>
			</tbody>
			<tbody id="actual-content" style="display:none;">
				<?php if (!empty($data['warehouse_items']) && is_iterable($data['warehouse_items'])): foreach ($data['warehouse_items'] as $it): 
					$available = max(0, intval(($it['stock_quantity'] ?? 0)) - intval(($it['reserved_quantity'] ?? 0)));
					$isLow = isset($it['threshold']) && $available <= intval($it['threshold']);
					$badge = $available == 0 ? '<span class="badge bg-danger">Hết hàng</span>' : ($isLow ? '<span class="badge bg-warning text-dark">Sắp hết</span>' : '<span class="badge bg-success">OK</span>');
				?>
				<tr>
					<td>
						<img src="/web_qlsp/Public/Picture/<?php echo htmlspecialchars($it['thumbnail'] ?? ''); ?>" width="60" height="60" style="object-fit:cover;border-radius:8px;border:1px solid #eee;" onerror="this.src='https://via.placeholder.com/60'">
					</td>
					<td>
						<div class="fw-bold"><?php echo htmlspecialchars($it['product_name'] ?? ''); ?></div>
						<small class="text-muted"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($it['category_name'] ?? ''); ?></small>
					</td>
					<td>
						<?php echo htmlspecialchars($it['color'] ?? ''); ?><?php echo isset($it['size']) && $it['size']!=='' ? (' • ' . htmlspecialchars($it['size'])) : ''; ?>
					</td>
					<td><?php echo number_format($it['cost_price'] ?? 0, 0, ',', '.'); ?>đ</td>
					<td class="fw-bold"><?php echo number_format($it['stock_quantity'] ?? 0); ?></td>
					<td>
						<span class="badge bg-light text-dark">
							<?php echo number_format($available); ?>
						</span>
						<div class="mt-1"><?php echo $badge; ?></div>
					</td>
					<td><?php echo isset($it['threshold']) ? number_format($it['threshold']) : '-'; ?></td>
					<td class="text-end">
						<button class="btn btn-sm btn-outline-primary" onclick="openAdjustModal(<?php echo intval($it['product_id'] ?? 0); ?>, <?php echo intval($it['variant_id'] ?? 0); ?>, '<?php echo htmlspecialchars($it['product_name'] ?? ''); ?>', '<?php echo htmlspecialchars($it['color'] ?? ''); ?>', '<?php echo htmlspecialchars($it['size'] ?? ''); ?>', <?php echo intval($it['stock_quantity'] ?? 0); ?>)">
							<i class="fas fa-edit"></i> Điều chỉnh
						</button>
					</td>
				</tr>
				<?php endforeach; else: ?>
				<tr>
					<td colspan="8" class="text-center text-muted py-4">Không có dữ liệu kho.</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Modal điều chỉnh tồn kho -->
<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Điều chỉnh tồn kho</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="adj_product_id">
				<input type="hidden" id="adj_variant_id">
				<div class="mb-2"><strong id="adj_title"></strong></div>
				<div class="mb-3">
					<label class="form-label">Tồn hiện tại</label>
					<input type="number" id="adj_current" class="form-control" readonly>
				</div>
				<div class="mb-3">
					<label class="form-label">Thay đổi</label>
					<div class="input-group">
						<span class="input-group-text">±</span>
						<input type="number" id="adj_delta" class="form-control" value="0" step="1">
					</div>
					<small class="text-muted">Nhập số dương để cộng, âm để trừ.</small>
				</div>
				<div class="mb-3">
					<label class="form-label">Lý do</label>
					<select id="adj_reason" class="form-select">
						<option value="stock_count">Kiểm kho</option>
						<option value="receive">Nhập hàng</option>
						<option value="damage">Hư hỏng/Mất mát</option>
						<option value="other">Khác</option>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
				<button type="button" class="btn btn-primary" onclick="submitAdjust()">Lưu</button>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/web_qlsp/Public/js/loading.js"></script>

<script>
// Hiển thị nội dung sau loading
window.addEventListener('load', function(){
  setTimeout(()=>{
	const skeleton = document.getElementById('loading-skeleton');
	const content = document.getElementById('actual-content');
	if(skeleton) skeleton.style.display = 'none';
	if(content) content.style.display = 'table-row-group';
  }, 300);
});

function applyFilters(e){
  if(e) e.preventDefault();
  const params = new URLSearchParams();
  const q = document.getElementById('q').value.trim();
  const cat = document.getElementById('category').value;
  const st = document.getElementById('stockStatus').value;
  if(q) params.set('q', q);
  if(cat) params.set('category', cat);
  if(st) params.set('status', st);
  window.location.href = '/web_qlsp/api/warehouse_api/' + (params.toString() ? ('?' + params.toString()) : '');
  return false;
}

let adjustModal;
function openAdjustModal(productId, variantId, name, color, size, current){
  document.getElementById('adj_product_id').value = productId;
  document.getElementById('adj_variant_id').value = variantId || '';
  document.getElementById('adj_title').textContent = name + (color? (' • ' + color):'') + (size? (' • ' + size):'');
  document.getElementById('adj_current').value = current;
  document.getElementById('adj_delta').value = 0;
  adjustModal = new bootstrap.Modal(document.getElementById('adjustModal'));
  adjustModal.show();
}

function submitAdjust(){
  const pid = document.getElementById('adj_product_id').value;
  const vid = document.getElementById('adj_variant_id').value;
  const delta = parseInt(document.getElementById('adj_delta').value || '0', 10);
  const reason = document.getElementById('adj_reason').value;
  if(!delta){
	Swal.fire('Thiếu dữ liệu', 'Vui lòng nhập số lượng điều chỉnh', 'warning');
	return;
  }
  const formData = new FormData();
  formData.append('product_id', pid);
  formData.append('variant_id', vid);
  formData.append('delta', delta);
  formData.append('reason', reason);
  fetch('/web_qlsp/api/warehouse_api//adjust_stock', { method: 'POST', body: formData })
	.then(r=>r.json())
	.then(d=>{
	  if(d && d.success){
		Swal.fire('Thành công', 'Đã cập nhật tồn kho', 'success').then(()=>{
		  location.reload();
		});
	  } else {
		Swal.fire('Thất bại', d?.message || 'Không thể cập nhật tồn kho', 'error');
	  }
	})
	.catch(err=>{
	  console.error(err);
	  Swal.fire('Lỗi', 'Có lỗi xảy ra khi gửi yêu cầu', 'error');
	});
}
</script>

