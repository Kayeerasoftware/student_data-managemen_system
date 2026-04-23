<?php

include_once __DIR__ . '/../classes/database.php';
include_once __DIR__ . '/../classes/Student.php';
include_once __DIR__ . '/helpers.php';

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

$studentId = (int) ($_POST['id'] ?? 0);

if ($studentId <= 0) {
    flash_set('error', 'Please choose a valid student record.');
    redirect_to('index.php');
}

// Delete the chosen student.
$student->id = $studentId;

if ($student->delete()) {
    flash_set('success', 'Student record deleted successfully.');
} else {
    flash_set('error', 'Could not delete the student record.');
}

redirect_to('index.php');
