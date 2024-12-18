<?php
function renderHead($page_title = '') {
    ?>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title ? "$page_title - " : ""; ?>GreenVille</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="/css/main.css">
    </head>
    <?php
}
