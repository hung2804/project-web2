<?php
include '../components/connect.php';

// Xử lý dữ liệu từ form
if(isset($_POST['submit'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Truy vấn cơ sở dữ liệu để lấy thông tin top 5 khách hàng mua hàng nhiều nhất
    $select_customers = $conn->prepare("SELECT users.id, users.name, SUM(orders.total_price) AS total_purchase
        FROM orders
        INNER JOIN users ON orders.user_id = users.id
        WHERE orders.placed_on BETWEEN ? AND ?
        AND orders.payment_status = 'completed'  -- Chỉ lấy các đơn hàng đã thanh toán
        GROUP BY users.id
        ORDER BY total_purchase DESC
        LIMIT 5");
    $select_customers->execute([$start_date, $end_date]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Top Customers</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Sử dụng Bootstrap CSS -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="../css/stlye1.css">
</head>
<body>
    <?php include '../components/admin_header.php' ?>
    <section class="container my-5">
        <h1 class="text-center mb-4">Top Customers</h1>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <form method="POST" action="" class="mb-4">
                <div class="form-group">
                    <label for="start_date">From Date:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_date">To Date:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Get Statistics</button>
                </form>
                <div class="card">
                <div class="card-body">
                    <?php if(isset($select_customers) && $select_customers->rowCount() > 0): ?>
                        <h5 class="card-title">Top 5 Customers</h5>
                        <ul class="list-group list-group-flush">
                            <?php while($customer = $select_customers->fetch(PDO::FETCH_ASSOC)): ?>
                            <li class="list-group-item">
                                <div class="customer-info">
                                    <span class="font-weight-bold">Name:</span>
                                    <span><?= $customer['name']; ?></span>
                                </div>
                                <div class="customer-info">
                                    <span class="font-weight-bold">Total Purchase:</span>
                                    <span class="float-right">$<?= $customer['total_purchase']; ?></span>
                                </div>
                                <div class="orders-list">
                                    <?php
                                    $select_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND payment_status = 'completed'");
                                    $select_orders->execute([$customer['id']]);
                                    ?>
                                    <ul class="list-unstyled">
                                        <?php while($order = $select_orders->fetch(PDO::FETCH_ASSOC)): ?>
                                        <li>
                                            <a href="order_details.php?order_id=<?= $order['id']; ?>">
                                                Order ID: <?= $order['id']; ?> - Total: $<?= $order['total_price']; ?>
                                            </a>
                                        </li>
                                        <?php endwhile; ?>
                                    </ul>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="card-text">No data available for the selected period.</p>
                    <?php endif; ?>
                </div>

                </div>
            </div>
        </div>
    </section>
    <!-- Sử dụng Bootstrap JavaScript và jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
