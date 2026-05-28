<?php function head(string $title, array $css = [], array $js = [])
{ ?>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>Pickpocket | <?= $title ?></title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <link href="css/common.css" rel="stylesheet">

        <?php foreach ($css as $path): ?>
        <link href="/css/<?=$path?>" rel="stylesheet">
        <?php endforeach; ?>


        <?php foreach ($js as $path): ?>
        <script src="/js/<?=$path?>" defer></script>
        <?php endforeach; ?>
    </head>
<?php } ?>
