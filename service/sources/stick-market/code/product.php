<?php
require_once('auth.php');
require_once("db.php");
require "func.php";

$uuid = $_SESSION['uuid'];
$balance = getCurrentBalance($uuid);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buyProduct'])){
    $message = $_POST['buyProduct'];
}

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    $stmt = $db->prepare("SELECT id, nameOfStick, description, price, author, image FROM market WHERE id = :id");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $id = $product['id'];
        $productName = $product['nameOfStick'];
        $productDescription = $product['description'];
        $author = $product['author'];
        $image = $product['image'];
        $price = $product['price'];
    } else {
        echo "Product not found.";
    }
} else {
    echo "Product ID not specified.";
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Stick Shop</title>
        <link rel="icon" type="image/x-icon" href="static/assets/favicon.ico" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link href="static/css/styles.css" rel="stylesheet" />
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="/index.php">Market</a></li>
                        <li class="nav-item dropdown">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="/addstick.php">Add new stick</a></li>
                        <li class="nav-item dropdown">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="/bonus.php">Bonus</a></li>
                        <li class="nav-item dropdown">
						<li class="nav-item"><a class="nav-link active" aria-current="page">Balance: <?php echo $balance ?></a></li>
                        </li>
                    </ul>
                    <form class="d-flex">
						<a href="/logout.php" class="btn btn-outline-dark" role="button">Exit</a>
                            <span class="badge bg-dark text-white ms-1 rounded-pill"></span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    <header class="bg-dark py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Stick Shop</h1>
                <p class="lead fw-normal text-white-50 mb-0">Product Details</p>
            </div>
        </div>
    </header>
    </head>
    <body>
    <div class="col mb-5">
        <div class="card h-100"> 
            <img class="center-image" src=' <?php echo $image; ?> ' alt="..." width="250" height="200">                           
            <div class="card-body p-4">
                <div class="text-center">
                    <form class="form-signin" action="/buy.php?id=" method="POST">
                        <h5 class="fw-bolder"> Product description:  </h5> <?php echo $productDescription; ?> 
                        <br><br>
                        <h5 class="fw-bolder"> Author: </h5> <?php echo $author; ?> 
                        <br><br>
                        <h5 class="fw-bolder"> Price: </h5> <?php echo $price; ?>$
                        <br><br>
                        <form class="form-signin" action="/buy.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $id ?>">
                            <input class="btn btn-primary btn-dark" type="submit" value="Buy"><br>
                        </form>   
                </div>
            </div>
        </div>
    </div>
</body>
</html>
