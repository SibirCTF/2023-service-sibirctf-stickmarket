<?php

require '../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

if (!is_dir(__DIR__ . '/images/')) {
    mkdir(__DIR__ . '/images/', 0755, true);
}

if (!is_dir(__DIR__ . '/uploads/')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

if (isset($_GET['check']) && is_callable($_GET['check'])) {
    $params = $_GET;
    unset($params['check']);
    call_user_func_array($_GET['check'], $params);
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    $charCount = strlen($characters);
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, $charCount - 1)];
    }
    return $randomString;
}

function unpackZIP($uploadedFile, $uploadedDir) {

    $zip = new ZipArchive();

    if ($zip->open($uploadedFile) === true) {
        
        $numFiles = $zip->numFiles;
        $hasStickYml = false;
        $hasJpg = false;
        
        for ($i = 0; $i < $numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strtolower($filename) === 'stick.yml') {
                $hasStickYml = true;
            }
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'jpg') {
                $hasJpg = true;
            }
        }

        if ($numFiles > 0 && $numFiles < 3) {
            
            if ($hasStickYml && ($numFiles == 1 || ($hasJpg && $numFiles == 2))) {
                $zip->extractTo($uploadedDir);
                $zip->close();
                if ($hasJpg === true) {
                    $imageNewName = generateRandomString() . $filename;
                    rename($uploadedDir . '/' . $filename, 'images/' . $imageNewName);
                } else {
                    $imageNewName = null;
                }
                return [true, "Ok", $imageNewName];
            } else {
                return [false, "Archive can contain only two files: stick.yml and *.jpg", null];
            }
        } else {
        return [false, "Archive is empty or contains more than two files", null];
        }

    } else {
        $response = [false, 'Failed to unpack archive!', null]; 
    }
    return $response;
}

function storeStickToDB($data) {
    try {
        $db = ConnectDatabase();
        $stmt = $db->prepare("INSERT INTO market (nameOfStick, price, description, phraseOfTruth, author, image) 
        VALUES (:nameOfStick, :price, :description, :phraseOfTruth, :author, :image)");
        
        $stmt->bindValue(':nameOfStick', $data['name']);
        $stmt->bindValue(':price', $data['price']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':phraseOfTruth', $data['phraseOfTruth']);
        $stmt->bindValue(':author', $data['author']);
        $stmt->bindValue(':image', $data['image']);

        $stmt->execute();
        return [true, "Add new stick to market"];

    } catch (PDOException $e) {

        return [false, $e->getMessage()];

    }
}

function getCurrentBalance($uuid) {
    $db = ConnectDatabase();
	$stmt = $db->prepare("SELECT balance FROM users WHERE uuid=:uuid");
	$stmt->bindValue(':uuid', $uuid);
	$stmt->execute();
	$balanceData = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $balanceData['balance'];
    if (empty($balance)) {
        $balance = 0;
    }
	return $balance;
}

function UpdateBonus($username) {
    try {
        $db = ConnectDatabase();
        $stmt = $db->prepare("UPDATE users SET bonus=0 WHERE username=:username");
        $stmt->bindValue(':uuid', $username);
        $stmt->execute();
        return true; 
    } catch (Exception) {
        return false;
    } 
}

function yamlValidate($yamlFilePath, $imagePath) {
    if (file_exists($yamlFilePath)) {
        $data = Yaml::parseFile($yamlFilePath);

        $name = $data['stick']['nameOfStick'] ?? "Typical stick";
        $price = $data['stick']['price'] ?? 1000;

        if ($price <= 0 | $price > 1000 ) {
            return [false, "Price must be greater than 0 and less than or equal to 1000"];
        }

        $description = $data['stick']['description'] ?? "Best. Of. The. Best. Stick. Of. Truth.";

        $phraseOfTruth = $data['stick']['phraseOfTruth'] ?? "Truth is lie.";
        $author = $data['stick']['author'] ?? "Kartman";
        $image = $data['stick']['image'] ?? null;

        if (!empty($image) && !empty($imagePath)) {

            $imageInfo = @pathinfo($imagePath);

            if ($imageInfo['extension'] != "jpg") {
                return [false, "Error load image file: " . $imagePath];
            }
        }

        $result = ['name'              => $name, 
                   'price'             => $price,
                   'description'       => $description, 
                   'phraseOfTruth'     => $phraseOfTruth,
                   'author'            => $author, 
                   'image'             => $imagePath
                ];

        return [true, $result];
    } else {
        return [false, "Error reading yaml file!"];
    }
}

function deleteDirectory($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . '/' . $object)) {
                    deleteDirectory($dir . '/' . $object);
                } else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        rmdir($dir);
    }
}

?>