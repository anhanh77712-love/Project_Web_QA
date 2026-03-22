function confirmDelete(deleteUrl) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Dữ liệu campains sẽ bị mất vĩnh viễn!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Đồng ý!',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            // Nếu người dùng đồng ý, chuyển hướng đến URL xóa
            window.location.href = deleteUrl;
        }
    });
}

function toggleForm() {
    var type = document.getElementById('sType').value;
    var bBanner = document.getElementById('block_banner');
    var bCollection = document.getElementById('block_collection');
    var bFlash = document.getElementById('block_flash');

    bBanner.style.display = 'none';
    bCollection.style.display = 'none';
    bFlash.style.display = 'none';

    if (type === 'overlay_banner') bBanner.style.display = 'block';
    else if (type === 'collection') bCollection.style.display = 'block';
    else if (type === 'flash_sale') {
        bCollection.style.display = 'block';
        bFlash.style.display = 'block';
    }
}

function toggleEditForm() {
    var type = document.getElementById('edit_type').value;
    var bBanner = document.getElementById('edit_block_banner');
    var bCollection = document.getElementById('edit_block_collection');
    var bFlash = document.getElementById('edit_block_flash');

    bBanner.style.display = 'none';
    bCollection.style.display = 'none';
    bFlash.style.display = 'none';

    if (type === 'overlay_banner') bBanner.style.display = 'block';
    else if (type === 'collection') bCollection.style.display = 'block';
    else if (type === 'flash_sale') {
        bCollection.style.display = 'block';
        bFlash.style.display = 'block';
    }
}

function editCampaign(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_title').value = data.title;
    document.getElementById('edit_type').value = data.section_type;
    document.getElementById('edit_bg_color').value = data.bg_color || '#ffffff';
    document.getElementById('edit_text_color').value = data.text_color || '#000000';
    document.getElementById('edit_display_order').value = data.display_order || 0;
    document.getElementById('edit_is_active').checked = data.status == 1;
    
    // Xử lý theo loại
    if(data.section_type === 'overlay_banner') {
        document.getElementById('edit_old_image').value = data.image_url || '';
        document.getElementById('edit_button_text').value = data.button_text || 'XEM NGAY';
        document.getElementById('edit_text_position').value = data.text_position || 'left';
        document.getElementById('edit_link_url').value = data.link_url || '#';
        
        // Hiển thị ảnh hiện tại
        if(data.image_url) {
            document.getElementById('current_image_preview').innerHTML = 
                '<img src="/web_qlsp/Public/Picture/campaigns/' + data.image_url + '" class="img-thumbnail" style="max-height:100px"><br><small class="text-muted">Ảnh hiện tại</small>';
        }
    } else if(data.section_type === 'collection' || data.section_type === 'flash_sale') {
        document.getElementById('edit_collection_id').value = data.collection_id || '';
        if(data.section_type === 'flash_sale') {
            document.getElementById('edit_end_time').value = data.end_time || '';
        }
    }
    
    toggleEditForm();
}

document.addEventListener("DOMContentLoaded", toggleForm);