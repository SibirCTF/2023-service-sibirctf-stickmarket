<?php

require_once("auth.php");
require_once("db.php");
require "func.php";

$uuid = $_SESSION['uuid'];
$balance = getCurrentBalance($uuid);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $db = ConnectDatabase();
    $stmt = $db->prepare("SELECT bonus FROM users WHERE uuid=:uuid");
    $stmt->bindValue(':uuid', $uuid);
    $stmt->execute();
    $resultData = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = $resultData['bonus'];
    if ($result === 1) {
        $message = "You already get you bonus. Go away!";
    } else {
        $stmt = $db->prepare("UPDATE users SET balance=balance+100 WHERE uuid=:uuid");
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();
        $stmt = $db->prepare("UPDATE users SET bonus=1 WHERE uuid=:uuid");
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();
        $message = "Get your bonuses and go away <3" . $randomJoke;    
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>StickMarket</title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link href="static/css/styles.css" rel="stylesheet" />
    </head>
    <body>
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
                    <p class="lead fw-normal text-white-50 mb-0">Kill Keny and get you free 100$ bonus.</p>
                </div>
            </div>
        </header>
        <?php if (isset($message) && !empty($message)) {
                echo '<div class="alert alert-primary text-center" role="alert">';
                echo $message;
                echo '</div>';
                }
        ?>     
        <section class="py-1">
            <div class="container px-4 px-lg-5 mt-5">
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                    <div class="col mb-5">
                        <div class="card h-100">
                        <div class="text-center">
                            <form class="form-signin" method="POST">
                                <input class="btn mt-auto" value="Kill Keny!" type="submit">
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </section>
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; SibirCTF 2023</p></div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
