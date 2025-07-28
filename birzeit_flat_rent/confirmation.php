<?php
// confirmation.php
$flat_ref_no = isset($_GET['flat_ref_no']) ? htmlspecialchars($_GET['flat_ref_no']) : 'N/A';

session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flat Submission Confirmation</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php';?>
    <section class="container" style="max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;">
        <h2 style="color: green;">✅ Flat Submitted Successfully!</h2>
        <p>Your flat reference number is:</p>
        <p style="font-weight: bold; font-size: 1.2em;"><?php echo $flat_ref_no; ?></p>
        <a href="index.php" class="btn">← Back to Home</a>
    </section>

    <?php require_once 'includes/footer.php';?>
</body>
</html>
