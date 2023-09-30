<?php
$servername = "localhost"; // Имя хоста (обычно localhost для локального сервера)
$username = "admin"; // Имя пользователя MySQL
$password = "admin"; // Пароль MySQL
$dbname = "elements_counter"; // Имя вашей базы данных

// Создайте подключение к базе данных
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверьте соединение с базой данных
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>