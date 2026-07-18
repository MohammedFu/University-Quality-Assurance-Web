<?php
// Simple script to test database connection and queries

// Include database connection
require_once __DIR__ . '/includes/db.php';

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to test database connection
function testDatabaseConnection($pdo) {
    try {
        $pdo->query('SELECT 1');
        echo "<p style='color:green'>✓ Database connection successful</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Function to test if learning_outcome table exists and has data
function testLearningOutcomeTable($pdo) {
    try {
        $stmt = $pdo->query('SHOW TABLES LIKE "learning_outcome"');
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✓ learning_outcome table exists</p>";
            
            // Check if table has data
            $stmt = $pdo->query('SELECT COUNT(*) FROM learning_outcome');
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                echo "<p style='color:green'>✓ learning_outcome table has $count records</p>";
                
                // Show sample data
                $stmt = $pdo->query('SELECT * FROM learning_outcome LIMIT 5');
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h3>Sample learning_outcome data:</h3>";
                echo "<pre>" . print_r($data, true) . "</pre>";
            } else {
                echo "<p style='color:red'>✗ learning_outcome table is empty</p>";
            }
        } else {
            echo "<p style='color:red'>✗ learning_outcome table does not exist</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Error checking learning_outcome table: " . $e->getMessage() . "</p>";
    }
}

// Function to test the getLearningOutcomes function
function testGetLearningOutcomes($pdo) {
    try {
        // Include the helper functions
        require_once __DIR__ . '/includes/helpers.php';
        
        // Call the function with no filters
        $outcomes = getLearningOutcomes($pdo);
        
        echo "<h3>getLearningOutcomes test:</h3>";
        echo "<p>Retrieved " . count($outcomes) . " learning outcomes</p>";
        
        if (count($outcomes) > 0) {
            echo "<pre>" . print_r(array_slice($outcomes, 0, 3), true) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error testing getLearningOutcomes: " . $e->getMessage() . "</p>";
    }
}

// Run the tests
echo "<h1>Database Connection Test</h1>";

if (testDatabaseConnection($pdo)) {
    testLearningOutcomeTable($pdo);
    testGetLearningOutcomes($pdo);
}
