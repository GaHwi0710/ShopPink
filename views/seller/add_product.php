<div class="container">
    <h1 class="page-title">Thêm sản phẩm mới</h1>
    
    <div class="seller-form-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="/seller/add-product" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Tên sản phẩm</label>
                <input type="text" id="name" name="name" placeholder="Nhập tên sản phẩm" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả sản phẩm</label>
                <textarea id="description" name="description" rows="5" placeholder="Nhập mô tả sản phẩm"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Giá sản phẩm (VNĐ)</label>
                    <input type="number" id="price" name="price" placeholder="Nhập giá sản phẩm" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Số lượng tồn kho</label>
                    <input type="number" id="stock" name="stock" placeholder="Nhập số lượng tồn kho" min="0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">Hình ảnh sản phẩm</label>
                <div class="image-upload">
                    <div class="image-preview" id="image-preview">
                        <i class="fas fa-image"></i>
                        <span>Chọn hình ảnh</span>
                    </div>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Thêm sản phẩm</button>
                <a href="/seller/dashboard" class="btn-cancel">Hủy</a>
            </div>
        </form>
    </div>
</div>