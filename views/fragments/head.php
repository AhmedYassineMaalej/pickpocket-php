<?php function head(string $title, string $csspath) { ?>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= $title ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/bookmark_button.js" defer></script>
        <link href="css/common.css" rel="stylesheet">
        <link href="/css/<?= $csspath?>" rel="stylesheet">
        <link rel="stylesheet" href="/css/sidebar.css"
        <link rel="stylesheet" href="/css/catalog.css"

    </head>
<?php } ?>
