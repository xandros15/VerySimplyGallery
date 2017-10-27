<?php
/** @var $images iterable */
/** @var $pagination array */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>gallery</title>
</head>
<body>
<main>
    <ul>
        <?php foreach ($images as $image): ?>
            <?php render('_image.php', ['image' => $image]) ?>
        <?php endforeach; ?>
    </ul>
    <section class="pagination">
        <?php if ($pagination['prev'] !== false): ?>
            <a href="?page=<?= $pagination['prev'] ?>">Previous</a>
        <?php endif; ?>
        <?php if ($pagination['next'] !== false): ?>
            <a href="?page=<?= $pagination['next'] ?>">Next</a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
