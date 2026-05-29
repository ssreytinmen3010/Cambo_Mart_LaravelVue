<?php

namespace App\Services\Bakong;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BakongKhqrService
{
    public function generateMerchantQr(array $data): array
    {
        $merchantName = $data['merchant_name'] ?? config('services.bakong.merchant_name', 'DAVIT YEM');
        $merchantCity = $data['merchant_city'] ?? config('services.bakong.merchant_city', 'Phnom Penh');
        $accountId = $data['account_id']
            ?? config('services.bakong.account_id')
            ?? Setting::get('bakong_account_id');
        $merchantId = $data['merchant_id'] ?? config('services.bakong.merchant_id', '123456');
        $acquiringBank = $data['acquiring_bank'] ?? config('services.bakong.acquiring_bank', 'Dev Bank');
        $currency = $this->normalizeCurrencyCode($data['currency'] ?? config('services.bakong.currency', 'USD'));
        $amount = number_format((float) ($data['amount'] ?? 0), 2, '.', '');
        $billNumber = $data['bill_number'] ?? null;
        $expiryMinutes = (int) ($data['expires_in_minutes'] ?? config('services.bakong.qr_timeout_minutes', 10));
        $merchantCategoryCode = $data['merchant_category_code'] ?? config('services.bakong.merchant_category_code', '5999');
        $countryCode = strtoupper((string) ($data['country_code'] ?? config('services.bakong.country_code', 'KH')));
        $purpose = $data['purpose'] ?? ($billNumber ? "Order {$billNumber}" : 'Payment');
        $upiAccountInformation = $data['upi_account_information'] ?? null;
        $mobileNumber = $data['mobile_number'] ?? null;
        $storeLabel = $data['store_label'] ?? null;
        $terminalLabel = $data['terminal_label'] ?? null;
        $language = $data['language'] ?? null;
        $alternateName = $data['merchant_name_alt'] ?? null;
        $alternateCity = $data['merchant_city_alt'] ?? null;
        $merchantType = $data['merchant_type'] ?? '30';
        $pointOfInitiationMethod = $data['point_of_initiation_method'] ?? '12';

        if (! $accountId) {
            throw new RuntimeException('Bakong account ID is not configured.');
        }

        $payload = $this->buildTlvs([
            '00' => '01',
            '01' => $pointOfInitiationMethod,
            '30' => $this->buildTlvs([
                '00' => $accountId,
                '01' => $merchantId,
                '02' => $acquiringBank,
            ]),
            '52' => $merchantCategoryCode,
            '53' => $currency,
            '54' => $amount,
            '58' => $countryCode,
            '59' => $merchantName,
            '60' => $merchantCity,
            '62' => $this->buildTlvs(array_filter([
                '01' => $billNumber,
                '02' => $mobileNumber,
                '03' => $storeLabel,
                '07' => $terminalLabel,
                '08' => $purpose,
            ], static fn ($value) => $value !== null && $value !== '')),
            '64' => $this->buildTlvs(array_filter([
                '00' => $language,
                '01' => $alternateName,
                '02' => $alternateCity,
            ], static fn ($value) => $value !== null && $value !== '')),
        ]);

        if ($upiAccountInformation) {
            $payload = substr_replace($payload, $this->buildTlv('15', $upiAccountInformation), 0, 0);
        }

        $qr = $payload . '6304';
        $md5 = md5($qr);
        $qr .= $this->crc16($qr);

        return [
            'qr' => $qr,
            'md5' => $md5,
            'expires_at' => now()->addMinutes(max(1, $expiryMinutes)),
            'merchant_name' => $merchantName,
            'merchant_city' => $merchantCity,
            'account_id' => $accountId,
            'currency' => $currency,
            'amount' => $amount,
            'bill_number' => $billNumber,
        ];
    }

    public function checkTransactionByMd5(string $md5): array
    {
        $token = config('services.bakong.token');
        $baseUrl = rtrim((string) config('services.bakong.base_url', 'https://api-bakong.nbc.org.kh'), '/');

        if (! $token) {
            throw new RuntimeException('Bakong API token is not configured.');
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->post($baseUrl . '/v1/check_transaction_by_md5', [
                'md5' => $md5,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                $response->json('responseMessage')
                    ?? $response->json('message')
                    ?? 'Unable to verify Bakong transaction.'
            );
        }

        return $response->json();
    }

    private function normalizeCurrencyCode(string $currency): string
    {
        $currency = strtoupper(trim($currency));

        return match ($currency) {
            'KHR', '116' => '116',
            default => '840',
        };
    }

    private function buildTlvs(array $fields): string
    {
        $payload = '';

        foreach ($fields as $tag => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $payload .= $this->buildTlv((string) $tag, (string) $value);
        }

        return $payload;
    }

    private function buildTlv(string $tag, string $value): string
    {
        $length = strlen($value);

        return str_pad($tag, 2, '0', STR_PAD_LEFT) . str_pad((string) $length, 2, '0', STR_PAD_LEFT) . $value;
    }

    private function crc16(string $payload): string
    {
        $crc = 0xFFFF;
        $bytes = str_split($payload);

        foreach ($bytes as $byte) {
            $crc ^= ord($byte) << 8;

            for ($i = 0; $i < 8; $i++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
