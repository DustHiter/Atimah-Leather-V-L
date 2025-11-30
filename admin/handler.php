session_start();
require_once __DIR__ . '/../db/config.php';

require_once __DIR__ . '/auth_check.php';

$action = $_REQUEST['action'] ?? '';

$pdo = db();

// Default redirect location
$redirect_to = 'index.php';

switch ($action) {
    case 'add':
        $redirect_to = 'add_product.php'; // Redirect back to form on error
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            $colors = trim($_POST['colors'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            $errors = [];

            // Validation
            if (empty($name)) $errors[] = "Product name is required.";
            if (empty($description)) $errors[] = "Description is required.";
            if ($price === false) $errors[] = "Price is invalid or missing.";

            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../assets/images/products/';
                 if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        $errors[] = "Image directory does not exist and could not be created.";
                    }
                }
                
                if (!is_writable($upload_dir)) {
                     $errors[] = "Image directory is not writable. Please check server permissions.";
                } else {
                    $filename = uniqid('product_', true) . '_' . basename($_FILES['image']['name']);
                    $target_file = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = 'assets/images/products/' . $filename;
                    } else {
                        $errors[] = "Failed to move uploaded file.";
                    }
                }
            } else {
                 $file_error = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
                 $upload_errors = [
                    UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the server's maximum upload size (upload_max_filesize).",
                    UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the maximum size specified in the form.",
                    UPLOAD_ERR_PARTIAL    => "The file was only partially uploaded.",
                    UPLOAD_ERR_NO_FILE    => "No file was selected for upload.",
                    UPLOAD_ERR_NO_TMP_DIR => "Server configuration error: Missing a temporary folder for uploads.",
                    UPLOAD_ERR_CANT_WRITE => "Server error: Failed to write the uploaded file to disk.",
                    UPLOAD_ERR_EXTENSION  => "A PHP extension prevented the file upload.",
                ];
                $error_message = $upload_errors[$file_error] ?? "An unknown upload error occurred (Code: {$file_error}).";
                // Only trigger error if the action is 'add', where image is mandatory
                if ($action === 'add') {
                    $errors[] = "Image Upload Failed: " . $error_message;
                }
            }

            if (empty($errors)) {
                try {
                    $sql = "INSERT INTO products (name, description, price, image_url, colors, is_featured) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $description, $price, $image_path, $colors, $is_featured]);
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'محصول با موفقیت اضافه شد!'];
                    $redirect_to = 'index.php';
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'خطا در افزودن محصول: ' . $e->getMessage()];
                }
            } else {
                $error_message = 'لطفاً تمام خطاها را برطرف کنید:<br><br>' . implode('<br>', $errors);
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $error_message];
            }
        }
        break;

    case 'edit':
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $redirect_to = 'edit_product.php?id=' . $id;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            $colors = trim($_POST['colors'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            $errors = [];
            
            if (!$id) {
                $errors[] = "شناسه محصول نامعتبر است.";
            }
            // Other validations...

            $image_path = $_POST['current_image'] ?? '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../assets/images/products/';
                $filename = uniqid('product_', true) . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    if (!empty($image_path) && file_exists(__DIR__ . '/../' . $image_path)) {
                        unlink(__DIR__ . '/../' . $image_path);
                    }
                    $image_path = 'assets/images/products/' . $filename;
                } else {
                    $errors[] = "خطا در آپلود تصویر جدید.";
                }
            }

            if (empty($errors)) {
                try {
                    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, colors = ?, is_featured = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $description, $price, $image_path, $colors, $is_featured, $id]);
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'محصول با موفقیت ویرایش شد!'];
                    $redirect_to = 'index.php';
                } catch (PDOException $e) {
                     $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'خطا در ویرایش محصول: ' . $e->getMessage()];
                }
            } else {
                $error_message = 'فرم دارای خطا است:<br><br>' . implode('<br>', $errors);
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => $error_message];
            }
        }
        break;

    case 'delete':
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id) {
            try {
                // First, get the image path to delete the file
                $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $image_to_delete = $stmt->fetchColumn();

                // Delete the record
                $sql = "DELETE FROM products WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);

                // If record deleted, delete the file
                if ($stmt->rowCount() > 0 && $image_to_delete && file_exists(__DIR__ . '/../' . $image_to_delete)) {
                    unlink(__DIR__ . '/../' . $image_to_delete);
                }

                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'محصول با موفقیت حذف شد.'];
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'خطا در حذف محصول: ' . $e->getMessage()];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'شناسه محصول نامعتبر است.'];
        }
        $redirect_to = 'index.php';
        break;
}

// Redirect back after the action
header('Location: ' . $redirect_to);
exit;
