<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="fw-bold mb-1">Nhập tồn kho từ Excel</h3>
    <p class="text-muted small mb-0">Cập nhật tồn kho theo SKU và số lượng mới</p>
  </div>
  <a href="/web_qlsp/warehouse" class="btn btn-outline-secondary"><i class="fas fa-warehouse me-2"></i> Về Kho hàng</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <?php if (!empty($data['error'])): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($data['error']); ?>
      </div>
    <?php endif; ?>

    <form action="/web_qlsp/warehouse/import" method="post" enctype="multipart/form-data" class="mb-3">
      <div class="mb-3">
        <label class="form-label">Chọn tệp Excel (.xlsx)</label>
        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-file-import me-2"></i> Nhập</button>
    </form>

    <div class="alert alert-info">
      <strong>Định dạng yêu cầu:</strong>
      <ul class="mb-0">
        <li>Cột <em>SKU</em>: dùng định dạng <code>PV-123</code> (ID biến thể).</li>
        <li>Cột <em>NewStock</em> (hoặc <em>Stock</em>/<em>Tồn kho mới</em>): số lượng tồn mong muốn.</li>
        <li>Hệ thống sẽ tính chênh lệch (delta) và cập nhật tồn kho biến thể tương ứng.</li>
      </ul>
    </div>

    <?php if (!empty($data['results'])): $r = $data['results']; ?>
      <hr>
      <h6 class="fw-bold">Kết quả nhập</h6>
      <div class="d-flex gap-3 mb-2">
        <span class="badge bg-success">Cập nhật: <?php echo intval($r['updated']); ?></span>
        <span class="badge bg-danger">Thất bại: <?php echo intval($r['failed']); ?></span>
      </div>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Trạng thái</th>
              <th>Từ</th>
              <th>Đến</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($r['details'] as $d): ?>
              <tr>
                <td><?php echo htmlspecialchars($d['sku'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($d['status'] ?? ''); ?></td>
                <td><?php echo isset($d['from']) ? intval($d['from']) : '-'; ?></td>
                <td><?php echo isset($d['to']) ? intval($d['to']) : '-'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a2e0a6f418.js" crossorigin="anonymous"></script>
