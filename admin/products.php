<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exists!';
   }else{
      if($image_size > 2000000){
         $message[] = 'image size is too large';
      }else{
         move_uploaded_file($image_tmp_name, $image_folder);

         $insert_product = $conn->prepare("INSERT INTO `products`(name, details, category, price, image) VALUES(?,?,?,?,?)");
         $insert_product->execute([$name, $details, $category, $price, $image]);

         $message[] = 'new product added!';
      }

   }

}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
   $delete_id = $_GET['delete'];

   $delete_product_name = $conn->prepare("SELECT name FROM `products` WHERE id = ?");
   $delete_product_name->execute([$delete_id]);
   $product_name_row = $delete_product_name->fetch(PDO::FETCH_ASSOC);
   $delete_product_name = $product_name_row['name'];

   // Kiểm tra xem sản phẩm có nằm trong đơn hàng chưa hoàn thành không
   $check_order = $conn->prepare("SELECT * FROM orders WHERE total_products LIKE ? AND payment_status = 'pending'");
   $check_order->execute(["%$delete_product_name%"]);
   $order = $check_order->fetch(PDO::FETCH_ASSOC);

   if ($order) {
       // Nếu có đơn hàng chưa hoàn thành chứa sản phẩm này, không cho phép xóa
       $message[] = "Không thể xóa sản phẩm vì có đơn hàng chưa hoàn thành chứa sản phẩm này.";
      } 
   else {
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:products.php');
  }
}
// Phân trang
$products_per_page = 6;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

$total_products_query = $conn->query("SELECT COUNT(*) AS total FROM `products`");
$total_products = $total_products_query->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $products_per_page);


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
        .preview {
            margin-top: 10px;
            width: 200px; /* kích thước xem trước */
            height: 200px;
            object-fit: cover; /* giữ tỉ lệ và cắt nếu cần */
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php' ?>

<!-- add products section starts  -->

<section class="add-products">

   <form action="" method="POST" enctype="multipart/form-data">
      <h3>add product</h3>
      <input type="text" required placeholder="enter product name" name="name" maxlength="100" class="box">
      <input type="number" min="0" max="9999999999" required placeholder="enter product price" name="price" onkeypress="if(this.value.length == 10) return false;" class="box">
      <select name="category" class="box" required>
         <option value="" disabled selected>select category --</option>
         <option value="main dish">main dish</option>
         <option value="fast food">fast food</option>
         <option value="drinks">drinks</option>
         <option value="desserts">desserts</option>
      </select>  
      <textarea name="details" placeholder="enter product details" class="box" required maxlength="500" cols="30" rows="10"></textarea> 
      <input type="file" name="image" id="fileInput" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
      <img id="imagePreview" class="preview" alt="Image Preview" style="display:none;">

    <script>
        document.getElementById('fileInput').addEventListener('change', function(event) {
            const file = event.target.files[0]; // Lấy tệp đã chọn
            const preview = document.getElementById('imagePreview'); // Tìm thành phần img để hiển thị hình ảnh

            if (file) { // Kiểm tra xem có tệp không
                const reader = new FileReader(); // Tạo đối tượng FileReader để đọc tệp
                
                // Sự kiện được gọi khi FileReader hoàn thành việc đọc tệp
                reader.onload = function(e) {
                    preview.src = e.target.result; // Đặt src của img bằng kết quả đọc
                    preview.style.display = 'block'; // Hiển thị ảnh
                };
                
                reader.readAsDataURL(file); // Đọc tệp dưới dạng DataURL (Base64)
            } else {
                preview.style.display = 'none'; // Nếu không có tệp, ẩn ảnh
            }
        });
    </script>
      <input type="submit" value="add product" name="add_product" class="btn">
   </form>

</section>

<!-- add products section ends -->

<!-- show products section starts  -->

<section class="show-products" style="padding-top: 0;">

   <div class="box-container">

   <?php
      $show_products = $conn->prepare("SELECT * FROM `products` LIMIT :limit OFFSET :offset");
      $show_products->bindParam(':limit', $products_per_page, PDO::PARAM_INT);
      $show_products->bindParam(':offset', $offset, PDO::PARAM_INT);
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="flex">
         <div class="price"><span>$</span><?= $fetch_products['price']; ?><span>/-</span></div>
         <div class="category"><?= $fetch_products['category']; ?></div>
      </div>
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="details"><?= $fetch_products['details']; ?></div>
      <div class="flex-btn">
         <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php  
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>

   </div>

</section>
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
<!-- show products section ends -->










<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>