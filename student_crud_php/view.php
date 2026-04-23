<?php

include_once __DIR__ . '/../Database.php';
include_once __DIR__ . '/../Student.php';
include_once __DIR__ . '/helpers.php';

$database  = new Database();
$db        = $database->getConnection();
$student   = new Student($db);
$studentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($studentId <= 0) {
    flash_set('error', 'Invalid student.');
    redirect_to('index.php');
}

$student->id = $studentId;
if (!$student->readOne()) {
    flash_set('error', 'Student not found.');
    redirect_to('index.php');
}

$fullName = h($student->first_name . ' ' . $student->last_name);
$initials = h(mb_strtoupper(mb_substr($student->first_name, 0, 1) . mb_substr($student->last_name, 0, 1)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $fullName ?> — Profile</title>
    <link rel="stylesheet" href="../Styles/style.css">
    <style>
        .profile-shell {
            width: min(580px, calc(100% - 32px));
            margin: 16px auto 24px;
        }

        .profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(19,34,56,0.10);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        /* ── Banner ── */
        .profile-banner {
            height: 52px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-banner-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        /* ── Profile header row ── */
        .profile-header-row {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
        }

        .profile-avatar-ring {
            flex-shrink: 0;
            width: 90px; height: 90px;
            border-radius: 14px;
            border: 3px solid #d1fae5;
            box-shadow: 0 4px 16px rgba(19,34,56,0.13);
            background: #eef3f9;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .profile-avatar-ring img { width:100%; height:100%; object-fit:cover; display:block; }
        .profile-avatar-initials {
            font-size: 2rem; font-weight: 800; color: #fff;
            background: linear-gradient(135deg, #11998e, #16a34a);
            width:100%; height:100%;
            display:flex; align-items:center; justify-content:center;
        }

        .profile-header-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
            text-align: left;
        }

        .profile-name {
            font-size: 1.25rem; font-weight: 800;
            color: var(--text); margin: 0;
            letter-spacing: -0.01em;
        }

        .profile-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .profile-email {
            font-size: 0.82rem; color: var(--muted); text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .profile-email:hover { color: #16a34a; text-decoration: underline; }

        .profile-status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.72rem; font-weight: 700;
            color: #16a34a; background: #dcfce7;
            border: 1px solid #86efac;
            border-radius: 20px; padding: 3px 10px;
        }
        .profile-status-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #16a34a;
            display: inline-block;
        }

        /* ── Section label ── */
        .profile-section-label {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #16a34a;
            margin: 14px 0 6px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .profile-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #d1fae5;
        }

        /* ── Detail rows ── */
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .profile-table tr {
            border-bottom: 1px solid #f0f7f4;
            transition: background 0.12s;
        }
        .profile-table tr:last-child { border-bottom: none; }
        .profile-table tr:hover { background: #f0fdf4; }
        .profile-table th {
            width: 36%;
            padding: 7px 12px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #5b6b7f;
            white-space: nowrap;
            border-right: 2px solid #f0f7f4;
        }
        .profile-table td {
            padding: 7px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text);
            word-break: break-word;
        }
        .profile-table .val-badge {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #16a34a;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 2px 8px;
            border-radius: 5px;
        }
        .profile-table .val-date {
            font-size: 0.78rem;
            color: var(--muted);
        }

        /* ── Actions ── */
        .profile-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .profile-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 22px; border-radius: 10px;
            font-size: 0.85rem; font-weight: 600; font-family: inherit;
            cursor: pointer; text-decoration: none;
            border: 1px solid transparent;
            transition: background 0.15s, transform 0.12s, box-shadow 0.15s;
        }
        .profile-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .profile-btn-edit   { background: var(--primary); color: #fff; }
        .profile-btn-edit:hover { background: var(--primary-dark); }
        .profile-btn-back   { background: #f0f4f8; color: var(--text); border-color: var(--border); }
        .profile-btn-back:hover { background: #e4eaf2; }
        .profile-btn-delete { background: #fdf0f0; color: var(--danger); border-color: #f5d0d0; }
        .profile-btn-delete:hover { background: #fde0e0; border-color: var(--danger); }

        @media (max-width: 520px) {
            .profile-actions { flex-direction: column; }
            .profile-btn { justify-content: center; }
        }
    </style>
</head>
<body>
<div class="profile-shell">
    <div class="profile-card">

        <!-- Banner -->
        <div class="profile-banner">
            <span class="profile-banner-title">Student Information</span>
        </div>

        <!-- Profile header: avatar left, info right -->
        <div class="profile-header-row">
            <div class="profile-avatar-ring">
                <img src="<?= photo_src($student->photo_path) ?>" alt="<?= $fullName ?>"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="profile-avatar-initials" style="display:none"><?= $initials ?></div>
            </div>
            <div class="profile-header-info">
                <h1 class="profile-name"><?= $fullName ?></h1>
                <a class="profile-email" href="mailto:<?= h($student->email) ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?= h($student->email) ?>
                </a>
                <span class="profile-status-badge">
                    <span class="profile-status-dot"></span> Active
                </span>
            </div>
        </div>

        <!-- Body -->
        <div class="profile-body">

            <!-- Personal Info -->
            <div class="profile-section-label">Personal Information</div>
            <table class="profile-table">
                <tr>
                    <th>First Name</th>
                    <td><?= h($student->first_name) ?></td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td><?= h($student->last_name) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><a class="profile-email" href="mailto:<?= h($student->email) ?>"><?= h($student->email) ?></a></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?= h($student->phone_number) ?: '<span style="color:#bcc8d8">&mdash;</span>' ?></td>
                </tr>
            </table>

            <!-- Academic Info -->
            <div class="profile-section-label">Academic Information</div>
            <table class="profile-table">
                <tr>
                    <th>Student Number</th>
                    <td><span class="val-badge"><?= h($student->student_number) ?></span></td>
                </tr>
                <tr>
                    <th>Registration Number</th>
                    <td><span class="val-badge"><?= h($student->registration_number) ?></span></td>
                </tr>
            </table>

            <!-- System Info -->
            <div class="profile-section-label">System Information</div>
            <table class="profile-table">
                <tr>
                    <th>Record ID</th>
                    <td><span class="val-date">#<?= h($studentId) ?></span></td>
                </tr>
                <tr>
                    <th>Registered On</th>
                    <td><span class="val-date"><?= h(date('d M Y, H:i', strtotime($student->created_at))) ?></span></td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td><span class="val-date"><?= h(date('d M Y, H:i', strtotime($student->updated_at))) ?></span></td>
                </tr>
            </table>

            <!-- Actions -->
            <div class="profile-actions">
                <a class="profile-btn profile-btn-edit" href="edit.php?id=<?= $studentId ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Student
                </a>
                <a class="profile-btn profile-btn-back" href="index.php">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Back to List
                </a>
                <form action="delete.php" method="post" onsubmit="return confirm('Delete <?= $fullName ?>? This cannot be undone.')">
                    <input type="hidden" name="id" value="<?= $studentId ?>">
                    <button class="profile-btn profile-btn-delete" type="submit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        Delete
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
</body>
</html>
