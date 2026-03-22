document.addEventListener('DOMContentLoaded', function() {
    
    // ===== 1. TỰ ĐỘNG CHÈN CSS CHO TOAST (Không cần sửa file CSS gốc) =====
    if (!document.getElementById('custom-toast-style')) {
        const style = document.createElement('style');
        style.id = 'custom-toast-style';
        style.innerHTML = `
            #custom-toast-container {
                position: fixed; top: 20px; right: 20px; z-index: 999999;
                display: flex; flex-direction: column; gap: 10px; pointer-events: none;
            }
            .custom-toast {
                min-width: 300px; padding: 16px 20px; background: #fff;
                border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.15);
                display: flex; align-items: center; gap: 12px;
                animation: slideIn 0.3s ease, fadeOut 0.3s ease 3s forwards;
                border-left: 5px solid #333; font-family: 'Segoe UI', Arial, sans-serif;
                pointer-events: auto;
            }
            .custom-toast.success { border-left-color: #2ecc71; }
            .custom-toast.success i { color: #2ecc71; font-size: 20px; }
            
            .custom-toast.error { border-left-color: #e74c3c; }
            .custom-toast.error i { color: #e74c3c; font-size: 20px; }
            
            .custom-toast.warning { border-left-color: #f1c40f; }
            .custom-toast.warning i { color: #f1c40f; font-size: 20px; }
            
            .toast-msg { font-size: 14px; font-weight: 500; color: #333; }
            
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes fadeOut { to { opacity: 0; visibility: hidden; } }
        `;
        document.head.appendChild(style);
    }

    // Tạo container chứa thông báo nếu chưa có
    if (!document.getElementById('custom-toast-container')) {
        const container = document.createElement('div');
        container.id = 'custom-toast-container';
        document.body.appendChild(container);
    }

    // Hàm hiển thị thông báo
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        
        // Kiểm tra FontAwesome
        const hasFontAwesome = document.querySelector('link[href*="font-awesome"]');
        
        let iconHTML = '';
        if (hasFontAwesome) {
            let iconClass = 'fa-check-circle';
            if (type === 'error') iconClass = 'fa-times-circle';
            if (type === 'warning') iconClass = 'fa-exclamation-triangle';
            iconHTML = `<i class="fas ${iconClass}"></i>`;
        } else {
            // Fallback icon text nếu web chưa có FontAwesome
            iconHTML = type === 'success' ? '<span style="color:#2ecc71;font-size:20px">✔</span>' 
                     : (type === 'error' ? '<span style="color:#e74c3c;font-size:20px">✖</span>' 
                     : '<span style="color:#f1c40f;font-size:20px">⚠</span>');
        }

        toast.innerHTML = `${iconHTML} <div class="toast-msg">${message}</div>`;
        
        const box = document.getElementById('custom-toast-container');
        if(box) box.appendChild(toast);

        // Tự xóa sau 3.5s
        setTimeout(() => { 
            if(toast.parentNode) toast.remove(); 
        }, 3500);
    }

    // ===== 2. XỬ LÝ LOGIC GALLERY THUMBS =====
    const mainImage = document.getElementById('mainImage');
    const thumbs = document.querySelectorAll('#thumbs .thumb');
    
    thumbs.forEach(t => {
        // Xóa event cũ trước khi gán mới (đề phòng lặp)
        t.removeEventListener('click', handleThumbClick); 
        t.addEventListener('click', handleThumbClick);
    });

    function handleThumbClick() {
        document.querySelectorAll('#thumbs .thumb').forEach(x => x.classList.remove('active'));
        this.classList.add('active');
        const src = this.getAttribute('data-src');
        if(src && mainImage){ mainImage.src = src; }
    }

    // ===== 3. XỬ LÝ RELATED SWATCHES =====
    document.querySelectorAll('#relatedProducts .color-dot').forEach(dot => {
        dot.removeEventListener('click', handleRelatedClick);
        dot.addEventListener('click', handleRelatedClick);
    });

    function handleRelatedClick(e) {
        e.preventDefault();
        const swatches = this.closest('.color-swatches');
        const pid = swatches ? swatches.getAttribute('data-product-id') : null;
        const images = this.getAttribute('data-images');
        
        if(swatches){
            swatches.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
            this.classList.add('active');
        }
        
        try {
            const arr = images ? JSON.parse(images) : [];
            if(arr && arr.length > 0 && pid){
                const imgEl = document.querySelector(`img.product-image[data-product-id="${pid}"]`);
                if(imgEl) imgEl.src = '/web_qlsp/Public/Picture/' + arr[0];
            }
        } catch(err) { /* ignore */ }
    }

    // ===== 4. XỬ LÝ SIZE ADVISOR (Tư vấn size) =====
    const modal = document.getElementById('sizeAdvisorModal');
    const openBtn = document.getElementById('openSizeAdvisor');
    const closeBtn = document.getElementById('saClose');
    const applyBtn = document.getElementById('saApply');
    
    // Các input size advisor
    const heightEl = document.getElementById('saHeight');
    const weightEl = document.getElementById('saWeight');
    const genderEl = document.getElementById('saGender');
    const fitEl = document.getElementById('saFit');
    const resultEl = document.getElementById('saResult');
    const sizeWrap = document.getElementById('sizeList');
    
    // Size Guide Modal
    const sizeGuideLink = document.getElementById('openSizeGuide');
    const sgOverlay = document.getElementById('sizeGuideOverlay');
    const sgClose = document.getElementById('sgClose');
    const sgTable = document.getElementById('sgTable');

    // --- Helper Functions ---
    function getAvailableSizes(){
        if(!sizeWrap) return [];
        return Array.from(sizeWrap.querySelectorAll('.size-chip'))
            .filter(ch => !ch.classList.contains('sold-out'))
            .map(ch => (ch.getAttribute('data-size')||'').toUpperCase());
    }

    function recommendSize(h, w, gender, fit){
        // Logic gợi ý size đơn giản
        const chart = [
            {size:'XS', h:[150,160], w:[45,55]},
            {size:'S',  h:[160,168], w:[52,60]},
            {size:'M',  h:[168,175], w:[60,70]},
            {size:'L',  h:[173,180], w:[68,80]},
            {size:'XL', h:[178,186], w:[78,90]},
            {size:'XXL',h:[184,195], w:[88,105]}
        ];
        
        let best = 'M';
        // Điều chỉnh theo giới tính
        const adj = (gender === 'nu') ? -0.5 : 0; 

        for (const row of chart){
            if (h >= row.h[0] && h <= row.h[1] && w >= row.w[0] && w <= row.w[1]){ 
                best = row.size; break; 
            }
        }
        
        // Nếu không khớp khoảng nào, tính theo BMI ước lượng
        if (!best){
            const bmi = w / Math.pow(h/100, 2);
            if (bmi < 20) best='S';
            else if (bmi < 23) best='M';
            else if (bmi < 26) best='L';
            else if (bmi < 30) best='XL';
            else best='XXL';
        }

        const order = ['XS','S','M','L','XL','XXL'];
        let idx = Math.max(0, order.indexOf(best) + adj);
        best = order[Math.round(idx)] || best;

        // Điều chỉnh theo form người
        if (fit === 'slim'){
            const i = order.indexOf(best);
            if (i > 0) best = order[i-1];
        } else if (fit === 'relaxed'){
            const i = order.indexOf(best);
            if (i < order.length - 1) best = order[i+1];
        }
        return best;
    }

    function renderRecommendation(){
        const h = parseInt(heightEl.value)||0;
        const w = parseFloat(weightEl.value)||0;
        const gender = (genderEl.value||'auto');
        const fit = (fitEl.value||'regular');
        
        resultEl.textContent = '';
        applyBtn.disabled = true;
        
        if (!h || !w){
            resultEl.textContent = 'Nhập chiều cao và cân nặng để gợi ý size.';
            return;
        }
        
        // Giả sử lấy giới tính mặc định từ đâu đó, ở đây fix cứng 'nam' nếu auto
        const g = (gender === 'auto') ? 'nam' : gender;
        const target = recommendSize(h, w, g, fit);
        
        // Tìm size khả dụng gần nhất
        const available = getAvailableSizes();
        let pick = null;
        
        if (available.includes(target)) {
            pick = target;
        } else {
            // Tìm size lân cận nếu size chuẩn hết hàng
            const order = ['XS','S','M','L','XL','XXL','3XL'];
            const idx = order.indexOf(target);
            if (idx !== -1) {
                for (let step=1; step < order.length; step++){
                    const left = order[idx - step];
                    const right = order[idx + step];
                    if (left && available.includes(left)) { pick = left; break; }
                    if (right && available.includes(right)) { pick = right; break; }
                }
            }
            if(!pick) pick = available[0] || null;
        }

        if (!pick){
            resultEl.textContent = 'Size phù hợp hiện đã hết hàng. Vui lòng chọn màu khác.';
            return;
        }
        
        resultEl.textContent = `Gợi ý: ${pick} (dựa trên ${h}cm / ${w}kg)`;
        applyBtn.disabled = false;
        applyBtn.setAttribute('data-size', pick);
    }

    // --- Event Listeners Size Advisor ---
    if(openBtn) openBtn.onclick = () => { modal.hidden = false; };
    if(closeBtn) closeBtn.onclick = () => { modal.hidden = true; };
    
    if(applyBtn) applyBtn.onclick = () => {
        const size = applyBtn.getAttribute('data-size');
        if (!size || !sizeWrap) return;
        
        const chip = Array.from(sizeWrap.querySelectorAll('.size-chip'))
            .find(c => (c.getAttribute('data-size')||'').toUpperCase() === size.toUpperCase());
            
        if (chip && !chip.classList.contains('sold-out')){
            // Trigger click vào chip size để chọn
            chip.click();
            showToast(`Đã chọn size ${size} theo gợi ý!`, 'success');
        }
        modal.hidden = true;
    };

    [heightEl, weightEl, genderEl, fitEl].forEach(el => {
        if(el) {
            el.oninput = renderRecommendation;
            el.onchange = renderRecommendation;
        }
    });

    // --- Event Listeners Size Guide ---
    if(sizeGuideLink) sizeGuideLink.onclick = (e) => { 
        e.preventDefault(); 
        if(sgOverlay) sgOverlay.hidden = false; 
    };
    if(sgClose) sgClose.onclick = () => { if(sgOverlay) sgOverlay.hidden = true; };
    if(sgOverlay) sgOverlay.onclick = (e) => { if(e.target === sgOverlay) sgOverlay.hidden = true; };


    // ===== 5. XỬ LÝ NÚT TĂNG GIẢM SỐ LƯỢNG =====
    const minusBtn = document.getElementById('qtyMinus');
    const plusBtn = document.getElementById('qtyPlus');
    const qtyInput = document.getElementById('qtyInput');

    if(minusBtn) {
        // Clone để xóa event cũ
        const newMinus = minusBtn.cloneNode(true);
        minusBtn.parentNode.replaceChild(newMinus, minusBtn);
        newMinus.addEventListener('click', () => {
            const input = document.getElementById('qtyInput');
            let val = parseInt(input.value) || 1;
            input.value = Math.max(1, val - 1);
        });
    }

    if(plusBtn) {
        const newPlus = plusBtn.cloneNode(true);
        plusBtn.parentNode.replaceChild(newPlus, plusBtn);
        newPlus.addEventListener('click', () => {
            const input = document.getElementById('qtyInput');
            let val = parseInt(input.value) || 1;
            // Giới hạn max 5 hoặc tùy ý
            if(val >= 5) {
                showToast('Chỉ được mua tối đa 5 sản phẩm', 'warning');
                return;
            }
            input.value = val + 1;
        });
    }

    if(qtyInput) {
        qtyInput.addEventListener('input', function() {
            let val = this.value.replace(/\D/g, '');
            if (val === '' || val === '0') {
                this.value = 1;
            } else {
                this.value = Math.max(1, Math.min(5, parseInt(val)));
            }
        });
    }

    // ===== 6. XỬ LÝ FORM ADD TO CART (Fix lỗi lặp) =====
    const addToCartForm = document.getElementById('addToCartForm');
    
    if (addToCartForm) {
        // CLONE node form để xóa sạch các event listener cũ bị gán lặp lại
        const newForm = addToCartForm.cloneNode(true);
        addToCartForm.parentNode.replaceChild(newForm, addToCartForm);

        newForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 6.1. Kiểm tra Client-side
            const activeChip = document.querySelector('#sizeList .size-chip.active');
            const qtyIn = document.getElementById('qtyInput');
            const qty = parseInt(qtyIn ? qtyIn.value : 1) || 1;

            if (activeChip) {
                const stock = parseInt(activeChip.getAttribute('data-stock'));
                // Nếu hết hàng
                if (!isNaN(stock) && stock <= 0) {
                    showToast('Sản phẩm biến thể này tạm hết hàng!', 'error');
                    return;
                }
                // Nếu mua quá số lượng
                if (!isNaN(stock) && qty > stock) {
                    showToast(`Kho chỉ còn ${stock} sản phẩm!`, 'warning');
                    return;
                }
            } else if (document.querySelector('#sizeList .size-chip')) {
                // Có list size mà chưa chọn
                showToast('Vui lòng chọn kích thước!', 'warning');
                return;
            }
            
            // 6.2. Gửi Ajax
            const formData = new FormData(this);
            const btn = this.querySelector('.add-cart-btn');
            const oldText = btn ? btn.innerHTML : 'Thêm vào giỏ';
            
            if(btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                btn.disabled = true;
            }

            fetch('/web_qlsp/cart/add_to_cart', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                let data = null;
                try { data = await response.json(); } catch(e) {}
                
                if (response.ok && data && data.success) {
                    showToast('Đã thêm vào giỏ hàng thành công!', 'success');
                    if(typeof updateCartCount === 'function') updateCartCount();
                    else window.location.reload(); // Fallback nếu ko có hàm update
                } else {
                    const msg = (data && data.message) ? data.message : 'Có lỗi xảy ra!';
                    showToast(msg, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Lỗi kết nối server!', 'error');
            })
            .finally(() => {
                if(btn) {
                    btn.innerHTML = oldText;
                    btn.disabled = false;
                }
            });
        });
    }

    // Toggle Description
    const toggleBtn = document.getElementById('descToggle');
    const content = document.getElementById('descContent');
    if(toggleBtn && content){
        // Clone button để xóa event cũ
        const newToggle = toggleBtn.cloneNode(true);
        toggleBtn.parentNode.replaceChild(newToggle, toggleBtn);
        
        newToggle.addEventListener('click', () => {
            content.classList.toggle('expanded');
            newToggle.textContent = content.classList.contains('expanded') ? 'THU GỌN' : 'XEM THÊM';
        });
    }

    // Hàm cập nhật số lượng giỏ hàng
    window.updateCartCount = function() {
        fetch('/web_qlsp/cart/get_cart_count')
            .then(res => res.json())
            .then(data => {
                const el = document.getElementById('cart-count');
                if (el) el.textContent = data.count;
            })
            .catch(e => console.log(e));
    };
});