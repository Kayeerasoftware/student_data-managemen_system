<?php

include_once __DIR__ . '/../classes/database.php';
include_once __DIR__ . '/../classes/Student.php';
include_once __DIR__ . '/helpers.php';

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

$errors = [];
$studentId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);

if ($studentId <= 0) {
    flash_set('error', 'Please choose a valid student record.');
    redirect_to('index.php');
}

$student->id = $studentId;
if (!$student->readOne()) {
    flash_set('error', 'Student record not found.');
    redirect_to('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean the updated values.
    $values = [
        'first_name' => clean($_POST['first_name'] ?? ''),
        'last_name' => clean($_POST['last_name'] ?? ''),
        'email' => clean($_POST['email'] ?? ''),
        'student_number' => clean($_POST['student_number'] ?? ''),
        'registration_number' => clean($_POST['registration_number'] ?? ''),
        'phone_number' => clean($_POST['phone_number'] ?? ''),
    ];

    $errors = validate_student($values);

    if (!$errors && $student->duplicateExists($values['email'], $values['student_number'], $values['registration_number'], $studentId)) {
        $errors[] = 'Another student already uses that email, student number, or registration number.';
    }

    if (!$errors) {
        // Keep old photo if no new one is uploaded.
        $photo = upload_photo($_FILES['photo'] ?? null, $student->photo_path);
        if (!$photo['success']) {
            $errors[] = $photo['error'];
        }
    }

    if (!$errors) {
        // Save the changes.
        $student->first_name = $values['first_name'];
        $student->last_name = $values['last_name'];
        $student->email = $values['email'];
        $student->student_number = $values['student_number'];
        $student->registration_number = $values['registration_number'];
        $student->phone_number = $values['phone_number'];
        $student->photo_path = $photo['path'];

        if ($student->update()) {
            flash_set('success', 'Student record updated successfully.');
            redirect_to('index.php');
        } else {
            $errors[] = 'Could not update the student record.';
        }
    }

    $currentValues = $values;
} else {
    $currentValues = [
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'email' => $student->email,
        'student_number' => $student->student_number,
        'registration_number' => $student->registration_number,
        'phone_number' => $student->phone_number,
    ];
}

$errorHtml = '';
if ($errors) {
    $errorHtml = '<div class="alert alert-error"><ul>';
    foreach ($errors as $error) {
        $errorHtml .= '<li>' . h($error) . '</li>';
    }
    $errorHtml .= '</ul></div>';
}

render_template(__DIR__ . '/../student_crud_html/edit.html', [
    'page_title' => 'Edit Student',
    'error_html' => $errorHtml,
    'student_id' => (string) $studentId,
    'first_name' => h($currentValues['first_name']),
    'last_name' => h($currentValues['last_name']),
    'email' => h($currentValues['email']),
    'student_number' => h($currentValues['student_number']),
    'registration_number' => h($currentValues['registration_number']),
    'phone_number' => h($currentValues['phone_number']),
    'photo_src' => photo_src($student->photo_path),
]);
