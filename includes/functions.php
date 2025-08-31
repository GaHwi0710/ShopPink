<?php
// Product functions
function getProducts($limit = 12, $offset = 0, $category_id = null, $search = null) {
    global $conn;
    
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    $param_types = "";
    
    if ($category_id) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_id;
        $param_types .= "i";
    }
    
    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= "ss";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    $query = "
        SELECT p.*, c.name as category_name, 
               COALESCE(AVG(pr.rating), 0) as avg_rating,
               COUNT(pr.id) as review_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id 
        WHERE $where_clause 
        GROUP BY p.id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= "ii";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    
    return $stmt->get_result();
}

function getProductById($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating,
               COUNT(pr.id) as review_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id 
        WHERE p.id = ? AND p.status = 'active'
        GROUP BY p.id
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

function getFeaturedProducts($limit = 8) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id 
        WHERE p.status = 'active' AND p.is_featured = 1
        GROUP BY p.id 
        ORDER BY p.created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result();
}

function getBestsellerProducts($limit = 8) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id 
        WHERE p.status = 'active'
        GROUP BY p.id 
        ORDER BY p.sold_count DESC, p.created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result();
}

function getRecentlyViewedProducts($user_id, $limit = 4) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id 
        WHERE p.status = 'active' AND p.id IN (
            SELECT DISTINCT product_id FROM user_views WHERE user_id = ?
        )
        GROUP BY p.id 
        ORDER BY MAX(uv.viewed_at) DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    
    return $stmt->get_result();
}

function getProductsByCategory($category_id, $limit = 12, $offset = 0) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating,
               COUNT(pr.id) as review_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id 
        WHERE p.status = 'active' AND p.category_id = ?
        GROUP BY p.id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $category_id, $limit, $offset);
    $stmt->execute();
    
    return $stmt->get_result();
}

// Category functions
function getCategories($parent_id = null) {
    global $conn;
    
    $where_clause = $parent_id === null ? "WHERE c.status = 'active'" : "WHERE c.status = 'active' AND c.parent_id = ?";
    $query = "
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        $where_clause
        GROUP BY c.id 
        ORDER BY c.sort_order, c.name
    ";
    
    $stmt = $conn->prepare($query);
    if ($parent_id !== null) {
        $stmt->bind_param("i", $parent_id);
    }
    $stmt->execute();
    
    return $stmt->get_result();
}

function getCategoryById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Cart functions
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Cập nhật cookie
    setcookie('cart', json_encode($_SESSION['cart']), time() + CART_EXPIRY, '/');
}

function updateCart($product_id, $quantity) {
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
        
        // Cập nhật cookie
        setcookie('cart', json_encode($_SESSION['cart']), time() + CART_EXPIRY, '/');
        return true;
    }
    return false;
}

function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        
        // Cập nhật cookie
        setcookie('cart', json_encode($_SESSION['cart']), time() + CART_EXPIRY, '/');
        return true;
    }
    return false;
}

function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    global $conn;
    
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id IN ($placeholders) AND p.status = 'active'
    ");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    
    $products = $stmt->get_result();
    $cart_items = [];
    
    while ($product = $products->fetch_assoc()) {
        $product['quantity'] = $_SESSION['cart'][$product['id']];
        $product['subtotal'] = $product['price'] * $product['quantity'];
        $cart_items[] = $product;
    }
    
    return $cart_items;
}

function getCartTotal() {
    $cart_items = getCartItems();
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['subtotal'];
    }
    
    return $total;
}

function clearCart() {
    unset($_SESSION['cart']);
    setcookie('cart', '', time() - 3600, '/');
}

// Order functions
function createOrder($user_id, $shipping_info, $cart_items, $payment_method, $notes = '') {
    global $conn;
    
    $conn->begin_transaction();
    
    try {
        // Tạo đơn hàng
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, full_name, phone, email, address, city, 
                               payment_method, notes, subtotal, shipping_fee, total_amount, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $subtotal = getCartTotal();
        $shipping_fee = calculateShippingFee($shipping_info['city']);
        $total_amount = $subtotal + $shipping_fee;
        
        $stmt->bind_param("issssssddds", 
            $user_id, 
            $shipping_info['full_name'], 
            $shipping_info['phone'], 
            $shipping_info['email'], 
            $shipping_info['address'], 
            $shipping_info['city'],
            $payment_method, 
            $notes, 
            $subtotal, 
            $shipping_fee, 
            $total_amount
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Lỗi tạo đơn hàng");
        }
        
        $order_id = $conn->insert_id;
        
        // Tạo chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("
                INSERT INTO order_details (order_id, product_id, product_name, price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("iisidi", 
                $order_id, 
                $item['id'], 
                $item['name'], 
                $item['price'], 
                $item['quantity'], 
                $item['subtotal']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Lỗi tạo chi tiết đơn hàng");
            }
            
            // Cập nhật số lượng đã bán
            $stmt = $conn->prepare("
                UPDATE products SET sold_count = sold_count + ? WHERE id = ?
            ");
            $stmt->bind_param("ii", $item['quantity'], $item['id']);
            $stmt->execute();
        }
        
        $conn->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $conn->rollback();
        log_error("Lỗi tạo đơn hàng: " . $e->getMessage());
        return false;
    }
}

function calculateShippingFee($city) {
    // Logic tính phí vận chuyển theo thành phố
    $shipping_fees = [
        'Hà Nội' => 0,
        'TP. Hồ Chí Minh' => 0,
        'Đà Nẵng' => 15000,
        'Hải Phòng' => 20000,
        'Cần Thơ' => 25000
    ];
    
    return $shipping_fees[$city] ?? 30000;
}

// User functions
function authenticateUser($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

function createUser($username, $password, $full_name, $phone, $email = '', $address = '') {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO users (username, password, full_name, phone, email, address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("ssssss", $username, $hashed_password, $full_name, $phone, $email, $address);
    
    return $stmt->execute();
}

function updateUserProfile($user_id, $data) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE users 
        SET full_name = ?, phone = ?, email = ?, address = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->bind_param("ssssi", 
        $data['full_name'], 
        $data['phone'], 
        $data['email'], 
        $data['address'], 
        $user_id
    );
    
    return $stmt->execute();
}

function changePassword($user_id, $new_password) {
    global $conn;
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    return $stmt->execute();
}

// Review functions
function addProductReview($user_id, $product_id, $rating, $comment) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO product_reviews (user_id, product_id, rating, comment, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
    
    return $stmt->execute();
}

function updateProductReview($review_id, $user_id, $rating, $comment) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE product_reviews 
        SET rating = ?, comment = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);
    
    return $stmt->execute();
}

function deleteProductReview($review_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM product_reviews WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $review_id, $user_id);
    
    return $stmt->execute();
}

function getProductReviews($product_id, $limit = 10, $offset = 0) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT pr.*, u.full_name, u.username
        FROM product_reviews pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.product_id = ?
        ORDER BY pr.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("iii", $product_id, $limit, $offset);
    $stmt->execute();
    
    return $stmt->get_result();
}
?>