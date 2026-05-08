<?php
/**
 * core/Asaas.php
 * Wrapper central para comunicação com a API do Asaas
 */

require_once __DIR__ . '/../config/asaas.php';
require_once __DIR__ . '/DB.php';

class AsaasException extends Exception
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
        error_log('[AsaasException] ' . $message);
    }
}

class Asaas
{
    private static function request(string $method, string $endpoint, array $data = []): array
    {
        $url = ASAAS_BASE_URL . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . ASAAS_KEY,
                'User-Agent: GuiaCampoBelo/1.0',
            ],
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new AsaasException('cURL erro: ' . $error);
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $msg = $decoded['errors'][0]['description'] ?? 'Erro desconhecido na API do Asaas';
            throw new AsaasException($msg . ' (HTTP ' . $httpCode . ')');
        }

        return $decoded ?? [];
    }
    
    public static function createCustomer(int $empresa_id, string $nome, string $email): string
    {
        // Busca todos os dados necessários
        $empresa = DB::row(
            'SELECT e.cpf_cnpj,
            l.telefone, l.whatsapp,
            l.endereco, l.endereco_numero, l.bairro, l.cep
             FROM empresas e
             LEFT JOIN lugares l ON l.id = e.lugar_id
             WHERE e.id = ?',
            [$empresa_id]
        );
    
        $dados = [
            'name'                 => $nome,
            'email'                => $email,
            'notificationDisabled' => false,
        ];
    
        // CPF/CNPJ
        if (!empty($empresa['cpf_cnpj'])) {
            $dados['cpfCnpj'] = $empresa['cpf_cnpj'];
        }
    
        // Telefone — lógica: se tiver os dois, fixo=phone e whatsapp=mobilePhone
        // se tiver só um, vai para phone
        $telefone = preg_replace('/\D/', '', $empresa['telefone'] ?? '');
        $whatsapp  = preg_replace('/\D/', '', $empresa['whatsapp'] ?? '');
    
        if (!empty($telefone) && !empty($whatsapp)) {
            $dados['phone']       = $telefone;
            $dados['mobilePhone'] = $whatsapp;
        } elseif (!empty($whatsapp)) {
            $dados['phone'] = $whatsapp;
        } elseif (!empty($telefone)) {
            $dados['phone'] = $telefone;
        }
    
        if (!empty($empresa['endereco'])) {
            $dados['address']       = $empresa['endereco'];
            $dados['addressNumber'] = !empty($empresa['endereco_numero'])
                                      ? $empresa['endereco_numero']
                                      : 'S/N';
        }
            
        // Bairro
        if (!empty($empresa['bairro'])) {
            $dados['province'] = $empresa['bairro'];
        }
    
        // CEP
        $cep = preg_replace('/\D/', '', $empresa['cep'] ?? '');
        if (!empty($cep)) {
            $dados['postalCode'] = $cep;
        }
    
        $response    = self::request('POST', '/customers', $dados);
        $customer_id = $response['id'] ?? null;
    
        if (!$customer_id) {
            throw new AsaasException('Falha ao criar cliente — ID não retornado.');
        }
    
        DB::exec(
            'UPDATE empresas SET asaas_customer_id = ? WHERE id = ?',
            [$customer_id, $empresa_id]
        );
    
        return $customer_id;
    }
    
    public static function getOrCreateCustomer(int $empresa_id, string $nome, string $email): string
    {
        // Se já tem customer_id salvo no banco, retorna ele
        $empresa = DB::row(
            'SELECT asaas_customer_id FROM empresas WHERE id = ?',
            [$empresa_id]
        );
    
        if (!empty($empresa['asaas_customer_id'])) {
            // Atualiza CPF/CNPJ no Asaas se ainda não tiver
            $cpf = DB::row('SELECT cpf_cnpj FROM empresas WHERE id = ?', [$empresa_id]);
            if (!empty($cpf['cpf_cnpj'])) {
                self::request('POST', '/customers/' . $empresa['asaas_customer_id'], [
                    'cpfCnpj' => $cpf['cpf_cnpj'],
                ]);
            }
            return $empresa['asaas_customer_id'];
        }
    
        // Senão, cria um novo cliente no Asaas
        return self::createCustomer($empresa_id, $nome, $email);
    }
    
    
    public static function createCheckout(int $empresa_id, string $customer_id, string $plano, string $billing_type): string
    {
        $valores = [
            'profissional' => 89.00,
            'premium'      => 159.00,
        ];
    
        if (!isset($valores[$plano])) {
            throw new AsaasException('Plano inválido: ' . $plano);
        }
    
        // Salva o plano pretendido no banco para usar no webhook
        DB::exec(
            'UPDATE empresas SET plan_intent = ? WHERE id = ?',
            [$plano, $empresa_id]
        );
    
        $response = self::request('POST', '/checkouts', [
            'billingTypes' => [$billing_type],
            'chargeTypes'  => ['RECURRENT'],
            'customer'     => $customer_id,
            'items'        => [
                [
                    'name'        => 'Plano ' . ucfirst($plano),
                    'description' => 'Assinatura mensal — Guia Campo Belo',
                    'quantity'    => 1,
                    'value'       => $valores[$plano],
                ]
            ],
            'subscription' => [
                'cycle'       => 'MONTHLY',
                'nextDueDate' => date('Y-m-d'),
            ],
            'callback' => [
                'successUrl' => 'https://gcbr.maiolidesign.com.br/empresa/status.php',
                'cancelUrl'  => 'https://gcbr.maiolidesign.com.br/empresa/plano.php',
            ],
        ]);
    
        $url = $response['link'] ?? null;
        if (!$url) {
            throw new AsaasException('Falha ao criar checkout — URL não retornada.');
        }
    
        return $url;
    }
    
    
    public static function createSubscription(int $empresa_id, string $customer_id, string $plano, string $billing_type): array
    {
        $valores = [
            'profissional' => 89.00,
            'premium'      => 159.00,
        ];
    
        if (!isset($valores[$plano])) {
            throw new AsaasException('Plano inválido: ' . $plano);
        }
    
        $response = self::request('POST', '/subscriptions', [
            'customer'        => $customer_id,
            'billingType'     => $billing_type, // 'CREDIT_CARD' ou 'PIX'
            'value'           => $valores[$plano],
            'cycle'           => 'MONTHLY',
            'nextDueDate'     => date('Y-m-d'),
            'description'     => 'Plano ' . ucfirst($plano) . ' — Guia Campo Belo',
        ]);
    
        $subscription_id = $response['id'] ?? null;
    
        if (!$subscription_id) {
            throw new AsaasException('Falha ao criar assinatura — ID não retornado.');
        }
        
        // Busca a primeira cobrança gerada pela assinatura
        $payments    = self::request('GET', '/subscriptions/' . $subscription_id . '/payments');
        $invoice_url = $payments['data'][0]['invoiceUrl'] ?? null;


        // Salva o subscription_id no banco
        DB::exec(
            'UPDATE empresas SET asaas_subscription_id = ?, plano_ativo = ? WHERE id = ?',
            [$subscription_id, $plano, $empresa_id]
        );
    
        $response['invoiceUrl'] = $invoice_url;
        return $response;
    }
    
    public static function cancelSubscription(int $empresa_id): bool
    {
        $empresa = DB::row(
            'SELECT asaas_subscription_id FROM empresas WHERE id = ?',
            [$empresa_id]
        );
    
        if (empty($empresa['asaas_subscription_id'])) {
            throw new AsaasException('Nenhuma assinatura encontrada para esta empresa.');
        }
    
        self::request('DELETE', '/subscriptions/' . $empresa['asaas_subscription_id']);
    
        // Rebaixa para essencial e limpa o subscription_id
        DB::exec(
            'UPDATE empresas SET plano_ativo = ?, asaas_subscription_id = NULL WHERE id = ?',
            ['essencial', $empresa_id]
        );
    
        // Loga a ação
        DB::exec(
            'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em) VALUES (?, ?, ?, NOW())',
            [$empresa_id, 'cancelamento', 'Assinatura cancelada via Asaas.']
        );
    
        return true;
    }
}