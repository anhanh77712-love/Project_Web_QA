<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/web_qlsp/Public/Css/add_to_cart.css">
    <style>
        .product-page { font-family: Arial, sans-serif; }
        .product-page * { box-sizing: border-box; }
        .product-container { max-width: 100%; margin: 0; padding: 25px 40px; display: flex; gap: 40px; background: #fff; border-radius: 12px; }

        /* ===== GALLERY ===== */
        .product-gallery { display: flex; gap: 12px; }
        .thumbs { display: flex; flex-direction: column; gap: 10px; }
        .thumb { width: 64px; height: 64px; border-radius: 8px; overflow: hidden; background:#eee; cursor: pointer; border:1px solid #eee; }
        .thumb img { width:100%; height:100%; object-fit: cover; display:block; }
        .thumb.active { border-color:#2563eb; }
        .product-main-img { width: 450px; height: 560px; border-radius: 10px; object-fit: cover; background: #eee; }

        /* ===== INFO & VARIANTS ===== */
        .product-info { flex: 1; }
        .product-title { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
        .variant-label { font-size:14px; color:#666; margin:12px 0 6px; }
        .color-swatches { display:flex; gap:10px; }
        .color-dot { width:26px; height:26px; border-radius:50%; border:1px solid #ddd; cursor:pointer; position:relative; box-shadow:0 1px 3px rgba(0,0,0,0.15); transition:transform .15s ease, box-shadow .15s ease; }
        .color-dot:hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(0,0,0,0.2); }
        .color-dot.active { outline:2px solid #2563eb; outline-offset:2px; }
        .color-dot.active::after { content:''; position:absolute; top:-3px; left:-3px; right:-3px; bottom:-3px; border:1px solid #999; border-radius:50%; }
        
        .size-list { display:flex; gap:10px; flex-wrap:wrap; }
        .size-chip { padding:8px 12px; border:1.5px solid #ddd; border-radius:12px; cursor:pointer; font-size:13px; background:#fff; transition:all .15s ease; }
        .size-chip:hover { border-color:#999; }
        .size-chip.active { border-color:#2563eb; color:#2563eb; box-shadow:0 2px 8px rgba(37,99,235,0.15); }
        .size-chip.sold-out { color:#b0b0b0; border-color:#ddd; background:#f7f7f7; cursor:not-allowed; position:relative; }
        .size-chip.sold-out::before, .size-chip.sold-out::after { content:''; position:absolute; left:6px; right:6px; top:50%; height:2px; background:#2f5acf; opacity:.7; }
        .size-chip.sold-out::before { transform:rotate(45deg); }
        .size-chip.sold-out::after { transform:rotate(-45deg); }

        /* ===== SIZE ADVISOR ===== */
        .size-advisor { margin-top: 10px; display: flex; gap: 12px; align-items: center; }
        .sa-btn { padding: 8px 12px; border-radius: 999px; border:1px solid #111; background:#fff; cursor:pointer; font-weight:600; }
        .sa-btn:hover { background:#111; color:#fff; }
        .sa-modal { position: relative; background:#fff; border:1px solid #eee; border-radius:12px; padding:14px; box-shadow:0 8px 28px rgba(0,0,0,0.12); max-width:420px; }
        .sa-grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
        .sa-grid .full { grid-column: 1 / -1; }
        .sa-modal label { font-size:13px; color:#555; }
        .sa-modal input, .sa-modal select { width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:8px; }
        .sa-actions { display:flex; gap:10px; margin-top:10px; }
        .sa-result { margin-top:8px; font-size:14px; font-weight:700; }

        /* ===== SIZE GUIDE MODAL ===== */
        .sg-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .sg-modal { width: min(720px, 92vw); background: #fff; border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,.25); overflow: hidden; animation: sgPop .18s ease-out; }
        @keyframes sgPop { from { transform: translateY(10px); opacity: .6; } to { transform:none; opacity:1; } }
        .sg-header { display:flex; align-items:center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #eee; }
        .sg-title { margin:0; font-size:18px; font-weight:700; }
        .sg-close { border:none; background:transparent; font-size:22px; line-height:1; cursor:pointer; padding:4px 8px; border-radius:8px; }
        .sg-close:hover { background:#f3f4f6; }
        .sg-body { padding: 16px 18px; }
        .sg-note { font-size:12px; color:#666; margin: 6px 0 14px; }
        .sg-table-wrap { overflow:auto; border:1px solid #eee; border-radius:10px; }
        .sg-table { width:100%; border-collapse: collapse; font-size:14px; }
        .sg-table th, .sg-table td { padding:10px 12px; text-align:center; border-bottom:1px solid #f0f0f0; white-space: nowrap; }
        .sg-table thead th { background:#f7f7f7; font-weight:700; }
        .sg-table tr:hover td { background:#fafafa; }
        .sg-foot { padding: 12px 18px 16px; font-size:12px; color:#666; }
        .sg-unavailable td { opacity:.45; text-decoration: line-through; }

        .product-price { font-size: 34px; font-weight: 800; margin: 6px 0 14px; letter-spacing: .2px; }

        /* ===== ACTIONS ===== */
        .product-actions { display: flex; gap: 15px; margin: 20px 0; }
        .qty-box { display: flex; align-items: center; background: #f3f4f6; border-radius: 30px; overflow: hidden; }
        .qty-box button { border: none; background: transparent; padding: 10px 16px; font-size: 18px; cursor: pointer; }
        .qty-box input { width: 40px; text-align: center; border: none; background: transparent; font-size: 16px; }
        .add-cart-btn { flex: 1; background: #000; color: #fff; border: none; border-radius: 30px; font-size: 18px; font-weight: 600; cursor: pointer; }

        /* ===== DESCRIPTION ===== */
        .product-desc { margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .product-desc h3 { font-size: 18px; margin-bottom: 10px; }
        .product-desc p { font-size: 14px; color: #555; line-height: 1.6; }
        .product-desc-content { max-height: 90px; overflow: hidden; transition: max-height 0.3s ease; }
        .product-desc-content.expanded { max-height: 1000px; }
        .product-desc-toggle { margin: 15px auto 0; display: block; padding: 10px 28px; border-radius: 999px; border: 1.5px solid #000; background: #fff; color: #000; font-size: 14px; font-weight: 600; letter-spacing: 0.5px; cursor: pointer; transition: 0.2s; }
        .product-desc-toggle:hover { background: #000; color: #fff; }

        /* ===== RELATED PRODUCTS ===== */
        .related-section { max-width: 100%; margin: 40px 0; padding: 0 40px; }
        .related-title { font-size: 28px; font-weight: bold; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }
        .related-carousel { position: relative; }
        .related-products { display: flex; gap: 20px; overflow-x: auto; scroll-behavior: smooth; padding: 10px 0; scrollbar-width: none; }
        .related-products::-webkit-scrollbar { display: none; }
        .related-card { min-width: calc(25% - 15px); flex: 0 0 calc(25% - 15px); background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; transition: 0.3s; border: none; }
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
        
        .cool-card-img-wrapper { background-color: #F3F3F3; border-radius: 12px; position: relative; overflow: hidden; aspect-ratio: 1/1.1; margin-bottom: 12px; }
        .cool-card-img-wrapper img.product-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; mix-blend-mode: multiply; }
        .related-card:hover .cool-card-img-wrapper img.product-image { transform: scale(1.03); }
        .related-info { padding: 12px 14px; }
        .related-name { font-size: 14px; margin-bottom: 6px; color: #333; min-height: 40px; line-height: 1.4; font-weight: 500; }
        .related-name a { color: #333; text-decoration: none; }
        .related-name a:hover { color: #2563eb; }
        .related-price { font-size: 14px; font-weight: 700; color: #e74c3c; }
        .related-price del { color:#999; font-weight:400; font-size:13px; margin-left:5px; }

        .related-products .color-swatches { display:flex; gap:6px; margin-bottom:8px; }
        .related-products .color-dot { width:18px; height:18px; border-radius:50%; border:1px solid #ddd; position:relative; cursor:pointer; }
        .related-products .color-dot.active::after { content:''; position:absolute; top:-3px; left:-3px; right:-3px; bottom:-3px; border:1px solid #999; border-radius:50%; }

        .carousel-nav { position: absolute; top: 45%; transform: translateY(-50%); width: 45px; height: 45px; background: #fff; border: none; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.15); cursor: pointer; font-size: 20px; transition: 0.2s; z-index: 2; }
        .carousel-nav:hover { background: #000; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.25); }
        .carousel-nav.prev { left: -20px; }
        .carousel-nav.next { right: -20px; }

        /* ===== TOAST NOTIFICATION ===== */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 10000; display: flex; flex-direction: column; gap: 10px; }
        .custom-toast { min-width: 300px; background: #fff; border-radius: 8px; padding: 16px 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; animation: slideInLeft 0.3s ease forwards, fadeOut 0.3s ease 3s forwards; border-left: 5px solid #333; }
        .custom-toast.success { border-left-color: #2ecc71; }
        .custom-toast.error { border-left-color: #e74c3c; }
        .custom-toast.warning { border-left-color: #f1c40f; }
        .toast-icon { font-size: 20px; }
        .custom-toast.success .toast-icon { color: #2ecc71; }
        .custom-toast.error .toast-icon { color: #e74c3c; }
        .custom-toast.warning .toast-icon { color: #f1c40f; }
        .toast-message { font-size: 14px; font-weight: 500; color: #333; }
        @keyframes slideInLeft { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeOut { to { opacity: 0; visibility: hidden; } }
    </style>
</head>

<body>
    <div id="toast-container"></div>

    <?php 
    // Dữ liệu đã được chuẩn bị gọn gàng từ Controller
    $product = $data['product'] ?? null;
    if(!$product): 
    ?>
        <div style="text-align:center; padding:100px;">
            <h2>Sản phẩm không tồn tại</h2>
            <a href="/web_qlsp/Customer/product_list_customer">Quay lại danh sách</a>
        </div>
    <?php 
        exit; 
    endif;
    
    $colors = $data['colors'] ?? [];
    $variant_map = $data['variant_map'] ?? [];
    $color_images = $data['color_images'] ?? [];
    $all_images = $data['all_images'] ?? [];
    $product_model = $data['product_model'] ?? null;

    $defaultColor = $colors[0] ?? null;
    $defaultImages = [];
    if($defaultColor && isset($color_images[$defaultColor]) && !empty($color_images[$defaultColor])){
        $defaultImages = $color_images[$defaultColor];
    } else {
        $defaultImages = $all_images;
    }
    if(empty($defaultImages)) $defaultImages[] = $product['thumbnail'];
    
    $defaultSize = null;
    if($defaultColor && isset($variant_map[$defaultColor]) && !empty($variant_map[$defaultColor])){
        $defaultSize = $variant_map[$defaultColor][0]['size'];
    }
    ?>

    <div class="product-page">
        <div class="product-container">

            <div class="product-gallery" id="gallery" 
                 data-map='<?= json_encode($variant_map) ?>'
                 data-color-images='<?= json_encode($color_images) ?>'
                 data-all-images='<?= json_encode($all_images) ?>'
                 data-default-color='<?= htmlspecialchars($defaultColor ?? '') ?>'>
                <div class="thumbs" id="thumbs">
                    <?php foreach($defaultImages as $idx => $img): ?>
                        <div class="thumb <?= $idx===0?'active':'' ?>" data-src="/web_qlsp/Public/Picture/<?= htmlspecialchars($img) ?>">
                            <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($img) ?>" alt="thumb">
                        </div>
                    <?php endforeach; ?>
                </div>
                <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($defaultImages[0]) ?>" 
                     class="product-main-img" id="mainImage"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onerror="this.src='https://via.placeholder.com/450x560'">
            </div>

            <div class="product-info">
                <div class="product-title"><?= htmlspecialchars($product['name']) ?></div>

                <div class="product-price">
                    <?php if(isset($product['is_sale']) && intval($product['is_sale'])===1): ?>
                        <span style="color: #e74c3c;"><?= number_format($product['base_price']) ?>đ</span>
                        <span style="font-size: 18px; color: #999; text-decoration: line-through; margin-left: 10px;">
                            <?= number_format($product['base_price']*1.1) ?>đ
                        </span>
                        <span style="font-size: 16px; background: hsl(233 68% 48%); color: #fff; padding: 4px 8px; border-radius: 4px; margin-left: 10px;">-10%</span>
                    <?php else: ?>
                        <span style="color: #e74c3c;"><?= number_format($product['base_price']) ?>đ</span>
                    <?php endif; ?>
                </div>

                <?php if(!empty($product['category_name'])): ?>
                <div style="margin-bottom: 15px; font-size: 14px; color: #666;">
                    <strong>Danh mục:</strong> 
                    <a href="/web_qlsp/Customer/product_list_customer?category=<?= urlencode($product['category_slug']) ?>" 
                       style="color: #2563eb; text-decoration: none;">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if(!empty($product['gender'])): ?>
                <div style="margin-bottom: 15px; font-size: 14px; color: #666;">
                    <strong>Giới tính:</strong> <?= htmlspecialchars($product['gender']) ?>
                </div>
                <?php endif; ?>

                <?php if(!empty($colors)): ?>
                    <div class="variant-label">Màu sắc</div>
                    <div class="color-swatches" id="colorSwatches">
                        <?php foreach($colors as $idx => $color): ?>
                            <div class="color-dot <?= $idx===0?'active':'' ?>" 
                                 data-color="<?= htmlspecialchars($color) ?>"
                                 style="background-color: <?= $product_model ? $product_model->get_color_hex($color) : '#ccc' ?>; border: <?= $color==='Trắng'?'2px solid #ddd':'1px solid transparent' ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="variant-label">Kích thước</div>
                    <div class="size-list" id="sizeList">
                        <?php if($defaultColor && isset($variant_map[$defaultColor])): ?>
                            <?php foreach($variant_map[$defaultColor] as $i => $sv): ?>
                                <div class="size-chip <?= $i===0?'active':'' ?>" data-size="<?= htmlspecialchars($sv['size']) ?>" data-variant-id="<?= intval($sv['variant_id']) ?>" data-stock="<?= intval($sv['stock']) ?>" data-images='<?= json_encode($sv['images']) ?>'>
                                    <?= htmlspecialchars($sv['size']) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="stock-info" id="stockInfo" style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 6px; font-size: 14px;">
                        <span style="color: #666;">Số lượng còn lại:</span> 
                        <span id="stockNumber" style="font-weight: 700; color: #28a745;">
                            <?php 
                            if($defaultColor && isset($variant_map[$defaultColor]) && !empty($variant_map[$defaultColor])){
                                $defaultStock = $variant_map[$defaultColor][0]['stock'];
                                echo $defaultStock > 0 ? $defaultStock . ' sản phẩm' : '<span style="color: #dc3545;">Hết hàng</span>';
                            } else {
                                echo 'Chưa xác định';
                            }
                            ?>
                        </span>
                    </div>

                    <div class="size-advisor">
                        <button type="button" class="sa-btn" id="openSizeAdvisor">Tư vấn chọn size</button>
                        <a href="#" id="openSizeGuide" style="font-size:13px; color:#2563eb; text-decoration:none;">Xem bảng size</a>
                    </div>

                    <div class="sg-overlay" id="sizeGuideOverlay" hidden>
                        <div class="sg-modal" role="dialog" aria-modal="true">
                            <div class="sg-header">
                                <h4 class="sg-title">Bảng size tham khảo</h4>
                                <button type="button" class="sg-close" id="sgClose">×</button>
                            </div>
                            <div class="sg-body">
                                <div class="sg-note">Áp dụng cho áo nam phom chuẩn. Số đo mang tính tham khảo; có thể thay đổi theo chất liệu.</div>
                                <div class="sg-table-wrap">
                                    <table class="sg-table" id="sgTable">
                                        <thead>
                                            <tr><th>Size</th><th>Chiều cao (cm)</th><th>Cân nặng (kg)</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr data-size="XS"><td>XS</td><td>150–160</td><td>45–55</td></tr>
                                            <tr data-size="S"><td>S</td><td>160–168</td><td>52–60</td></tr>
                                            <tr data-size="M"><td>M</td><td>168–175</td><td>60–70</td></tr>
                                            <tr data-size="L"><td>L</td><td>173–180</td><td>68–80</td></tr>
                                            <tr data-size="XL"><td>XL</td><td>178–186</td><td>78–90</td></tr>
                                            <tr data-size="XXL"><td>XXL</td><td>184–195</td><td>88–105</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="sg-foot">Kích thước bị gạch mờ là size hiện không có sẵn.</div>
                        </div>
                    </div>

                    <div class="sa-modal" id="sizeAdvisorModal" hidden>
                        <div class="sa-grid">
                            <div><label>Chiều cao (cm)</label><input type="number" id="saHeight" min="140" max="210" placeholder="172"></div>
                            <div><label>Cân nặng (kg)</label><input type="number" id="saWeight" min="35" max="130" placeholder="65"></div>
                            <div>
                                <label>Giới tính</label>
                                <select id="saGender">
                                    <option value="auto">Tự động</option><option value="nam">Nam</option><option value="nu">Nữ</option>
                                </select>
                            </div>
                            <div>
                                <label>Phong cách</label>
                                <select id="saFit">
                                    <option value="regular">Vừa vặn</option><option value="slim">Ôm sát</option><option value="relaxed">Thoải mái</option>
                                </select>
                            </div>
                            <div class="full sa-result" id="saResult"></div>
                        </div>
                        <div class="sa-actions">
                            <button type="button" class="sa-btn" id="saApply" disabled>Chọn size gợi ý</button>
                            <button type="button" class="sa-btn" id="saClose">Đóng</button>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="addToCartForm" method="POST" action="/web_qlsp/cart/add_to_cart">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                    <input type="hidden" name="product_price" value="<?= $product['base_price'] ?>">
                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($product['thumbnail']) ?>">
                    <input type="hidden" name="size" id="selectedSize" value="<?= htmlspecialchars($defaultSize ?? '') ?>">
                    <input type="hidden" name="color" id="selectedColor" value="<?= htmlspecialchars($defaultColor ?? '') ?>">
                    <input type="hidden" name="variant_id" id="selectedVariant" value="<?php 
                        if($defaultColor && isset($variant_map[$defaultColor]) && !empty($variant_map[$defaultColor])){ 
                            echo intval($variant_map[$defaultColor][0]['variant_id']); 
                        } 
                    ?>">
                    
                    <div class="product-actions">
                        <div class="qty-box">
                            <button type="button" id="qtyMinus">-</button>
                            <input type="text" id="qtyInput" name="quantity" value="1">
                            <button type="button" id="qtyPlus">+</button>
                        </div>
                        <button type="submit" class="add-cart-btn">🛒 Thêm vào giỏ hàng</button>
                    </div>
                </form>

            </div>
        </div>
        
        <div class="product-desc" style="max-width: 100%; margin: 30px 0; background: #fff; padding: 30px 40px; border-radius: 12px;">
            <h3>Mô tả sản phẩm</h3>
            <div class="product-desc-content" id="descContent">
                <?php if(!empty($product['description'])): ?>
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                <?php else: ?>
                    <p>Chưa có mô tả cho sản phẩm này.</p>
                <?php endif; ?>
            </div>
            <button class="product-desc-toggle" id="descToggle">XEM THÊM</button>
        </div>
        
        <?php 
        $related_products = $data['related_products'] ?? [];
        if(count($related_products) > 0):
        ?>
        <div class="related-section">
            <h2 class="related-title">Gợi ý sản phẩm</h2>
            <div class="related-carousel">
                <button class="carousel-nav prev" onclick="scrollCarousel('left')">‹</button>
                <button class="carousel-nav next" onclick="scrollCarousel('right')">›</button>
                
                <div class="related-products" id="relatedProducts">
                    <?php foreach($related_products as $rp): ?>
                    <div class="related-card">
                        <div class="cool-card">
                            <div class="cool-card-img-wrapper">
                                <a href="/web_qlsp/Customer/product_detail?slug=<?= urlencode($rp['slug']) ?>">
                                    <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($rp['thumbnail']) ?>" 
                                         class="product-image"
                                         data-product-id="<?= $rp['id'] ?>"
                                         alt="<?= htmlspecialchars($rp['name']) ?>"
                                         onerror="this.src='https://via.placeholder.com/280x320'">
                                </a>
                            </div>

                            <div class="related-info">
                                <a href="/web_qlsp/Customer/product_detail?slug=<?= urlencode($rp['slug']) ?>" class="related-name">
                                    <?= htmlspecialchars($rp['name']) ?>
                                </a>

                                <?php if(!empty($rp['colors'])): ?>
                                    <div class="color-swatches" data-product-id="<?= $rp['id'] ?>" data-variant-map='<?= json_encode($rp['variant_map']) ?>'>
                                        <?php foreach($rp['colors'] as $idx => $c): ?>
                                            <div class="color-dot <?= $idx===0?'active':'' ?>" 
                                                 data-color="<?= htmlspecialchars($c) ?>"
                                                 data-images='<?= json_encode($rp['variant_map'][$c]['images']) ?>'
                                                 style="background-color: <?= $rp['variant_map'][$c]['hex'] ?>; border: <?= $c==='Trắng'?'2px solid #ddd':'1px solid transparent' ?>;"
                                                 title="<?= htmlspecialchars($c) ?>"></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="related-price">
                                    <?= number_format($rp['base_price']) ?>đ
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Hàm Toast Custom
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `custom-toast ${type}`;
            let icon = type === 'error' ? 'fa-times-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-check-circle');
            toast.innerHTML = `<div class="toast-icon"><i class="fas ${icon}"></i></div><div class="toast-message">${message}</div>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function(){
            // Gallery
            const mainImage = document.getElementById('mainImage');
            const thumbsWrap = document.getElementById('thumbs');
            
            function bindThumbClick() {
                document.querySelectorAll('#thumbs .thumb').forEach(t=>{
                    t.addEventListener('click', function(){
                        document.querySelectorAll('#thumbs .thumb').forEach(x=>x.classList.remove('active'));
                        this.classList.add('active');
                        if(mainImage) mainImage.src = this.getAttribute('data-src');
                    });
                });
            }
            bindThumbClick();

            // Variant Switching
            const gallery = document.getElementById('gallery');
            const map = gallery ? JSON.parse(gallery.getAttribute('data-map') || '{}') : {};
            const colorImages = gallery ? JSON.parse(gallery.getAttribute('data-color-images') || '{}') : {};
            const colorWrap = document.getElementById('colorSwatches');
            const sizeWrap = document.getElementById('sizeList');

            function renderThumbs(images){
                if(!thumbsWrap) return;
                thumbsWrap.innerHTML = '';
                (images||[]).forEach((img, idx)=>{
                    const div = document.createElement('div');
                    div.className = 'thumb' + (idx===0?' active':'');
                    div.setAttribute('data-src', '/web_qlsp/Public/Picture/' + img);
                    div.innerHTML = '<img src="/web_qlsp/Public/Picture/' + img + '" alt="thumb">';
                    thumbsWrap.appendChild(div);
                });
                bindThumbClick();
                if(images && images.length>0 && mainImage) mainImage.src = '/web_qlsp/Public/Picture/' + images[0];
            }

            function renderSizes(color){
                if(!sizeWrap) return;
                sizeWrap.innerHTML = '';
                const items = map[color] || [];
                items.forEach((sv, idx)=>{
                    const isSoldOut = !sv.stock || sv.stock <= 0;
                    const chip = document.createElement('div');
                    chip.className = 'size-chip' + (isSoldOut ? ' sold-out' : '');
                    chip.setAttribute('data-size', sv.size);
                    chip.setAttribute('data-variant-id', sv.variant_id);
                    chip.setAttribute('data-stock', sv.stock || 0);
                    chip.textContent = sv.size;
                    chip.title = isSoldOut ? 'Hết hàng' : '';
                    
                    chip.addEventListener('click', function(){
                        if (this.classList.contains('sold-out')) {
                            showToast('Kích thước này đã hết hàng!', 'error'); return;
                        }
                        sizeWrap.querySelectorAll('.size-chip').forEach(x=>x.classList.remove('active'));
                        this.classList.add('active');
                        document.getElementById('selectedSize').value = this.getAttribute('data-size');
                        document.getElementById('selectedVariant').value = this.getAttribute('data-variant-id');
                        updateStockDisplay(parseInt(this.getAttribute('data-stock')) || 0);
                    });
                    sizeWrap.appendChild(chip);
                });
                
                const imgsByColor = colorImages[color] || [];
                if(imgsByColor.length>0) renderThumbs(imgsByColor);
                else if(items.length>0) renderThumbs(items[0].images||[]);

                const firstAvailable = sizeWrap.querySelector('.size-chip:not(.sold-out)');
                if (firstAvailable) {
                    firstAvailable.classList.add('active');
                    document.getElementById('selectedSize').value = firstAvailable.getAttribute('data-size');
                    document.getElementById('selectedVariant').value = firstAvailable.getAttribute('data-variant-id');
                    updateStockDisplay(parseInt(firstAvailable.getAttribute('data-stock')) || 0);
                } else if(items.length > 0){
                    updateStockDisplay(0);
                }
            }

            function updateStockDisplay(stock){
                const sn = document.getElementById('stockNumber');
                if(sn){
                    if(stock > 0){ sn.innerHTML = stock + ' sản phẩm'; sn.style.color = '#28a745'; } 
                    else { sn.innerHTML = '<span style="color: #dc3545;">Hết hàng</span>'; }
                }
            }

            if(colorWrap){
                colorWrap.querySelectorAll('.color-dot').forEach(dot=>{
                    dot.addEventListener('click', function(){
                        colorWrap.querySelectorAll('.color-dot').forEach(x=>x.classList.remove('active'));
                        this.classList.add('active');
                        const color = this.getAttribute('data-color');
                        renderSizes(color);
                        document.getElementById('selectedColor').value = color;
                    });
                });
            }

            // QTY Control (Cho phép lên tới 2)
            const qtyInput = document.getElementById('qtyInput');
            document.getElementById('qtyMinus').addEventListener('click', function(e){
                e.preventDefault();
                let val = parseInt(qtyInput.value) || 1;
                qtyInput.value = Math.max(1, val - 1);
            });
            document.getElementById('qtyPlus').addEventListener('click', function(e){
                e.preventDefault();
                let val = parseInt(qtyInput.value) || 1;
                qtyInput.value = Math.min(2, val + 1); // Giới hạn là 2
            });

            // Add To Cart Fetch API
            const addToCartForm = document.getElementById('addToCartForm');
            if(addToCartForm) {
                addToCartForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const activeChip = document.querySelector('#sizeList .size-chip.active');
                    const qty = parseInt(qtyInput.value) || 1;

                    // Kiểm tra tồn kho trên giao diện
                    if (activeChip) {
                        const stock = parseInt(activeChip.getAttribute('data-stock'));
                        if (!isNaN(stock) && stock <= 0) { 
                            showToast('Sản phẩm này tạm thời hết hàng!', 'error'); 
                            return; 
                        }
                        if (!isNaN(stock) && qty > stock) { 
                            showToast(`Chỉ còn ${stock} sản phẩm trong kho!`, 'warning'); 
                            return; 
                        }
                    }
                    
                    // Lấy chính xác đường dẫn từ thuộc tính action của form (chống lỗi URL)
                    const actionUrl = this.getAttribute('action'); 
                    
                    fetch(actionUrl, {
                        method: 'POST',
                        body: new FormData(this)
                    })
                    .then(async response => {
                        if (!response.ok) throw new Error("Mã lỗi máy chủ: " + response.status);
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("Dữ liệu lỗi từ PHP:", text);
                            throw new Error("Đường dẫn API sai hoặc PHP báo lỗi. Nhấn F12 xem chi tiết.");
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.limit_reached) {
                                showToast('Đã thêm (Đạt giới hạn tối đa 2 sản phẩm/loại)', 'warning');
                            } else {
                                showToast('Đã thêm vào giỏ hàng thành công!', 'success');
                            }
                            
                            // Cập nhật số lượng trên icon Giỏ hàng
                            const countEl = document.getElementById('cart-count');
                            if (countEl && data.count !== undefined) countEl.textContent = data.count;
                        } else {
                            showToast(data.message || 'Có lỗi xảy ra!', data.limit_reached ? 'warning' : 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showToast('Lỗi kết nối server!', 'error');
                    });
                });
            }

            // Desc Toggle
            const toggleBtn = document.getElementById('descToggle');
            const content = document.getElementById('descContent');
            if(toggleBtn){
                toggleBtn.addEventListener('click', () => {
                    content.classList.toggle('expanded');
                    toggleBtn.textContent = content.classList.contains('expanded') ? 'THU GỌN' : 'XEM THÊM';
                });
            }
            
            // Related Swatches
            document.querySelectorAll('#relatedProducts .color-dot').forEach(dot => {
                dot.addEventListener('click', function(e){
                    e.preventDefault();
                    const swatches = this.closest('.color-swatches');
                    const pid = swatches.getAttribute('data-product-id');
                    const images = JSON.parse(this.getAttribute('data-images')||'[]');
                    swatches.querySelectorAll('.color-dot').forEach(d=>d.classList.remove('active'));
                    this.classList.add('active');
                    if(images.length > 0){
                        const imgEl = document.querySelector(`img.product-image[data-product-id="${pid}"]`);
                        if(imgEl) imgEl.src = '/web_qlsp/Public/Picture/' + images[0];
                    }
                });
            });
        });

        function scrollCarousel(direction) {
            const carousel = document.getElementById('relatedProducts');
            if(direction === 'left') carousel.scrollLeft -= carousel.offsetWidth;
            else carousel.scrollLeft += carousel.offsetWidth;
        }

        // Kịch bản Size Advisor & Guide (Giữ nguyên)
        document.addEventListener('DOMContentLoaded', function(){
            const modal = document.getElementById('sizeAdvisorModal');
            const openBtn = document.getElementById('openSizeAdvisor');
            const closeBtn = document.getElementById('saClose');
            const applyBtn = document.getElementById('saApply');
            const heightEl = document.getElementById('saHeight');
            const weightEl = document.getElementById('saWeight');
            const resultEl = document.getElementById('saResult');
            const sizeWrap = document.getElementById('sizeList');
            const sgOverlay = document.getElementById('sizeGuideOverlay');

            function getAvailableSizes(){
                return Array.from(sizeWrap.querySelectorAll('.size-chip'))
                    .filter(ch => !ch.classList.contains('sold-out'))
                    .map(ch => (ch.getAttribute('data-size')||'').toUpperCase());
            }

            function pickNearestAvailable(target, available){
                const order = ['XS','S','M','L','XL','XXL','3XL'];
                if (available.includes(target)) return target;
                const idx = order.indexOf(target);
                if (idx === -1) return available[0] || null;
                for (let step=1; step<order.length; step++){
                    if (order[idx - step] && available.includes(order[idx - step])) return order[idx - step];
                    if (order[idx + step] && available.includes(order[idx + step])) return order[idx + step];
                }
                return available[0] || null;
            }

            function recommendSize(h, w){
                const chart = [
                    {size:'XS', h:[150,160], w:[45,55]}, {size:'S', h:[160,168], w:[52,60]},
                    {size:'M', h:[168,175], w:[60,70]}, {size:'L', h:[173,180], w:[68,80]},
                    {size:'XL', h:[178,186], w:[78,90]}, {size:'XXL',h:[184,195], w:[88,105]}
                ];
                for (const row of chart){
                    if (h>=row.h[0] && h<=row.h[1] && w>=row.w[0] && w<=row.w[1]) return row.size;
                }
                return 'M'; // Fallback
            }

            function renderRecommendation(){
                const h = parseInt(heightEl.value)||0; const w = parseFloat(weightEl.value)||0;
                applyBtn.disabled = true;
                if (!h || !w){ resultEl.textContent = 'Nhập chiều cao và cân nặng.'; return; }
                const target = recommendSize(h, w);
                const pick = pickNearestAvailable(target, getAvailableSizes());
                if (!pick){ resultEl.textContent = 'Size hiện tại đã hết hàng.'; return; }
                resultEl.textContent = `Gợi ý: ${pick}`;
                applyBtn.disabled = false; applyBtn.setAttribute('data-size', pick);
            }

            if (openBtn) openBtn.addEventListener('click', ()=> modal.hidden = false);
            if (closeBtn) closeBtn.addEventListener('click', ()=> modal.hidden = true);
            if (applyBtn) applyBtn.addEventListener('click', ()=>{
                const size = applyBtn.getAttribute('data-size');
                const chip = Array.from(sizeWrap.querySelectorAll('.size-chip')).find(c => c.getAttribute('data-size') === size);
                if (chip) chip.click();
                modal.hidden = true;
            });

            document.getElementById('openSizeGuide')?.addEventListener('click', e => { e.preventDefault(); sgOverlay.hidden = false; });
            document.getElementById('sgClose')?.addEventListener('click', () => sgOverlay.hidden = true);
            
            [heightEl, weightEl].forEach(el => {
                if (el) { el.addEventListener('input', renderRecommendation); }
            });
        });
    </script>
</body>
</html>