<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Spinner } from '@/components/ui/spinner';

interface Tariff {
    id: number;
    name: string;
    price: number;
    duration_minutes: number | null;
    data_limit_bytes: number | null;
    download_speed_kbps: number;
    upload_speed_kbps: number;
}

const props = defineProps<{
    tariff: Tariff;
}>();

const phone = ref('254');
const loading = ref(false);
const message = ref('');
const success = ref(false);
const voucher = ref('');
const checkoutId = ref('');
const pollInterval = ref<number | null>(null);

const formatBytes = (bytes: number | null) => {
    if (!bytes) return 'Unlimited';
    const gb = bytes / (1024 * 1024 * 1024);
    return gb >= 1 ? `${gb.toFixed(1)} GB` : `${(bytes / (1024 * 1024)).toFixed(0)} MB`;
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return 'Unlimited';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
};

const formatSpeed = (kbps: number) => {
    const mbps = kbps / 1024;
    return mbps >= 1 ? `${mbps.toFixed(1)} Mbps` : `${kbps} Kbps`;
};

const pay = async () => {
    if (phone.value.length !== 12 || !phone.value.startsWith('254')) {
        message.value = 'Please enter a valid phone number (254XXXXXXXXX)';
        success.value = false;
        return;
    }

    loading.value = true;
    message.value = '';

    try {
        const response = await fetch('/payment/mpesa/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                phone: phone.value,
                tariff_id: props.tariff.id,
            }),
        });

        const data = await response.json();

        if (data.success) {
            checkoutId.value = data.checkout_id;
            message.value = 'STK Push sent! Please enter your M-Pesa PIN on your phone.';
            success.value = true;
            startPolling();
        } else {
            message.value = data.message || 'Payment initiation failed';
            success.value = false;
            loading.value = false;
        }
    } catch (error) {
        message.value = 'Network error. Please try again.';
        success.value = false;
        loading.value = false;
    }
};

const startPolling = () => {
    pollInterval.value = window.setInterval(async () => {
        try {
            const response = await fetch(`/payment/mpesa/status/${checkoutId.value}`);
            const data = await response.json();

            if (data.status === 'paid') {
                stopPolling();
                voucher.value = data.voucher;
                message.value = 'Payment successful! Your voucher code is ready.';
                loading.value = false;
            } else if (data.status === 'failed') {
                stopPolling();
                message.value = 'Payment failed. Please try again.';
                success.value = false;
                loading.value = false;
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 5000);
};

const stopPolling = () => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
};

const copyVoucher = () => {
    navigator.clipboard.writeText(voucher.value);
    alert('Voucher code copied to clipboard!');
};

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head :title="`Pay for ${tariff.name}`" />

    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <Card class="w-full max-w-md">
            <CardHeader>
                <CardTitle>Pay with M-Pesa</CardTitle>
                <CardDescription>{{ tariff.name }}</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Tariff Details -->
                <div class="rounded-lg border bg-muted/50 p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Price:</span>
                        <span class="font-semibold">KES {{ tariff.price }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Duration:</span>
                        <span>{{ formatDuration(tariff.duration_minutes) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Data Limit:</span>
                        <span>{{ formatBytes(tariff.data_limit_bytes) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Speed:</span>
                        <span>{{ formatSpeed(tariff.download_speed_kbps) }} / {{ formatSpeed(tariff.upload_speed_kbps) }}</span>
                    </div>
                </div>

                <!-- Payment Form -->
                <div v-if="!voucher" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="phone">M-Pesa Phone Number</Label>
                        <Input
                            id="phone"
                            v-model="phone"
                            type="tel"
                            placeholder="254712345678"
                            :disabled="loading"
                            maxlength="12"
                        />
                        <p class="text-xs text-muted-foreground">
                            Enter your Safaricom number (starts with 254)
                        </p>
                    </div>

                    <Button
                        @click="pay"
                        :disabled="loading"
                        class="w-full"
                        size="lg"
                    >
                        <Spinner v-if="loading" class="mr-2 h-4 w-4" />
                        {{ loading ? 'Processing...' : `Pay KES ${tariff.price}` }}
                    </Button>
                </div>

                <!-- Status Message -->
                <Alert v-if="message" :variant="success ? 'default' : 'destructive'">
                    <AlertDescription>{{ message }}</AlertDescription>
                </Alert>

                <!-- Voucher Display -->
                <div v-if="voucher" class="space-y-4">
                    <Alert>
                        <AlertDescription class="text-center">
                            <div class="text-sm font-medium mb-2">Your Voucher Code:</div>
                            <div class="text-2xl font-bold tracking-wider font-mono bg-background p-3 rounded border">
                                {{ voucher }}
                            </div>
                        </AlertDescription>
                    </Alert>

                    <Button @click="copyVoucher" variant="outline" class="w-full">
                        Copy Code
                    </Button>

                    <div class="text-sm text-muted-foreground text-center space-y-1">
                        <p>Use this code to login to the WiFi hotspot.</p>
                        <p class="font-medium">Enter it on the WiFi login page.</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
