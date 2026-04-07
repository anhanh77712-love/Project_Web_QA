<style>
    body { font-family: "Montserrat", sans-serif; background-color: #ffffff; margin: 0; }
    a { text-decoration: none; color: #000; transition: 0.2s; }
    a:hover { color: #2f5acf; }
    .section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
    .section-title { font-weight: 800; font-size: 28px; text-transform: uppercase; letter-spacing: -0.5px; margin: 0; }
    .view-more { font-weight: 600; font-size: 14px; text-decoration: underline; color: #000; }
    .cool-card { border: none; background: transparent; }
    .cool-card-img-wrapper { background-color: #F3F3F3; border-radius: 12px; position: relative; overflow: hidden; aspect-ratio: 1/1.1; margin-bottom: 12px; }
    .cool-card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; mix-blend-mode: multiply; transition: transform 0.4s ease; }
    .cool-card:hover .cool-card-img-wrapper img { transform: scale(1.05); }
    .cool-badge { position: absolute; top: 12px; right: 12px; background-color: #000; color: #fff; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; z-index: 2; }
    .cool-badge.new { background-color: #2f5acf; } 
    .color-swatches { display: flex; gap: 6px; margin-bottom: 8px; }
    .color-dot { width: 18px; height: 18px; border-radius: 50%; border: 1px solid #ddd; position: relative; cursor: pointer; }
    .color-dot.active::after { content: ''; position: absolute; top: -3px; left: -3px; right: -3px; bottom: -3px; border: 1px solid #999; border-radius: 50%; }
    .cool-prod-name { font-size: 14px; font-weight: 500; color: #333; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .cool-price { font-size: 14px; font-weight: 700; color: #e74c3c; }
    .cool-price del { color: #999; font-weight: 400; font-size: 13px; margin-left: 5px; }
    .skeleton { background: #e2e8f0; animation: pulse 1.5s infinite; border-radius: 8px; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
</style>

<div id="home-content-container">
    <div id="home-skeleton">
        <div class="skeleton mb-5" style="width: 100%; height: 500px; border-radius: 0;"></div>
        <div class="container mb-5">
            <div class="d-flex justify-content-between mb-4">
                <div class="skeleton" style="width: 250px; height: 35px;"></div>
                <div class="skeleton" style="width: 80px; height: 20px;"></div>
            </div>
            <div class="row g-4">
                <?php for($i=0; $i<4; $i++): ?>
                <div class="col-6 col-md-3">
                    <div class="skeleton" style="aspect-ratio: 1/1.1; margin-bottom: 12px;"></div>
                    <div class="skeleton mb-2" style="width: 80%; height: 16px;"></div>
                    <div class="skeleton" style="width: 50%; height: 16px;"></div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // GỌI API LOAD DỮ LIỆU
        fetch('/web_qlsp/home/api_get_home_data')
            .then(res => res.json())
            .then(data => {
                if (data.success) renderHomePage(data);
            })
            .catch(err => {
                console.error("Lỗi tải trang chủ:", err);
                document.getElementById('home-content-container').innerHTML = '<div class="text-center py-5 text-danger fw-bold">Không thể tải dữ liệu trang chủ. Vui lòng kiểm tra lại kết nối.</div>';
            });
    });

    function renderHomePage(data) {
        let html = '';

        // 1. RENDER BANNER
        if (data.banners && data.banners.length > 0) {
            let indicators = ''; let items = '';
            data.banners.forEach((b, i) => {
                const active = i === 0 ? 'active' : '';
                indicators += `<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="${i}" class="${active}"></button>`;
                items += `<div class="carousel-item ${active}"><a href="${b.link_url}"><img src="/web_qlsp/Public/Picture/banners/${b.image_url}" class="d-block w-100" style="object-fit: cover; min-height: 400px; max-height: 600px;" alt="${b.title}"></a></div>`;
            });
            html += `<div id="heroCarousel" class="carousel slide mb-5" data-bs-ride="carousel"><div class="carousel-indicators">${indicators}</div><div class="carousel-inner">${items}</div><button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button><button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button></div>`;
        }

        // 2. RENDER SECTIONS
        if (data.sections && data.sections.length > 0) {
            data.sections.forEach(sec => {
                
                // Collection / Flash Sale
                if (['collection', 'flash_sale'].includes(sec.section_type)) {
                    const icon = sec.section_type === 'flash_sale' ? '<i class="fas fa-bolt text-danger me-2"></i>' : '';
                    let productsHtml = '';
                    
                    if (sec.products && sec.products.length > 0) {
                        sec.products.forEach(p => {
                            let swatchesHtml = '';
                            if (p.colors && p.colors.length > 0) {
                                swatchesHtml = `<div class="color-swatches mb-2" data-product-id="${p.id}">`;
                                p.colors.forEach((color, idx) => {
                                    const vData = p.variant_map[color];
                                    const active = idx === 0 ? 'active' : '';
                                    const border = color === 'Trắng' ? '2px solid #ddd' : '1px solid transparent';
                                    swatchesHtml += `<div class="color-dot ${active}" data-color="${color}" data-images='${JSON.stringify(vData.images)}' style="background-color: ${vData.hex}; border: ${border};" title="${color}"></div>`;
                                });
                                swatchesHtml += `</div>`;
                            }

                            const priceHtml = p.is_sale == 1 
                                ? `${Number(p.base_price).toLocaleString('vi-VN')}đ <del>${Number(p.base_price * 1.1).toLocaleString('vi-VN')}đ</del>`
                                : `${Number(p.base_price).toLocaleString('vi-VN')}đ`;

                            productsHtml += `
                            <div class="col-6 col-md-3">
                                <div class="cool-card">
                                    <div class="cool-card-img-wrapper">
                                        <a href="/web_qlsp/product_detail?slug=${encodeURIComponent(p.slug)}">
                                            <img src="/web_qlsp/Public/Picture/${p.thumbnail}" alt="${p.name}" class="product-image" data-product-id="${p.id}">
                                        </a>
                                    </div>
                                    <div class="card-info">
                                        <a href="/web_qlsp/product_detail?slug=${encodeURIComponent(p.slug)}" class="cool-prod-name">${p.name}</a>
                                        ${swatchesHtml}
                                        <div class="cool-price">${priceHtml}</div>
                                    </div>
                                </div>
                            </div>`;
                        });
                    } else { 
                        productsHtml = `<p class='text-center text-muted w-100'>Đang cập nhật sản phẩm...</p>`; 
                    }

                    const viewMore = sec.collection_id ? `<a href="/web_qlsp/product_list_customer?sale=1" class="view-more">Xem thêm</a>` : '';

                    html += `<section class="section-wrapper py-5 position-relative" style="background-color: ${sec.bg_color};"><div class="container"><div class="section-header"><h2 class="section-title" style="color: ${sec.text_color};">${icon}${sec.title}</h2>${viewMore}</div><div class="row g-4">${productsHtml}</div></div></section>`;
                } 
                
                // Category Grid
                else if (sec.section_type === 'category_grid') {
                    let catsHtml = '';
                    if (sec.categories && sec.categories.length > 0) {
                        sec.categories.forEach(c => {
                            const img = c.thumbnail ? `/web_qlsp/Public/Picture/categories/${c.thumbnail}` : "https://placehold.co/300x400";
                            catsHtml += `<div class="col-4 col-md-2"><a href="/web_qlsp/product_list_customer?category=${encodeURIComponent(c.slug)}" class="d-block text-center"><div class="rounded-3 overflow-hidden mb-2 shadow-sm"><img src="${img}" class="w-100" style="aspect-ratio: 3/4; object-fit: cover;" alt="${c.name}"></div><div class="fw-bold small text-uppercase" style="color: ${sec.text_color}">${c.name}</div></a></div>`;
                        });
                    }
                    html += `<section class="py-5" style="background-color: ${sec.bg_color};"><div class="container"><div class="section-header justify-content-center mb-4"><h2 class="section-title" style="color: ${sec.text_color};">${sec.title}</h2></div><div class="row g-3 justify-content-center">${catsHtml}</div></div></section>`;
                }
                
                // Overlay Banner
                else if (sec.section_type === 'overlay_banner') {
                    let pos = "start-0"; 
                    if(sec.text_position === 'center') pos = "start-50 translate-middle-x"; 
                    else if(sec.text_position === 'right') pos = "end-0 me-5"; 
                    
                    html += `<section class="py-4"><div class="container"><div class="position-relative rounded-3 overflow-hidden"><a href="${sec.link_url}"><img src="/web_qlsp/Public/Picture/campaigns/${sec.image_url}" class="w-100 d-block" style="min-height: 350px; object-fit: cover;" alt="${sec.title}"></a><div class="position-absolute top-50 translate-middle-y ${pos} p-5" style="z-index: 2; max-width: 600px; color: ${sec.text_color};"><h2 class="fw-bold display-5 mb-4">${sec.title}</h2><a href="${sec.link_url}" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark border-0">${sec.button_text || 'XEM NGAY'}</a></div></div></div></section>`;
                }
            });
        }

        // Bơm HTML vào Giao diện
        document.getElementById('home-content-container').innerHTML = html;
        
        // Khởi tạo các event sau khi render xong HTML
        attachColorDotEvents();
    }

    // HÀM CHUYỂN ẢNH KHI BẤM VÀO MÀU SẮC
    function attachColorDotEvents() {
        document.querySelectorAll('.color-dot').forEach(dot => {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                const colorSwatches = this.closest('.color-swatches');
                const productId = colorSwatches.getAttribute('data-product-id');
                const imagesStr = this.getAttribute('data-images');
                if (!imagesStr) return;
                const images = JSON.parse(imagesStr);
                
                colorSwatches.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                
                if(images && images.length > 0) {
                    const productImg = document.querySelector(`.product-image[data-product-id="${productId}"]`);
                    if(productImg) {
                        productImg.style.opacity = 0.5;
                        setTimeout(() => {
                            productImg.src = '/web_qlsp/Public/Picture/' + images[0];
                            productImg.style.opacity = 1;
                        }, 100);
                    }
                }
            });
        });
    }
</script>