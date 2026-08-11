<?php
require_once __DIR__ . '/config.php';
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' — ' . COMPANY_NAME : COMPANY_NAME; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap site-header__inner">
    <a href="index.php" class="brand">
      <span class="brand__mark">AU</span>
      <span class="brand__text">
        <strong><?php echo htmlspecialchars(COMPANY_NAME); ?></strong>
        <small>Quality Control — Live Dashboard</small>
      </span>
    </a>
    <nav class="site-nav">
      <a href="index.php" class="<?php echo $current === 'index.php' ? 'is-active' : ''; ?>">Dashboard</a>
      <a href="add_report.php" class="<?php echo $current === 'add_report.php' ? 'is-active' : ''; ?>">Add Report</a>
    </nav>
  </div>
</header>
<main class="wrap page">
