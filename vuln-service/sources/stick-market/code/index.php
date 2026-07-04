<?php

require_once('auth.php');
require_once("db.php");
require "func.php";

$uuid = $_SESSION['uuid'];
$balance = getCurrentBalance($uuid);

$itemsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

$stmt = $db->prepare("SELECT * FROM market LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
    
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <p class="lead fw-normal text-white-50 mb-0">Get your truth from the stick of truth</p>
            </div>
        </div>
    </header>
    </head>
    <body>
        
    <section class="py-1">
        <div class="container px-4 px-lg-5 mt-5">
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php
                    foreach ($items as $item) {
                        $render = '
                        <div class="col mb-5">
                            <div class="card h-100"> 
                                <img class="card-img-top" src=' . $item['image'] . ' alt="..." width="250" height="200">                           
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <form class="form-signin" action="/product.php?id=' . htmlspecialchars($item['id']) . '" method="POST">
                                            <h5 class="fw-bolder">' . htmlspecialchars($item['nameOfStick']) . '</h5>
                                            $' . htmlspecialchars($item['price']) . '
                                            <br><br>
                                            <input class="btn btn-primary btn-dark" type="submit" value="More info"><br>
                                        </form>   
                                    </div>
                                </div>
                            </div>
                        </div>';
                        echo $render;
                    }
                ?>
            </div>
        </div>
    </section>
    </div>
    <div class="pagination-container">
        <div class="pagination">
            
            <?php
            $totalItemsQuery = $db->query("SELECT COUNT(*) as count FROM market");
            $totalItemsResult = $totalItemsQuery->fetch(PDO::FETCH_ASSOC);
            $totalItems = $totalItemsResult['count'];
            $totalPages = ceil($totalItems / $itemsPerPage);
            if (!empty($totalItems)) {
                echo '<p>Page:&nbsp;</p>';
            }
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $page) {
                    echo "<span> $i </span> "; 
                } else {
                    echo "&nbsp<a href='?page=$i'>$i</a>&nbsp"; 
                }
            }
            ?>
        </div>
    </div>
    
</body>
</html>
        
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; SibirCTF 2023</p></div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
