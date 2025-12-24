<?php
session_start();
require_once 'db/config.php';
require_once 'includes/jdf.php'; // For Jalali date conversion

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = db();

// Handle form submissions for account page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $redirect_page = $_GET['page'] ?? 'dashboard';

    try {
        if ($action === 'update_details') {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($first_name) || empty($last_name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('لطفاً تمام فیلدها را به درستی پر کنید.');
            }
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $user_id]);
            $_SESSION['profile_message'] = 'اطلاعات شما با موفقیت به‌روزرسانی شد.';
            $_SESSION['profile_message_type'] = 'success';
            $redirect_page = 'account';
        } elseif ($action === 'update_password') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (strlen($new_password) < 8) {
                throw new Exception('رمز عبور جدید باید حداقل ۸ کاراکتر باشد.');
            } elseif ($new_password !== $confirm_password) {
                throw new Exception('رمزهای عبور جدید با هم مطابقت ندارند.');
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $_SESSION['profile_message'] = 'رمز عبور شما با موفقیت تغییر کرد.';
            $_SESSION['profile_message_type'] = 'success';
            $redirect_page = 'account';

        } elseif ($action === 'add_address') {
            $province = trim($_POST['province'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $address_line = trim($_POST['address_line'] ?? '');
            $postal_code = trim($_POST['postal_code'] ?? '');
            $is_default = isset($_POST['is_default']);
             if (empty($province) || empty($city) || empty($address_line) || empty($postal_code)) {
                throw new Exception('لطفاً تمام فیلدهای آدرس را پر کنید.');
            }

            $pdo->beginTransaction();
            if ($is_default) {
                $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }
            $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, province, city, address_line, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $province, $city, $address_line, $postal_code, $is_default ? 1 : 0]);
            $pdo->commit();

            $_SESSION['profile_message'] = 'آدرس جدید با موفقیت اضافه شد.';
            $_SESSION['profile_message_type'] = 'success';
            $redirect_page = 'addresses';
        } elseif ($action === 'delete_address') {
            $address_id = $_POST['address_id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            if (!$stmt->execute([$address_id, $user_id])) throw new Exception('خطا در حذف آدرس.');
            
            $_SESSION['profile_message'] = 'آدرس با موفقیت حذف شد.';
            $_SESSION['profile_message_type'] = 'success';
            $redirect_page = 'addresses';
        } elseif ($action === 'set_default_address') {
            $address_id = $_POST['address_id'] ?? 0;
            $pdo->beginTransaction();
            $stmt1 = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt1->execute([$user_id]);
            $stmt2 = $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            $stmt2->execute([$address_id, $user_id]);
            $pdo->commit();
            $_SESSION['profile_message'] = 'آدرس پیش‌فرض با موفقیت تغییر کرد.';
            $_SESSION['profile_message_type'] = 'success';
            $redirect_page = 'addresses';
        }
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e->errorInfo[1] == 1062) { // Duplicate entry
            $_SESSION['profile_message'] = 'این ایمیل قبلاً ثبت شده است.';
        } else {
            $_SESSION['profile_message'] = 'یک خطای پایگاه داده رخ داد: ' . $e->getMessage();
        }
        $_SESSION['profile_message_type'] = 'danger';
    } catch (Exception $e) {
        $_SESSION['profile_message'] = $e->getMessage();
        $_SESSION['profile_message_type'] = 'danger';
    }

    header('Location: profile.php?page=' . $redirect_page);
    exit;
}

// Determine current page
$page = $_GET['page'] ?? 'dashboard';
$page_map = [
    'dashboard' => 'داشبورد',
    'orders' => 'سفارشات من',
    'addresses' => 'آدرس‌های من',
    'account' => 'جزئیات حساب',
];
$page_title = $page_map[$page] ?? 'حساب کاربری';

// Retrieve flash message
if (isset($_SESSION['profile_message'])) {
    $flash_message = $_SESSION['profile_message'];
    $flash_message_type = $_SESSION['profile_message_type'];
    unset($_SESSION['profile_message']);
    unset($_SESSION['profile_message_type']);
}

// Fetch all necessary data
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

$stmt_addresses = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt_addresses->execute([$user_id]);
$addresses = $stmt_addresses->fetchAll(PDO::FETCH_ASSOC);

$total_purchase_amount = array_reduce($orders, function ($sum, $order) {
    return strtolower($order['status']) === 'completed' ? $sum + $order['total_amount'] : $sum;
}, 0);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - پنل کاربری</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.0.3/Vazirmatn-font-face.css">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin/assets/css/admin_style.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Minor adjustments for profile page to match admin styles */
        .admin-main-content { background-color: var(--admin-bg); }
        .table th, .table td { vertical-align: middle; }
        .form-label { font-weight: 600; color: var(--admin-text-muted); }
        .card-header h4 { margin: 0; font-size: 1.1rem; }
        .order-status {
            padding: 0.25em 0.6em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 50rem;
            display: inline-block;
            line-height: 1;
        }
        .order-status.status-completed { background: var(--admin-success); color: #111; }
        .order-status.status-pending { background: var(--admin-warning); color: #111; }
        .order-status.status-shipped { background: var(--admin-info); color: #111; }
        .order-status.status-cancelled { background: var(--admin-danger); color: #fff; }

        .stat-cards-grid-reports {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card-report {
            background-color: var(--admin-card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--admin-border);
        }
        .stat-card-report p { margin-bottom: 0.5rem; color: var(--admin-text-muted); }
        .stat-card-report h3 { margin: 0; font-size: 2rem; color: var(--admin-text); }
    </style>
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2><a href="index.php">آتیمه<span>.</span></a></h2>
        </div>
        <ul class="admin-nav">
            <li>
                <a class="admin-nav-link <?= ($page === 'dashboard') ? 'active' : '' ?>" href="profile.php?page=dashboard">
                    <i class="fas fa-tachometer-alt"></i><span>داشبورد</span>
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= ($page === 'orders') ? 'active' : '' ?>" href="profile.php?page=orders">
                    <i class="fas fa-clipboard-list"></i><span>سفارشات من</span>
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= ($page === 'addresses') ? 'active' : '' ?>" href="profile.php?page=addresses">
                    <i class="fas fa-map-marker-alt"></i><span>آدرس‌های من</span>
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= ($page === 'account') ? 'active' : '' ?>" href="profile.php?page=account">
                    <i class="fas fa-user-cog"></i><span>جزئیات حساب</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="index.php"><i class="fas fa-home fa-fw"></i> <span>بازگشت به سایت</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> <span>خروج از حساب</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main-content">
         <header class="admin-header-bar">
            <button id="sidebar-toggle" class="btn d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            <div class="admin-header-title">
                <h1><?= htmlspecialchars($page_title) ?></h1>
            </div>
        </header>
        
        <main>
            <?php if (isset($flash_message)): ?>
                <script>
                    Swal.fire({
                        title: '<?= ($flash_message_type === 'success') ? 'موفق' : 'خطا' ?>',
                        text: '<?= addslashes(htmlspecialchars($flash_message)) ?>',
                        icon: '<?= htmlspecialchars($flash_message_type) ?>',
                        confirmButtonText: 'باشه'
                    });
                </script>
            <?php endif; ?>

            <?php if ($page === 'dashboard'): ?>
                <div class="card mb-4" style="background-color: var(--admin-card-bg); border-color: var(--admin-border);">
                    <div class="card-body">
                        <h3 style="color: var(--admin-text);">سلام، <?= htmlspecialchars($user['first_name'] ?? 'کاربر'); ?> عزیز!</h3>
                        <p class="text-muted">به پنل کاربری خود خوش آمدید. از اینجا می‌توانید آخرین سفارشات خود را مشاهده کرده و حساب خود را مدیریت کنید.</p>
                    </div>
                </div>
                <div class="stat-cards-grid-reports">
                    <div class="stat-card-report">
                        <p>تعداد کل سفارشات</p>
                        <h3><?= count($orders); ?></h3>
                    </div>
                    <div class="stat-card-report">
                        <p>مجموع خرید (تکمیل شده)</p>
                        <h3><?= number_format($total_purchase_amount); ?> تومان</h3>
                    </div>
                </div>

            <?php elseif ($page === 'orders'): ?>
                <div class="card">
                    <div class="card-header"><h4>تاریخچه سفارشات</h4></div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <p class="text-center text-muted">شما هنوز هیچ سفارشی ثبت نکرده‌اید.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>تاریخ</th>
                                            <th>وضعیت</th>
                                            <th>مبلغ کل</th>
                                            <th class="text-end">رهگیری</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($order['id']); ?></td>
                                                <td><?= jdate('d F Y', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <span class="order-status status-<?= strtolower(htmlspecialchars($order['status'])) ?>">
                                                        <?= htmlspecialchars($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?= number_format($order['total_amount']); ?> تومان</td>
                                                <td class="text-end">
                                                    <a href="track_order.php?tracking_id=<?= htmlspecialchars($order['tracking_id']); ?>" class="btn btn-sm btn-outline-primary">نمایش</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($page === 'addresses'): ?>
                <div class="card">
                     <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>آدرس‌های من</h4>
                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#add-address-form">
                            <i class="fas fa-plus"></i> افزودن آدرس
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="collapse mb-4" id="add-address-form">
                            <form method="POST" action="profile.php?page=addresses">
                                <input type="hidden" name="action" value="add_address">
                                <div class="row">
                                    <div class="col-md-4 mb-3"><label class="form-label">استان</label><input type="text" class="form-control" name="province" required></div>
                                    <div class="col-md-4 mb-3"><label class="form-label">شهر</label><input type="text" class="form-control" name="city" required></div>
                                    <div class="col-md-4 mb-3"><label class="form-label">کد پستی</label><input type="text" class="form-control" name="postal_code" required></div>
                                </div>
                                <div class="mb-3"><label class="form-label">آدرس کامل</label><textarea class="form-control" name="address_line" rows="2" required></textarea></div>
                                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_default" id="is_default"><label class="form-check-label" for="is_default">انتخاب به عنوان آدرس پیش‌فرض</label></div>
                                <button type="submit" class="btn btn-success">ذخیره آدرس</button>
                            </form>
                            <hr>
                        </div>

                        <?php if (empty($addresses)): ?>
                            <p class="text-center text-muted">شما هنوز هیچ آدرسی ثبت نکرده‌اید.</p>
                        <?php else: ?>
                            <?php foreach ($addresses as $address): ?>
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <p class="mb-0" style="color: var(--admin-text);"><?= htmlspecialchars(implode(', ', array_filter([$address['province'], $address['city'], $address['address_line'], "کدپستی: ".$address['postal_code']]))) ?></p>
                                        <?php if ($address['is_default']): ?><span class="badge bg-primary">پیش‌فرض</span><?php endif; ?>
                                    </div>
                                    <div class="d-flex">
                                        <?php if (!$address['is_default']): ?>
                                            <form method="POST" action="profile.php?page=addresses" class="ms-2">
                                                <input type="hidden" name="action" value="set_default_address">
                                                <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">پیش‌فرض</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="profile.php?page=addresses" onsubmit="return confirm('آیا از حذف این آدرس مطمئن هستید؟');">
                                            <input type="hidden" name="action" value="delete_address">
                                            <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($page === 'account'): ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h4>جزئیات حساب</h4></div>
                            <div class="card-body">
                                <form method="POST" action="profile.php?page=account">
                                    <input type="hidden" name="action" value="update_details">
                                    <div class="mb-3"><label class="form-label">نام</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? ''); ?>" required></div>
                                    <div class="mb-3"><label class="form-label">نام خانوادگی</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? ''); ?>" required></div>
                                    <div class="mb-3"><label class="form-label">آدرس ایمیل</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required></div>
                                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h4>تغییر رمز عبور</h4></div>
                            <div class="card-body">
                                <form method="POST" action="profile.php?page=account">
                                    <input type="hidden" name="action" value="update_password">
                                    <div class="mb-3"><label class="form-label">رمز عبور جدید</label><input type="password" class="form-control" name="new_password" required></div>
                                    <div class="mb-3"><label class="form-label">تکرار رمز عبور جدید</label><input type="password" class="form-control" name="confirm_password" required></div>
                                    <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            
            <?php else: ?>
                <div class="alert alert-danger">صفحه مورد نظر یافت نشد.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<div class="sidebar-backdrop"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.admin-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('show');
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('open');
            backdrop.classList.remove('show');
        });
    }
});
</script>
</body>
</html>