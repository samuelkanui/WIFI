<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

defineProps<{
    sessions: {
        data: Array<any>;
        links: Array<any>;
    };
    filters: any;
}>();

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date: string) => new Date(date).toLocaleString();
</script>

<template>
    <Head title="Sessions Report" />

    <AppLayout :breadcrumbs="[{ title: 'Reports', href: '#' }, { title: 'Sessions', href: '' }]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">User Sessions</h2>
                <p class="text-muted-foreground">Monitor hotspot usage activity.</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Session History</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Started</TableHead>
                                <TableHead>Voucher</TableHead>
                                <TableHead>MAC Address</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Data In</TableHead>
                                <TableHead>Data Out</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="session in sessions.data" :key="session.id">
                                <TableCell>{{ formatDate(session.started_at) }}</TableCell>
                                <TableCell class="font-mono">{{ session.voucher_code }}</TableCell>
                                <TableCell class="font-mono text-sm">{{ session.mac_address }}</TableCell>
                                <TableCell class="font-mono text-sm">{{ session.ip_address }}</TableCell>
                                <TableCell>{{ formatBytes(session.bytes_in) }}</TableCell>
                                <TableCell>{{ formatBytes(session.bytes_out) }}</TableCell>
                            </TableRow>
                            <TableRow v-if="sessions.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No sessions recorded yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
