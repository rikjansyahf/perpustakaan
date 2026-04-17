<?php include __DIR__ . '/app_settings.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= htmlspecialchars($cfg['app_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .modal-backdrop { backdrop-filter: blur(2px); }
    </style>
</head>
<body class="bg-gray-50">
<div class="flex h-screen overflow-hidden">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
<?php include __DIR__ . '/topbar.php'; ?>
<main class="flex-1 overflow-y-auto p-6">
