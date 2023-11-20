<?php

$db = ConnectDatabase();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY, username TEXT, password TEXT, balance INT DEFAULT 0, bonus INTEGER DEFAULT 0, uuid TEXT)");
$stmt->execute();
$stmt = $db->prepare("CREATE TABLE IF NOT EXISTS market(id INTEGER PRIMARY KEY, nameOfStick TEXT, price INT, description TEXT, author TEXT, phraseOfTruth TEXT, image TEXT)");
$stmt->execute();

function ConnectDatabase () {
    return new PDO('sqlite:market.db', SQLITE3_OPEN_READWRITE);
}

function UserExists ($login) {
    $db = ConnectDatabase();
    $stmt = $db->prepare("SELECT username FROM users WHERE username=:login");
    $stmt->bindValue(':login', $login);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) { 
        return true;
    } else {
        return false;
    }
}

?>