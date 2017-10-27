<?php

require_once __DIR__ . '/../src/functions.php';
$page = (int) ($_GET['page'] ?? 0);
$length = 5;
$offset = $length * $page;
$total = countImages();
if ($total < $offset + $length) {
    render('notfound.php');
} else {
    render('gallery.php', [
        'images' => readImages($offset, $length),
        'pagination' => [
            'prev' => $page - 1 < 0 ? false : $page - 1,
            'next' => $total < $offset + $length * 2 ? false : $page + 1,
        ],
    ]);
}
