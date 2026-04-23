<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to($path)
{
    header('Location: ' . $path);
    exit;
}

function flash_set($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function flash_get()
{
    if (!isset($_SESSION['flash'])) {
        return '';
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return '<div class="alert alert-' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}

function render_template($templatePath, $variables = [])
{
    // Put PHP values inside a plain HTML file.
    $template = file_get_contents($templatePath);

    foreach ($variables as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }

    echo $template;
}

function clean($value)
{
    return trim((string) $value);
}

function validate_student($data)
{
    $errors = [];

    if ($data['first_name'] === '') {
        $errors[] = 'First name is required.';
    }

    if ($data['last_name'] === '') {
        $errors[] = 'Last name is required.';
    }

    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($data['student_number'] === '') {
        $errors[] = 'Student number is required.';
    }

    if ($data['registration_number'] === '') {
        $errors[] = 'Registration number is required.';
    }

    return $errors;
}

function default_photo()
{
    return 'data:image/svg+xml;utf8,' . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><rect width="160" height="160" rx="20" fill="#eef3f9"/><circle cx="80" cy="60" r="26" fill="#b8c7d9"/><path d="M34 136c8-24 27-37 46-37s38 13 46 37" fill="#b8c7d9"/></svg>'
    );
}

function photo_src($path)
{
    $path = clean($path);

    if ($path === '' || $path === 'uploads/default.png' || $path === '../Uploads/default.png') {
        return default_photo();
    }

    // Uploaded file: return a browser-safe relative path.
    if (strpos($path, '../Uploads/') === 0) {
        return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    }

    // Already a data URI or absolute URL.
    if (strpos($path, 'data:image') === 0 || strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }

    return default_photo();
}

function upload_photo($file, $currentPath = '')
{
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => true,
            'path' => $currentPath !== '' ? $currentPath : '../Uploads/default.png',
        ];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'Photo upload failed.',
        ];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return [
            'success' => false,
            'error' => 'Photo must be 2MB or less.',
        ];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        return [
            'success' => false,
            'error' => 'Please upload a JPG, PNG, GIF, or WEBP image.',
        ];
    }

    $uploadDir = __DIR__ . '/../Uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => false,
            'error' => 'Could not save the photo.',
        ];
    }

    return [
        'success' => true,
        'path' => '../Uploads/' . $fileName,
    ];
}
