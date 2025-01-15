<?php
session_start();

if (isset($_GET['session_date']) && isset($_GET['tutor_name']) && isset($_GET['subject'])) {
    $_SESSION['selected_session'] = [
        'session_date' => $_GET['session_date'],
        'tutor_name' => $_GET['tutor_name'],
        'subject' => $_GET['subject']
    ];
}

header('Location: tutor_details.php?tutor_name=' . urlencode($_GET['tutor_name']) . '&subject=' . urlencode($_GET['subject']));
exit();
?>
