<?php

namespace App\Support\Contracts;

/**
 * Kontrak gateway WhatsApp - implementasi: FonnteService. Driver aktif
 * dipilih via config services.whatsapp.driver (WHATSAPP_DRIVER), pola sama
 * persis dgn project larashop-be.
 */
interface WhatsappGateway
{
    public function sendMessage(string $phone, string $message): bool;
}
