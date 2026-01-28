<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Wifi, Download, Upload, Clock, Database } from 'lucide-vue-next';

defineProps<{
    tariffs: Array<{
        id: number;
        name: string;
        price: number;
        duration_minutes: number | null;
        data_limit_bytes: number | null;
        download_speed_kbps: number;
        upload_speed_kbps: number;
    }>;
}>();

const formatBytes = (bytes: number | null) => {
    if (!bytes) return 'Unlimited Data';
    const gb = bytes / (1024 * 1024 * 1024);
    return gb >= 1 ? `${gb.toFixed(1)} GB Data` : `${(bytes / (1024 * 1024)).toFixed(0)} MB Data`;
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return 'Unlimited Time';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours} Hours` : `${mins} Minutes`;
};

const formatSpeed = (kbps: number) => {
    const mbps = kbps / 1024;
    return mbps >= 1 ? `${mbps.toFixed(1)} Mbps` : `${kbps} Kbps`;
};
</script>

<template>
    <Head title="Buy WiFi Package" />

    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="border-b bg-card">
            <div class="container mx-auto flex h-16 items-center justify-between px-4">
                <div class="flex items-center gap-2 font-bold text-xl">
                    <Wifi class="h-6 w-6 text-primary" />
                    WiFi Hotspot
                </div>
                <Button variant="outline" as-child>
                    <a href="/hotspot/login">Have a voucher?</a>
                </Button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold tracking-tight mb-2">Choose Your Plan</h1>
                <p class="text-muted-foreground">High-speed internet packages tailored for you.</p>
            </div>

            <!-- Tariffs Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="tariff in tariffs" :key="tariff.id" class="flex flex-col relative overflow-hidden transition-all hover:border-primary/50 hover:shadow-md">
                    <div class="absolute top-0 right-0 p-3">
                        <Badge variant="secondary" class="font-mono">KES {{ tariff.price }}</Badge>
                    </div>
                    
                    <CardHeader>
                        <CardTitle class="text-xl">{{ tariff.name }}</CardTitle>
                        <CardDescription>Instant Access</CardDescription>
                    </CardHeader>
                    
                    <CardContent class="flex-1 space-y-4">
                        <div class="flex items-center gap-3 text-sm">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                            <span>{{ formatDuration(tariff.duration_minutes) }}</span>
                        </div>
                        
                        <div class="flex items-center gap-3 text-sm">
                            <Database class="h-4 w-4 text-muted-foreground" />
                            <span>{{ formatBytes(tariff.data_limit_bytes) }}</span>
                        </div>
                        
                        <div class="flex items-center gap-3 text-sm">
                            <Download class="h-4 w-4 text-muted-foreground" />
                            <span>{{ formatSpeed(tariff.download_speed_kbps) }} Download</span>
                        </div>
                        
                        <div class="flex items-center gap-3 text-sm">
                            <Upload class="h-4 w-4 text-muted-foreground" />
                            <span>{{ formatSpeed(tariff.upload_speed_kbps) }} Upload</span>
                        </div>
                    </CardContent>
                    
                    <CardFooter>
                        <Button class="w-full" size="lg" as-child>
                            <Link :href="route('payment.show', tariff.id)">
                                Buy Now
                            </Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="tariffs.length === 0" class="text-center py-12">
                <div class="bg-muted rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-4">
                    <Wifi class="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-medium">No packages available</h3>
                <p class="text-muted-foreground mt-1">Please check back later.</p>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="border-t py-6 mt-auto">
            <div class="container mx-auto px-4 text-center text-sm text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} WiFi Hotspot. All rights reserved.
            </div>
        </footer>
    </div>
</template>
