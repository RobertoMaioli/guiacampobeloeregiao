<?php
/**
 * core/Google.php
 * Integração com Google Places API via cURL
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/DB.php';

class Google
{
    private static string $baseUrl = 'https://maps.googleapis.com/maps/api/place';

    public static function fetchPlace(string $placeId): ?array
    {
        $fields = 'rating,user_ratings_total,reviews';
        $url    = sprintf(
            '%s/details/json?place_id=%s&fields=%s&language=pt-BR&key=%s',
            self::$baseUrl,
            urlencode($placeId),
            $fields,
            GOOGLE_PLACES_KEY
        );

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'GuiaCampoBelo/1.0',
        ]);
        $resp   = curl_exec($ch);
        $errno  = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);

        if ($errno || !$resp) {
            error_log('[Google] cURL erro ' . $errno . ': ' . $errmsg . ' — Place ID: ' . $placeId);
            return null;
        }

        $data = json_decode($resp, true);

        if (($data['status'] ?? '') !== 'OK') {
            error_log('[Google] Status: ' . ($data['status'] ?? 'unknown') . ' — Place ID: ' . $placeId);
            return null;
        }

        return $data['result'] ?? null;
    }

    public static function syncLugar(int $lugarId): array
    {
        $lugar = DB::row(
            'SELECT id, nome, google_place_id FROM lugares WHERE id = ?',
            [$lugarId]
        );

        if (!$lugar) {
            return ['ok' => false, 'erro' => 'Lugar não encontrado.'];
        }

        if (empty($lugar['google_place_id'])) {
            return ['ok' => false, 'erro' => 'Place ID do Google não cadastrado.'];
        }

        $result = self::fetchPlace($lugar['google_place_id']);

        if (!$result) {
            return ['ok' => false, 'erro' => 'Não foi possível conectar à API do Google. Verifique a key e o Place ID.'];
        }

        $rating       = (float) ($result['rating']             ?? 0);
        $totalReviews = (int)   ($result['user_ratings_total'] ?? 0);
        $reviews      = $result['reviews'] ?? [];

        // Atualiza rating do lugar
        DB::exec(
            'UPDATE lugares SET rating = ?, total_reviews = ?, google_synced_at = NOW() WHERE id = ?',
            [$rating, $totalReviews, $lugarId]
        );

        // Insere avaliações novas
        $inseridas = 0;
        foreach ($reviews as $rev) {
            $fonteId = $lugar['google_place_id'] . '_' . ($rev['time'] ?? uniqid());

            $existe = DB::row(
                'SELECT id FROM avaliacoes WHERE fonte = ? AND fonte_id = ?',
                ['google', $fonteId]
            );
            if ($existe) continue;

            DB::exec(
                'INSERT INTO avaliacoes
                    (lugar_id, fonte, fonte_id, autor_nome, autor_foto, nota, texto, data_avaliacao, aprovado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)',
                [
                    $lugarId,
                    'google',
                    $fonteId,
                    $rev['author_name']       ?? 'Anônimo',
                    $rev['profile_photo_url'] ?? null,
                    (float) ($rev['rating']   ?? 5),
                    $rev['text']              ?? null,
                    isset($rev['time']) ? date('Y-m-d', $rev['time']) : date('Y-m-d'),
                ]
            );
            $inseridas++;
        }

        return [
            'ok'        => true,
            'nome'      => $lugar['nome'],
            'rating'    => $rating,
            'reviews'   => $totalReviews,
            'inseridas' => $inseridas,
        ];
    }

    public static function syncAll(): array
    {
        $lugares = DB::query(
            'SELECT id FROM lugares WHERE google_place_id IS NOT NULL AND google_place_id != "" AND ativo = 1'
        );

        $resultados = [];
        foreach ($lugares as $l) {
            $resultados[] = self::syncLugar((int) $l['id']);
            usleep(300000); // 300ms entre chamadas
        }

        return $resultados;
    }
}