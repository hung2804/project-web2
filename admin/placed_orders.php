<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};
// Số sản phẩm trên mỗi trang
$products_per_page = 6;

// Xác định trang hiện tại
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Số lượng sản phẩm
$count_orders = $conn->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();

// Tính toán số lượng trang
$total_pages = ceil($count_orders / $products_per_page);

// Xác định OFFSET
$offset = ($current_page - 1) * $products_per_page;


if(isset($_POST['update_payment'])){

   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_status->execute([$payment_status, $order_id]);
   $message[] = 'payment status updated!';

}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>placed orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php' ?>

<!-- placed orders section starts  -->

<section class="placed-orders">

   <h1 class="heading">placed orders</h1>

   <div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> user id : <span><?= $fetch_orders['user_id']; ?></span> </p>
      <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> email : <span><?= $fetch_orders['email']; ?></span> </p>
      <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
      
      <?php
          $image_names = preg_split("/(?<=\.png)(?=\D)/", $fetch_orders['image']);
           foreach ($image_names as $image) {
           $split_position = strpos($image, '.png');
            if ($split_position !== false) {
              echo '<img style="width:150px;height:150px;" src="../uploaded_img/' . substr($image, 0, $split_position) . '.png" alt="">'; 
    }
}
?>




      <p> total price : <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
      <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
      <form action="" method="POST">
         <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
         <select name="payment_status" class="drop-down">
            <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
            <option value="pending">pending</option>
            <option value="completed">completed</option>
         </select>
         <div class="flex-btn">
            <input type="submit" value="update" class="btn" name="update_payment">
            <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
         </div>
      </form>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">no orders placed yet!</p>';
   }
   ?>

   </div>
   <!-- Hiển thị liên kết phân trang -->
   <div class="pagination">
      <?php
         // Giới hạn số lượng liên kết phân trang được hiển thị
         $num_links = 5;
         $start = max(1, $current_page - floor($num_links / 2));
         $end = min($total_pages, $start + $num_links - 1);

         if ($start > 1) {
            echo '<a href="?page=1">1</a>';
            echo '<span>...</span>';
         }

         for ($i = $start; $i <= $end; $i++) {
            echo '<a href="?page=' . $i . '" class="' . (($current_page == $i) ? 'active' : '') . '">' . $i . '</a>';
         }

         if ($end < $total_pages) {
            echo '<span>...</span>';
            echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
         }
      ?>
   </div>

</section>

<!-- placed orders section ends -->









<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>