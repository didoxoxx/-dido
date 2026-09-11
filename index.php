<?php
require_once 'server/config/db.php';
require_once 'server/config/session_start.php';

// ดึงหมวดหมู่ทั้งหมด
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

// ดึงสินค้า (กรองตามหมวดหมู่ถ้ามี)
$catFilter = $_GET['cat'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.status = 'active'";
$params = [];

if ($catFilter) {
    $sql .= " AND p.category_id = ?";
    $params[] = $catFilter;
}
if ($search) {
    $sql .= " AND p.name LIKE ?";
    $params[] = '%' . $search . '%';
}
$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// นับตะกร้า
$cartCount = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartCount += $qty;
}
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ອາຫານທະເລແຊ່ແຂງ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/cart.css">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <main class="main-container">

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fa-solid fa-fire" style="color: #FFB020;"></i> ສົດໃໝ່ທຸກວັນ • ສົ່ງເຖິງປະຕູ
                </div>

                <h1 class="hero-title">
                    ອາຫານທະເລແຊ່ແຂງ<br>
                    <span class="text-cyan">ສົດ ສະອາດ ປອດໄພ</span>
                </h1>

                <p class="hero-desc">
                    ກຸ້ງ • ປາ • ປູ • ປາມຶກ • ຫອຍ — ຄັດສັນຄຸນນະພາບສູງ ແຊ່ແຂງ -18°C ຮັກສາລົດຊາດສົດໃໝ່ ສົ່ງເຖິງເຮືອນພາຍໃນ
                    24 ຊົ່ວໂມງ.
                </p>

                <form class="search-box" method="GET" action="index.php">
                    <input type="text" name="search" placeholder="ຄົ້ນຫາສິນຄ້າ ເຊັ່ນ: ກຸ້ງ, ປາແຊລມອນ..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-search">
                        <i class="fa-solid fa-magnifying-glass"></i> ຄົ້ນຫາ
                    </button>
                </form>
            </div>
            <div class="hero-visual">
                <div class="premium-card">
                    <div class="card-logo">
                        <img src="image/dd.png" alt="DDFOOD Premium">
                    </div>
                    <h3>DDFOOD Premium</h3>
                    <p class="card-subtitle">ບໍລິການພິເສດ</p>

                    <div class="card-stats">
                        <div class="stat-item">
                            <h4 class="text-cyan"><?= count($products) ?>+</h4>
                            <p>ຊະນິດ</p>
                        </div>
                        <div class="stat-item">
                            <h4 class="text-cyan">5</h4>
                            <p>ດາວ</p>
                        </div>
                        <div class="stat-item">
                            <h4 class="text-cyan">24h</h4>
                            <p>ຈັດສົ່ງ</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories-section">
            <div class="section-heading">
                <h2>ໝວດໝູ່ສິນຄ້າ</h2>
                <p>ເລືອກປະເພດທີ່ຕ້ອງການ</p>
            </div>

            <div class="category-tags">
                <a href="index.php" class="tag <?= $catFilter === '' ? 'active' : '' ?>"><i class="fa-solid fa-grip"></i> ທັງໝົດ</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?cat=<?= $cat['id'] ?>" class="tag <?= $catFilter == $cat['id'] ? 'active' : '' ?>"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="products-section">
            <div class="product-grid">
                <?php foreach ($products as $prod): ?>
                <div class="product-card">
                    <div class="product-img">
                        <?php if ($prod['image']): ?>
                            <img src="image/<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
                        <?php else: ?>
                            <i class="fa-regular fa-image"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <p class="product-category"><?= htmlspecialchars($prod['category_name'] ?? '') ?></p>
                        <h3 class="product-name"><?= htmlspecialchars($prod['name']) ?></h3>
                        <div class="product-price-row">
                            <span class="price"><?= number_format($prod['price']) ?> ₭/kg</span>
                            <span class="stock">ຍັງເຫຼືອ <?= $prod['stock'] ?> kg</span>
                        </div>
                        <button class="btn-add-cart" data-id="<?= $prod['id'] ?>"><i class="fa-solid fa-cart-plus"></i> ເພີ່ມໃສ່ກະຕ່າ</button>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <p style="color:#8A9BB3;text-align:center;grid-column:1/-1;padding:40px;">ບໍ່ພົບສິນຄ້າ</p>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- =================== ส่วนที่ 2: ตะกร้าสินค้า (Cart Sidebar) =================== -->
    <?php include 'include/cart.php'; ?>

    <!-- =================== Footer =================== -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <a href="index.php" class="logo">
                    <img src="image/dd.png" alt="DDFOOD">
                    <span class="logo-text">DD<span>FOOD</span></span>
                </a>
                <p>ອາຫານທະເລແຊ່ແຂງ ສົດ ສະອາດ ປອດໄພ ຄັດສັນຄຸນນະພາບສູງ ພ້ອມຈັດສົ່ງເຖິງທີ່ພາຍໃນ 24 ຊົ່ວໂມງ.</p>
            </div>

            <div class="footer-links">
                <h4>ເມນູອື່ນໆ</h4>
                <ul>
                    <li><a href="/do/index.php"><i class="fa-solid fa-angle-right"></i> ຫນ້າຫລັກ</a></li>
                    <li><a href="/do/index.php"><i class="fa-solid fa-angle-right"></i> ສິນຄ້າທັງໝົດ</a></li>
                    <li><a href="/do/pages/history.php"><i class="fa-solid fa-angle-right"></i> ປະຫວັດການຊື້</a></li>
                    <li><a href="/do/pages/payment.php"><i class="fa-solid fa-angle-right"></i> ກະຕ່າ</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>ຕິດຕໍ່ພວກເຮົາ</h4>
                <p><i class="fa-solid fa-phone"></i> +856 20 1234 5678</p>
                <p><i class="fa-brands fa-whatsapp"></i> +856 20 9876 5432</p>
                <p><i class="fa-solid fa-envelope"></i> contact@ddFOOD.com</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 DDFOOD. All Rights Reserved. ແຕ່ງໂດຍ Ton</p>
        </div>
    </footer>
    <?php include 'include/login_modal.php'; ?>
    <?php include 'include/loading.php'; ?>
</body>
<script src="js/cart.js"></script>
<script src="js/login.js"></script>

</html>
