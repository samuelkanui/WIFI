<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

defineProps<{
    payments: {
        data: Array<any>;
        links: Array<any>;
    };
    totalRevenue: number;
    filters: any;
}>();

const formatDate = (date: string) => new Date(date).toLocaleString();
</script>

<template>
    <Head title="Payments Report" />

    <AppLayout :breadcrumbs="[{ title: 'Reports', href: '#' }, { title: 'Payments', href: '' }]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Payments Report</h2>
                    <p class="text-muted-foreground">View all transaction history.</p>
                </div>
                <Card class="w-48">
                    <CardContent class="p-4 pt-4">
                        <div class="text-sm font-medium text-muted-foreground">Total Revenue</div>
                        <div class="text-2xl font-bold">KES {{ totalRevenue }}</div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Transactions</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Tariff</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Receipt</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments.data" :key="payment.id">
                                <TableCell>{{ formatDate(payment.created_at) }}</TableCell>
                                <TableCell>{{ payment.phone }}</TableCell>
                                <TableCell>{{ payment.tariff?.name }}</TableCell>
                                <TableCell>KES {{ payment.amount }}</TableCell>
                                <TableCell class="font-mono text-sm">{{ payment.mpesa_receipt || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="payment.status === 'paid' ? 'default' : (payment.status === 'pending' ? 'outline' : 'destructive')">
                                        {{ payment.status }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="payments.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No payments found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
