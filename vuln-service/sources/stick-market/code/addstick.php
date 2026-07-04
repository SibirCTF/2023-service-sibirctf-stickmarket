<?php
require_once("auth.php");
require_once("db.php");
require_once("func.php");

$uuid = $_SESSION['uuid'];
$balance = getCurrentBalance($uuid);

function tmpDir() {
    $uploads = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
    $tempDir = $uploads . generateRandomString();
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    return $tempDir;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadedDir = tmpDir();
    $uploadedFile = $uploadedDir . '/' . basename($_FILES["file"]["name"]);
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadedFile)) {
        $response = 'File ' . basename($_FILES['file']['name']) . ' uploaded successfully.';
        
        $result = unpackZIP($uploadedFile, $uploadedDir);
        if ($result[0] === true) {

            if (!empty($result[2])) {
                $imagePath = 'images/' . $result[2];
            } else {
                $imagePath = 'images/stick.jpg';
            }

            $result = yamlValidate($uploadedDir . '/stick.yml', $imagePath);
            if ($result[0] === true) {
                $data = $result[1];
                $data['image'] = $imagePath;
                $result = storeStickToDB($data);
                if (!$result[0] === true) {
                    $response = "Error while adding to db: " . $result[1];
                } else {
                    $response = $result[1];
                }
            } else {
                $response = $result[1];
            }
        } else {
            $response = $result[1];
        }

    deleteDirectory($uploadedDir);
    
    if (!isset($response)) {
        $response = "Succsessful added new stick!";
    }

    } else {
        $response = 'Failed to move the uploaded file';
        exit();
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
                <p class="lead fw-normal text-white-50 mb-0">Add new stick of truth</p>
            </div>
        </div>
    </header>
    </head>
    <body>
    <?php if (isset($response)) {
                echo '<div class="alert alert-primary text-center" role="alert">';
                echo $response;
                echo '</div>';
                }
        ?>   
<body>
    <br>
    <div class="py-1 text-center">
        <form method="post" enctype="multipart/form-data">
            <input type="file" id="file" name="file" accept=".zip">
            <input type="submit" value="Upload ZIP"> 
        </form>
    </div>
    <br><br>
<footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; SibirCTF 2023</p></div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
