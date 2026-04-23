<?php

include_once __DIR__ . '/../classes/database.php';
include_once __DIR__ . '/../classes/Student.php';
include_once __DIR__ . '/helpers.php';

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

$errors = [];
$values = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'student_number' => '',
    'registration_number' => '',
    'phone_number' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean form data.
    foreach ($values as $key => $value) {
        $values[$key] = clean($_POST[$key] ?? '');
    }

    // Check required fields first.
    $errors = validate_student($values);

    if (!$errors && $student->duplicateExists($values['email'], $values['student_number'], $values['registration_number'])) {
        $errors[] = 'A student with the same email, student number, or registration number already exists.';
    }

    if (!$errors) {
        // Upload the photo.
        $photo = upload_photo($_FILES['photo'] ?? null);
        if (!$photo['success']) {
            $errors[] = $photo['error'];
        }
    }

    if (!$errors) {
        // Send the values to the Student class.
        $student->first_name = $values['first_name'];
        $student->last_name = $values['last_name'];
        $student->email = $values['email'];
        $student->student_number = $values['student_number'];
        $student->registration_number = $values['registration_number'];
        $student->phone_number = $values['phone_number'];
        $student->photo_path = $photo['path'];

        if ($student->create()) {
            flash_set('success', 'Student record created successfully.');
            redirect_to('index.php');
        } else {
            $errors[] = 'Could not save the student record.';
        }
    }
}

$errorHtml = '';
if ($errors) {
    $errorHtml = '<div class="alert alert-error"><ul>';
    foreach ($errors as $error) {
        $errorHtml .= '<li>' . h($error) . '</li>';
    }
    $errorHtml .= '</ul></div>';
}

render_template(__DIR__ . '/../student_crud_html/create.html', [
    'page_title' => 'Add Student',
    'error_html' => $errorHtml,
    'first_name' => h($values['first_name']),
    'last_name' => h($values['last_name']),
    'email' => h($values['email']),
    'student_number' => h($values['student_number']),
    'registration_number' => h($values['registration_number']),
    'phone_number' => h($values['phone_number']),
]);
