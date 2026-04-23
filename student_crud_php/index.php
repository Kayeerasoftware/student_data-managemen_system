<?php

include_once __DIR__ . '/../classes/database.php';
include_once __DIR__ . '/../classes/Student.php';
include_once __DIR__ . '/helpers.php';

$database = new Database();
$db       = $database->getConnection();
$student  = new Student($db);
$students = $student->readAll()->fetchAll(PDO::FETCH_ASSOC);

$rowsHtml = '';
if (!$students) {
    $rowsHtml = '<tr><td colspan="7" class="empty-state">';
    $rowsHtml .= '<div class="empty-icon">&#128100;</div>';
    $rowsHtml .= '<p>No students found.</p>';
    $rowsHtml .= '<a class="button button-primary" href="create.php">+ Add the first student</a>';
    $rowsHtml .= '</td></tr>';
} else {
    foreach ($students as $row) {
        $initials = h(mb_strtoupper(mb_substr($row['first_name'], 0, 1) . mb_substr($row['last_name'], 0, 1)));
        $fullName = h($row['first_name'] . ' ' . $row['last_name']);
        $rowsHtml .= '<tr>';
        // Student column: avatar + name + email preview
        $rowsHtml .= '<td><div class="student-cell">';
        $rowsHtml .= '<div class="avatar-wrap"><img class="avatar" src="' . photo_src($row['photo_path']) . '" alt="' . $fullName . '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">';
        $rowsHtml .= '<span class="avatar-initials" style="display:none">' . $initials . '</span></div>';
        $rowsHtml .= '<div class="student-info"><span class="student-name">' . $fullName . '</span></div>';
        $rowsHtml .= '</div></td>';
        // Student number pill
        $rowsHtml .= '<td><span class="badge badge-blue">' . h($row['student_number']) . '</span></td>';
        // Reg number pill
        $rowsHtml .= '<td><span class="badge badge-gray">' . h($row['registration_number']) . '</span></td>';
        // Email
        $rowsHtml .= '<td><a class="table-link" href="mailto:' . h($row['email']) . '">' . h($row['email']) . '</a></td>';
        // Phone
        $rowsHtml .= '<td>' . (h($row['phone_number']) ?: '<span class="muted-dash">&mdash;</span>') . '</td>';
        // Created date — date only
        $rowsHtml .= '<td class="date-cell">' . h(date('d M Y', strtotime($row['created_at']))) . '</td>';
        // Actions
        $rowsHtml .= '<td><div class="actions">';
        $rowsHtml .= '<a class="btn-icon btn-view" href="view.php?id=' . (int) $row['id'] . '" title="View student">';
        $rowsHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> View</a>';
        $rowsHtml .= '<a class="btn-icon btn-edit" href="edit.php?id=' . (int) $row['id'] . '" title="Edit student">';
        $rowsHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit</a>';
        $rowsHtml .= '<form action="delete.php" method="post" onsubmit="return confirm(\'Delete ' . $fullName . '? This cannot be undone.\');">';
        $rowsHtml .= '<input type="hidden" name="id" value="' . (int) $row['id'] . '">';
        $rowsHtml .= '<button class="btn-icon btn-delete" type="submit" title="Delete student">';
        $rowsHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg> Delete</button>';
        $rowsHtml .= '</form></div></td>';
        $rowsHtml .= '</tr>';
    }
}

render_template(__DIR__ . '/../student_crud_html/index.html', [
    'page_title'    => 'Student Management System',
    'flash_html'    => flash_get(),
    'student_count' => (string) count($students),
    'rows_html'     => $rowsHtml,
]);
