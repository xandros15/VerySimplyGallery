<?php
require_once __DIR__ . '/../src/functions.php';

if (isPost() && uploadMultipleFiles($_FILES['image'])) {
    redirect('gallery.php');
} else {
    render('upload.php');
}
