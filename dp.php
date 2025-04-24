<?php
$host = 'localhost';
$dbname = 'ngo_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>










<?php
$conn = new mysqli('localhost', 'root', '', 'inventory_system');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function sellItem($item_id, $quantity_sold) {
    global $conn;
    $result = $conn->query("SELECT * FROM inventory WHERE item_id = $item_id");
    $item = $result->fetch_assoc();

    if ($item && $item['quantity_in_stock'] >= $quantity_sold) {
        $conn->query("UPDATE inventory SET quantity_in_stock = quantity_in_stock - $quantity_sold WHERE item_id = $item_id");
        $conn->query("INSERT INTO sales (item_id, quantity_sold) VALUES ($item_id, $quantity_sold)");
        echo "Sale successful! Sold $quantity_sold of {$item['item_name']}.";
    } else {
        echo "Not enough stock or item not found.";
    }
}

sellItem(1, 3);
$conn->close();
?>

