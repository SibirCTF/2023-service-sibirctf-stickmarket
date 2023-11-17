<?php
require_once("auth.php");
require_once("db.php");
require("func.php");

$uuid = $_SESSION['uuid'];
$balance = getCurrentBalance($uuid);

function buyProduct($uuid, $id) {
    $db = ConnectDatabase();
    $stmt = $db->prepare("SELECT balance FROM users WHERE uuid=:uuid");
    $stmt->bindValue(':uuid', $uuid);
    $stmt->execute();
    $balanceData = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $balanceData['balance'];
    $stmt = $db->prepare("SELECT price FROM market WHERE id=:id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $priceData = $stmt->fetch(PDO::FETCH_ASSOC);
    $price = $priceData['price'];
    $result = $balance >= $price;
    if (!empty($result)) {
        $stmt = $db->prepare("UPDATE users SET balance=balance - :price WHERE uuid=:uuid");
        $stmt->bindParam(':price', $price, PDO::PARAM_INT);
        $stmt->bindParam(':uuid', $uuid, PDO::PARAM_STR);
        $stmt->execute();
        $stmt = $db->prepare("SELECT phraseOfTruth FROM market WHERE id=:id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultData = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = $resultData['phraseOfTruth'];
        return $result;
    } else {
        return "Error: Insufficient funds!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $productId = $_POST['id'];
        $result = buyProduct($uuid, $productId);
        $message = $result;
    } else {
        $message = "Something error, try again. Or kill all Keny and try again.";
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
                    <p class="lead fw-normal text-white-50 mb-0">There is you order?!</p>
                </div>
            </div>
        </header>
        <?php if (isset($message) && !empty($message)) {
                echo '<div class="alert alert-primary text-center" role="alert">';
                echo $message;
                echo '</div>';
                }
        ?> 
        <br><br>
    <footer class="py-5 bg-dark">
        <div class="container"><p class="m-0 text-center text-white">Copyright &copy; SibirCTF 2023</p></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
