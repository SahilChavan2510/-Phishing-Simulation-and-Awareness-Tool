<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer's autoloader for Mailgun
require 'vendor/autoload.php';

use Mailgun\Mailgun;

// Mailgun API configuration
$apiKey = '59f3a13a277c4559d79027d1221778fd-2e68d0fb-4e90473c'; // Replace with your Mailgun API key
$domain = 'sandbox5661e8d8e13d4ed1b54c730448a40786.mailgun.org'; // Replace with your Mailgun domain

// Database credentials
$servername = "localhost"; // Your database server name
$username = "root"; // Default XAMPP username
$password = "root"; // Default XAMPP password
$dbname = "my_database"; // Your database name

// Function to send educational email
function sendEducationalEmail($email, $mgClient, $domain) {
    $subject = "Phishing Awareness: Protect Yourself!";
    $body = "
        <h1>Be Aware of Phishing Scams</h1>
        <p>Dear User,</p>
        <p>You recently interacted with a simulated phishing page created for educational purposes.</p>
        <p>Here are some tips to protect yourself:</p>
        <ul>
            <li>Verify the website URL before entering credentials.</li>
            <li>Be cautious of urgent or alarming messages.</li>
            <li>Don't share sensitive information via email.</li>
        </ul>
        <p>Stay safe online!</p>
        <p>Best regards,<br>Your Security Team</p>
    ";

    try {
        $mgClient->messages()->send($domain, [
            'from'    => 'sandbox5661e8d8e13d4ed1b54c730448a40786.mailgun.org', // Replace with a valid sender email
            'to'      => $email, // Use the email collected from the form
            'subject' => $subject,
            'html'    => $body
        ]);
        echo "Educational email sent successfully to $email.<br>";
    } catch (Exception $e) {
        echo "Failed to send email: " . $e->getMessage() . "<br>";
    }
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email and password from the form
    $email = $_POST['email'];
    $plain_password = $_POST['password']; // User's password (plain)

    // Hash the password before storing it
    // $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // Prepare the SQL query with placeholders
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters (s = string, s = string for email and hashed password)
    $stmt->bind_param("ss", $email, $plain_password);

    // Execute the query
    if ($stmt->execute()) {
        echo "New user registered successfully.";
        
        // Send educational email
        $mgClient = Mailgun::create($apiKey);
        sendEducationalEmail($email, $mgClient, $domain);

        // Redirect to a new page (e.g., hacked.html or dashboard.html)
        header("Location: hacked.html");
        exit;  // Make sure to exit after redirect
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the prepared statement and connection
    $stmt->close();
}

$conn->close();
?>
