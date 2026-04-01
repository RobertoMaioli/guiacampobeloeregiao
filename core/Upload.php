<?php
/**
 * core/Upload.php
 * Upload seguro de imagens para /assets/img/lugares/
 */

require_once __DIR__ . '/../config/database.php';

class Upload
{
    /**
     * Processa o upload de uma imagem
     * @param  array  $file     $_FILES['campo']
     * @param  string $subfolder  ex: 'lugares/42/'
     * @return array  ['ok'=>bool, 'url'=>string, 'erro'=>string]
     */
    public static function image(array $file, string $subfolder = ''): array
    {
        // 1. Erro do PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'erro' => 'Erro no upload: código ' . $file['error']];
        }

        // 2. Tamanho
        $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'erro' => 'Arquivo maior que ' . UPLOAD_MAX_MB . 'MB.'];
        }

        // 3. MIME via magic bytes (sem extensões externas)
        $f     = fopen($file['tmp_name'], 'rb');
        $bytes = fread($f, 12);
        fclose($f);
        
        $mime = match(true) {
            str_starts_with($bytes, "\xFF\xD8\xFF")                                      => 'image/jpeg',
            str_starts_with($bytes, "\x89PNG\r\n\x1A\n")                                 => 'image/png',
            str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP'           => 'image/webp',
            default                                                                       => $file['type'],
        };
        if (!in_array($mime, UPLOAD_ALLOWED, true)) {
            return ['ok' => false, 'erro' => 'Formato não permitido. Use JPG, PNG ou WebP.'];
        }

        // 4. Extensão segura
        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        // 5. Nome único
        $filename = uniqid('img_', true) . '.' . $ext;

        // 6. Diretório destino
        $dir = rtrim(UPLOAD_DIR . $subfolder, '/') . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $destPath = $dir . $filename;
        $destUrl  = rtrim(UPLOAD_URL . $subfolder, '/') . '/' . $filename;

        // 7. Move
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'erro' => 'Falha ao salvar o arquivo no servidor.'];
        }

        return ['ok' => true, 'url' => $destUrl, 'path' => $destPath];
    }

    /** Remove um arquivo de imagem pelo caminho relativo */
    public static function delete(string $url): bool
    {
        $path = __DIR__ . '/../' . ltrim($url, '/');
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        return false;
    }
}
