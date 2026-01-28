<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Trash2, Edit, Plus } from 'lucide-vue-next';

defineProps<{
    tariffs: Array<{
        id: number;
        name: string;
        price: number;
        duration_minutes: number;
        data_limit_bytes: number;
        download_speed_kbps: number;
        upload_speed_kbps: number;
        vouchers_count: number;
    }>;
}>();

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

const deleteTariff = (id: number) => {
    if (confirm('Are you sure you want to delete this tariff?')) {
        router.delete(route('tariffs.destroy', id));
    }
};
</script>

<template>
    <Head title="Tariffs" />

    <AppLayout :breadcrumbs="[{ title: 'Tariffs', href: route('tariffs.index') }]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Tariffs</h2>
                    <p class="text-muted-foreground">Manage your WiFi hotspot packages.</p>
                </div>
                <Button as-child>
                    <Link :href="route('tariffs.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Tariff
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Tariffs</CardTitle>
                    <CardDescription>
                        List of all available internet packages.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Speed (DL/UL)</TableHead>
                                <TableHead>Duration</TableHead>
                                <TableHead>Data Limit</TableHead>
                                <TableHead>Vouchers</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="tariff in tariffs" :key="tariff.id">
                                <TableCell class="font-medium">{{ tariff.name }}</TableCell>
                                <TableCell>KES {{ tariff.price }}</TableCell>
                                <TableCell>
                                    {{ formatSpeed(tariff.download_speed_kbps) }} /
                                    {{ formatSpeed(tariff.upload_speed_kbps) }}
                                </TableCell>
                                <TableCell>{{ formatDuration(tariff.duration_minutes) }}</TableCell>
                                <TableCell>{{ formatBytes(tariff.data_limit_bytes) }}</TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{{ tariff.vouchers_count }}</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="route('tariffs.edit', tariff.id)">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            @click="deleteTariff(tariff.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="tariffs.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                    No tariffs found. Create one to get started.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
