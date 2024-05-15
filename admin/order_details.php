<?php
include '../components/connect.php';

if(isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Truy vấn cơ sở dữ liệu để lấy thông tin chi tiết của đơn hàng
    $select_order = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $select_order->execute([$order_id]);

    if($select_order->rowCount() > 0) {
        $order_details = $select_order->fetch(PDO::FETCH_ASSOC);
        // Lấy thông tin chi tiết của đơn hàng
        $user_id = $order_details['user_id'];
        $total_price = $order_details['total_price'];
        $placed_on = $order_details['placed_on'];
        $email= $order_details['email'];
        $number = $order_details['number'];
        $address = $order_details['address'];
        $total_products= $order_details['total_products'];
        // Các thông tin khác nếu cần

        // Truy vấn cơ sở dữ liệu để lấy thông tin của người dùng đặt hàng
        $select_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $select_user->execute([$user_id]);
        $user_details = $select_user->fetch(PDO::FETCH_ASSOC);
    } else {
        // Đơn hàng không tồn tại
        header('Location: error_page.php'); // Chuyển hướng đến trang thông báo lỗi
        exit(); // Dừng script
    }
} else {
    // Không có tham số order_id trong URL
    header('Location: error_page.php'); // Chuyển hướng đến trang thông báo lỗi
    exit(); // Dừng script
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/style2.css">
    
</head>
<body>
    <?php include '../components/admin_header.php' ?>
    <section class="container">
        <h1 class="text-center my-4">Order Details</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card order-details">
                    <div class="card-body">
                        <h5 class="card-title">Order ID: <?= $order_id; ?></h5>
                        <p><strong>User Name:</strong> <?= $user_details['name']; ?></p>
                        <!-- Hiển thị các thông tin khác của đơn hàng -->
                        <p><strong>Total Price:</strong> $<?= $total_price; ?></p>
                        <p><strong>Placed On:</strong> <?= $placed_on; ?></p>
                        <p><strong>Email:</strong> <?= $email; ?></p>
                        <p><strong>Number:</strong> <?= $number; ?></p>
                        <p><strong>Address:</strong> <?= $address; ?></p>
                        <p><strong>Total Products:</strong> <?= $total_products; ?></p>

                        <!-- Hiển thị hình ảnh -->
                        <?php
                        $image_names = preg_split("/(?<=\.png)(?=\D)/", $order_details['image']);
                        foreach ($image_names as $image) {
                            $split_position = strpos($image, '.png');
                            if ($split_position !== false) {
                                echo '<img src="../uploaded_img/' . substr($image, 0, $split_position) . '.png" alt="Product Image">';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Khai báo các đoạn mã JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
