<?php


// Function to establish a PDO connection.
// Update the database credentials as per your configuration.
function getPDO() {
    $host = 'localhost';
    $db   = 'SIMS';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}

/**
 * Records a user activity into the history table.
 *
 * @param PDO $pdo PDO connection object to the database
 * @param int $user_id The ID of the user performing the action
 * @param string $action Description of the activity
 */
function recordActivity($pdo, $user_id, $action) {
    try {
        $sql = "INSERT INTO history (user_id, action) VALUES (:user_id, :action)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id, ':action' => $action]);
        return true;
    } catch (PDOException $e) {
        error_log('Failed to record activity: ' . $e->getMessage());  // Log to PHP error log
        return false;
    }
}


/**
 * Retrieves the user's activity history from the history table.
 *
 * @param PDO $pdo PDO connection object to the database
 * @param int $user_id The ID of the user whose history is being retrieved
 * @return array An associative array of the user's history
 */
function getHistory($pdo, $user_id = null) {
    // If no user ID is provided, fetch history for all users
    $user_role = $_SESSION['role'] ?? 'guest'; // Get user role from session

    if ($user_role == 'superadmin') { // Admins can see all history
      $sql = "SELECT * FROM history ORDER BY timestamp DESC";
      $stmt = $pdo->query($sql); 
    } else { 
      // Other users see only their own history
      $sql = "SELECT * FROM history WHERE user_id = ? ORDER BY timestamp DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$user_id]);
    }
  
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  
  
?>
