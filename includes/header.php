<?php
require_once __DIR__ . '/config.php';

$current = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo isset($page_title)
            ? htmlspecialchars($page_title) . ' — ' . COMPANY_NAME
            : COMPANY_NAME;
        ?>
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap"
        rel="stylesheet"
    >

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="site-header">

    <div class="site-header__inner">

        <!-- =========================================================
             BRAND
             ========================================================= -->

        <a href="index.php" class="brand">

            <span class="brand__mark">
                AU
            </span>

            <span class="brand__text">

                <strong>
                    <?php echo htmlspecialchars(COMPANY_NAME); ?>
                </strong>

                <small>
                    Quality Control
                </small>

            </span>

        </a>


        <!-- =========================================================
             MAIN NAVIGATION
             ========================================================= -->

        <nav class="site-nav" aria-label="Main navigation">

            <a
                href="index.php"
                class="<?php echo $current === 'index.php' ? 'is-active' : ''; ?>"
            >
                <span class="site-nav__icon">▦</span>
                <span>Dashboard</span>
            </a>

            <a
                href="add_report.php"
                class="<?php echo $current === 'add_report.php' ? 'is-active' : ''; ?>"
            >
                <span class="site-nav__icon">＋</span>
                <span>Add Report</span>
            </a>

        </nav>


        <!-- =========================================================
             HEADER STATUS / ACTION AREA
             ========================================================= -->

        <div class="header-tools">

            <div class="header-status">

                <span class="header-status__dot"></span>

                <span class="header-status__text">
                    System Live
                </span>

            </div>

            <a
                href="add_report.php"
                class="header-add-btn"
            >
                <span class="header-add-btn__icon">+</span>
                <span>Add Report</span>
            </a>

        </div>

    </div>

</header>


<!-- ================================================================
     MAIN CONTENT
     Pages that include this header will render their content here.
     footer.php closes this element.
     ================================================================ -->

<main class="wrap page">