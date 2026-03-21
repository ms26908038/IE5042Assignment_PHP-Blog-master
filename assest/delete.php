<?php require "db.php"; ?>
<?php

/*
// Get id & type from header
$id = $_GET['id'];
$type = $_GET['type'];

if ($conn) {
    switch ($type) {
        case "article":
            delete($conn, $type, $id, "article.php");
            break;
        case "category":
            delete($conn, $type, $id, "categories.php");
            break;
        case "author":
            delete($conn, $type, $id, "author.php");
            break;
        default:
            break;
    }
} else {
    echo 'Error: ' . $e->getMessage();
}


function delete($conn, $table, $id, $goto)
{

    $col = $table . "_id";

    try {
        // sql to delete a record
        $sql = "DELETE FROM $table WHERE $col = $id";

        // use exec() because no results are returned
        $conn->exec($sql);
        echo "$table Deleted Successfully";
    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }

    $conn = null;

    header("Location: ../$goto", true, 301);
    exit;
}
?>
*/

// 1. Initial Source of Taint
$type = $_GET['type'] ?? '';
$raw_id = $_GET['id'] ?? '';

// 2. [W4 FIX] - Explicit Input Filtering
// This ensures $id is a valid integer. If it's a script or string, it returns false.
$id = filter_var($raw_id, FILTER_VALIDATE_INT);

// 3. Security Gate
$allowed_types = ["article", "category", "author"];

if (!$id || !in_array($type, $allowed_types)) {
    // If an attacker sends ?id=<script> or ?id=1OR1, the filter returns false.
    error_log("Security Warning: Invalid ID or Type attempted in delete.php. Input: " . $raw_id);
    
    // Fail-Safe: Stop execution immediately
    die("Access Denied: Invalid Request Format.");
}

// 4. Proceed only if the Gate is passed
if ($conn) {
    switch ($type) {
        case "article":
            delete($conn, "article", $id, "article.php");
            break;
        case "category":
            delete($conn, "category", $id, "categories.php");
            break;
        case "author":
            delete($conn, "author", $id, "author.php");
            break;
    }
}

function delete($conn, $table, $id, $goto)
{
    $col = $table . "_id";

    try {
        // [W1 FIX] - Parameterized Query
        $sql = "DELETE FROM $table WHERE $col = :id";
        $stmt = $conn->prepare($sql);
        
        // Bind parameter as Integer type
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
    } catch (PDOException $e) {
        // [W3 & W4 FIX] - Sink Removal and Secure Logging
        error_log("Database Error: " . $e->getMessage());
        // Generic message only - prevents Reflected XSS
        die("An internal error occurred.");
    }

    $conn = null;
    header("Location: ../$goto", true, 301);
    exit;
}
?>