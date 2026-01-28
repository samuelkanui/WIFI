<?php

namespace App\Services;

use App\Models\Tariff;
use App\Models\Voucher;
use RouterOS\Client;
use RouterOS\Query;

class MikroTikService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'host' => config('services.mikrotik.host'),
            'user' => config('services.mikrotik.user'),
            'pass' => config('services.mikrotik.pass'),
            'port' => config('services.mikrotik.port', 8728),
        ]);
    }

    /**
     * Add a voucher user to MikroTik
     */
    public function addUser(string $voucherCode, Tariff $tariff): bool
    {
        try {
            $query = new Query('/ip/hotspot/user/add');
            $query->equal('name', $voucherCode);
            $query->equal('password', $voucherCode);
            $query->equal('profile', 'hotspot-' . $tariff->id);

            // Set limits if specified
            if ($tariff->duration_minutes) {
                $query->equal('limit-uptime', $tariff->duration_minutes . 'm');
            }

            if ($tariff->data_limit_bytes) {
                $limitMB = round($tariff->data_limit_bytes / 1048576);
                $query->equal('limit-bytes-total', $limitMB . 'M');
            }

            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            \Log::error('MikroTik add user failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a user from MikroTik
     */
    public function removeUser(string $voucherCode): bool
    {
        try {
            // Find the user
            $query = new Query('/ip/hotspot/user/print');
            $query->where('name', $voucherCode);
            $users = $this->client->query($query)->read();

            if (empty($users)) {
                return true; // Already removed
            }

            // Remove the user
            $userId = $users[0]['.id'];
            $query = new Query('/ip/hotspot/user/remove');
            $query->equal('.id', $userId);
            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            \Log::error('MikroTik remove user failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active hotspot sessions
     */
    public function getActiveSessions(): array
    {
        try {
            $query = new Query('/ip/hotspot/active/print');
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error('MikroTik get sessions failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a hotspot profile for a tariff
     */
    public function createProfile(Tariff $tariff): bool
    {
        try {
            $profileName = 'hotspot-' . $tariff->id;

            // Check if profile exists
            $query = new Query('/ip/hotspot/user/profile/print');
            $query->where('name', $profileName);
            $profiles = $this->client->query($query)->read();

            if (!empty($profiles)) {
                return true; // Already exists
            }

            // Create profile
            $query = new Query('/ip/hotspot/user/profile/add');
            $query->equal('name', $profileName);
            $query->equal('rate-limit', $tariff->upload_speed_kbps . 'k/' . $tariff->download_speed_kbps . 'k');

            if ($tariff->duration_minutes) {
                $query->equal('session-timeout', $tariff->duration_minutes . 'm');
            }

            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            \Log::error('MikroTik create profile failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Login a user to the hotspot
     */
    public function loginUser(string $username, string $password, string $macAddress = ''): bool
    {
        try {
            $query = new Query('/ip/hotspot/active/login');
            $query->equal('user', $username);
            $query->equal('password', $password);

            if ($macAddress) {
                $query->equal('mac-address', $macAddress);
            }

            $this->client->query($query)->read();

            return true;
        } catch (\Exception $e) {
            \Log::error('MikroTik login failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync active sessions to database
     */
    public function syncSessions(): void
    {
        $sessions = $this->getActiveSessions();

        foreach ($sessions as $session) {
            \App\Models\UserSession::updateOrCreate(
                [
                    'voucher_code' => $session['user'] ?? '',
                    'mac_address' => $session['mac-address'] ?? '',
                ],
                [
                    'ip_address' => $session['address'] ?? '',
                    'started_at' => now()->subSeconds($session['uptime'] ?? 0),
                    'bytes_in' => $session['bytes-in'] ?? 0,
                    'bytes_out' => $session['bytes-out'] ?? 0,
                ]
            );
        }
    }
}
