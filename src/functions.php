<?php


function uploadMultipleFiles(array $files): bool
{
    try {
        foreach ($files['error'] as $key => $error) {//error zawsze jest, nawet w przypadku blednego uploadu
            $file = [ //niestety musielsimy przemapowac uploadowany plik
                'name' => $files['name'][$key] ?? '',
                'type' => $files['type'][$key] ?? '',
                'tmp_name' => $files['tmp_name'][$key] ?? '',
                'error' => $files['error'][$key],
                'size' => $files['size'][$key] ?? 0,
            ];
            if (!uploadFile($file)) {
                return false;
            }
        }

        return (bool) $files;

    } catch (\Throwable $exception) {
        //@todo obsluga wyjatku i pokazywanie bledu
        echo $exception->getMessage();
    }

    return false;
}

/**
 * @todo refaktoryzacja funkcji
 *
 * @param array $file
 *
 * @return bool
 * @throws \ErrorException
 */
function uploadFile(array $file): bool
{
    if (validateFile($file)) {
        $uploadRoot = __DIR__ . '/../web/upload/';
        $uploadWebRoot = '/upload/';
        $name = uniqName($file['tmp_name']) . guessExtension($file['tmp_name']);

        if (is_file($uploadRoot . $name)) {
            throw new \ErrorException('File exist');
        }

        if (move_uploaded_file($file['tmp_name'], $uploadRoot . $name)) {
            createImage($uploadWebRoot . $name);

            return true;
        }
    }

    return false;
}


function uniqName(string $filename): string
{
    return md5_file($filename);
}

function guessExtension(string $filename): string
{
    $map = [
        'image/jpeg' => '.jpg',
        'image/gif' => '.gif',
        'image/png' => '.png',
    ];

    return $map[(new finfo(FILEINFO_MIME_TYPE))->file($filename)] ?? '.jpg';
}

function validateFile(array $file): bool
{
    if (!isset($file['error']) || $file['error'] != UPLOAD_ERR_OK) {
        throw new \ErrorException('Error with upload file');
    }

    if (!(filesize($file['tmp_name']) < 1024 * 1024 * 4)) {

        throw new \ErrorException('Bad filesize');
    }
    if (!in_array((new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']), [
        'image/jpeg',
        'image/gif',
        'image/png',
    ])) {
        throw new \ErrorException('Wrong file type');
    }

    return true;
}

//messages

function isPost(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}

/**
 * very simply renderer
 *
 * @param string $view
 * @param array $params
 */
function render(string $view, array $params = [])
{
    extract($params);
    include __DIR__ . '/../templates/' . func_get_arg(0);
}

/**
 * simply redirect
 *
 * @param string $path
 * @param int $code
 */
function redirect(string $path, $code = 302)
{
    header('Location: ' . $path, true, $code);
}


//baza

/**
 * @return PDO
 */
function connect(): PDO
{
    static $connection;
    if (!$connection) {
        $params = require __DIR__ . '/../config/database.php';
        $connection = new PDO(
            "mysql:host={$params['host']};dbname={$params['name']};charset=utf8",
            $params['login'],
            $params['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $connection;
}


/**
 * @param string $path
 *
 * @return bool
 */
function createImage(string $path): bool
{
    $stmt = connect()->prepare("INSERT INTO gallery (path) VALUES (:path)");

    return $stmt->execute(['path' => $path]);
}


/**
 * @param int $id
 *
 * @return bool
 */
function deleteImage(int $id): bool
{
    $stmt = connect()->prepare("DELETE FROM gallery WHERE id = :id");

    return $stmt->execute(['id' => $id]);
}

function countImages(): int
{
    return connect()->query('SELECT count(*) FROM gallery')->fetch(PDO::FETCH_NUM)[0] ?? 0;
}

/**
 * @param int $offset
 * @param int $length
 *
 * @return iterable
 */
function readImages(int $offset = 0, int $length = 5): iterable
{
    $stmt = connect()->query("SELECT * FROM gallery ORDER BY created_at,id DESC LIMIT {$offset}, {$length}",
        PDO::FETCH_ASSOC);

    foreach ($stmt as $record) {
        yield $record;
    }
}
