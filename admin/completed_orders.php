<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit(); // Kết thúc chương trình sau khi chuyển hướng
}

$select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'completed'");
$select_orders->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Completed Orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php' ?>

<!-- Completed orders section starts  -->

<section class="placed-orders">

   <h1 class="heading">Completed Orders</h1>

   <div class="box-container">

   <?php
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
      <p> status: completed <p>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">No completed orders!</p>';
      }
   ?>

   

</section>

<!-- Completed orders section ends -->

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>
