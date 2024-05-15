<?php
include '../components/connect.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
   header('location:admin_login.php');
   exit(); // Thêm exit để dừng việc thực thi mã tiếp theo nếu chưa đăng nhập
}

$admin_id = $_SESSION['admin_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Page</title>

   <!-- Font Awesome CDN link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
   
<!-- Header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- Header section ends -->

<!-- Search form section starts  -->
<section class="search-form">
   <form method="post" action="">
      <input type="text" name="search_box" placeholder="Tên sản phẩm..." class="box">
      <input type="number" name="min_price" placeholder="Giá thấp nhất" class="box">
      <input type="number" name="max_price" placeholder="Giá cao nhất" class="box">
      <select name="category" class="box">
         <option value="">Tất cả danh mục</option>
         <option value="fast food">fast food</option>
         <option value="drinks">drinks</option>
         <option value="main dish">main dish</option>
         <option value="desserts">dessert</option>
         <!-- Thêm các option cho các danh mục sản phẩm -->
      </select>
      <button type="submit" name="search_btn" class="fas fa-search"></button>
   </form>
</section>
<!-- Search form section ends -->

<section class="products" style="min-height: 100vh; padding-top:0;">
   <div class="box-container">
      <?php
         if (isset($_POST['search_btn'])) {
            $search_box = $_POST['search_box'];
            $min_price = isset($_POST['min_price']) && is_numeric($_POST['min_price']) ? $_POST['min_price'] : 0;
            $max_price = isset($_POST['max_price']) && is_numeric($_POST['max_price']) ? $_POST['max_price'] : PHP_INT_MAX;
            $category = $_POST['category'];

            $where = "WHERE name LIKE '%$search_box%'";

            // Thêm điều kiện giá vào câu truy vấn nếu có giá trị hợp lệ
            if ($min_price >= 0 && $max_price > $min_price) {
               $where .= " AND price BETWEEN $min_price AND $max_price";
            }

            if (!empty($category)) {
               $where .= " AND category = '$category'";
            }

            $select_products = $conn->prepare("SELECT * FROM `products` $where");
            $select_products->execute();

            if ($select_products->rowCount() > 0) {
               while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
      ?>
                  <form action="" method="post" class="box">
                     <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                     <div class="flex">
                        <div class="price"><span>$</span><?= $fetch_products['price']; ?><span>/-</span></div>
                        <div class="category"><?= $fetch_products['category']; ?></div>
                     </div>
                     <div class="name"><?= $fetch_products['name']; ?></div>
                     <div class="details"><?= $fetch_products['details']; ?></div>
                     <div class="flex-btn">
                        <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Update</a>
                        <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
                     </div>
                  </form>
      <?php
               }
            } else {
               echo '<p class="empty">No products found!</p>';
            }
         }
      ?>
   </div>
</section>

<!-- Custom JS file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>
