<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'RoomFinder.vn' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css">
    <link rel="stylesheet" href="/assets/css/location.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/login-modal.css">

    <script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
    <script src="/assets/js/location-service.js"></script>

    <!-- Page CSS -->
    <?php if (!empty($css)) : ?>
        <?php foreach ($css as $file) : ?>
            <link rel="stylesheet" href="/assets/css/<?= $file ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<!-- HEADER -->
<?php require __DIR__ . '/../components/header.php'; ?>

<!-- CONTENT -->
<?= $content ?>

<!-- FOOTER (chỉ homepage có) -->
<?php if (!empty($showFooter)) : ?>
    <?php require __DIR__ . '/../components/footer.php'; ?>
<?php endif; ?>

<!-- LOGIN MODAL -->
<?php require __DIR__ . '/../components/login-modal.php'; ?>

<!-- Global JS -->
<script src="/assets/js/main.js"></script>

<!-- Page JS -->
<?php if (!empty($js)) : ?>
    <?php foreach ($js as $file) : ?>
        <script src="/assets/js/<?= $file ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>