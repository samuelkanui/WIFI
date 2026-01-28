<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, Search, Filter } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps<{
    vouchers: {
        data: Array<{
            id: number;
            code: string;
            tariff: { name: string; price: number };
            payment_id: number | null;
            used: boolean;
            expires_at: string | null;
            created_at: string;
        }>;
        links: Array<any>;
    };
    tariffs: Array<{ id: number; name: string }>; // Passed from controller for generation
    filters: {
        status?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');

// Watch for search/filter changes
watch([search, status], debounce(() => {
    router.get(route('vouchers.index'), { 
        search: search.value, 
        status: status.value !== 'all' ? status.value : undefined 
    }, { preserveState: true, replace: true });
}, 300));

// Generation Form
const generateForm = useForm({
    tariff_id: '',
    quantity: 1,
});

const generateVouchers = () => {
    generateForm.post(route('vouchers.generate'), {
        onSuccess: () => {
            // Close dialog logic or just let toast handle success
            generateForm.reset();
        }
    });
};

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head title="Vouchers" />

    <AppLayout :breadcrumbs="[{ title: 'Vouchers', href: route('vouchers.index') }]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Vouchers</h2>
                    <p class="text-muted-foreground">Manage and generate WiFi vouchers.</p>
                </div>
                
                <Dialog>
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Generate Bulk
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Generate Vouchers</DialogTitle>
                            <DialogDescription>
                                Create multiple vouchers for a specific tariff at once.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="space-y-2">
                                <Label htmlFor="tariff">Tariff Package</Label>
                                <Select v-model="generateForm.tariff_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a tariff" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <!-- Note: We need to pass tariffs from the controller index method for this to work perfectly. 
                                             For now assuming tariffs prop exists or we'd fetch them. -->
                                        <SelectItem v-for="t in tariffs" :key="t.id" :value="t.id.toString()">
                                            {{ t.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="generateForm.errors.tariff_id" class="text-sm text-destructive">{{ generateForm.errors.tariff_id }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label htmlFor="quantity">Quantity</Label>
                                <Input id="quantity" type="number" v-model="generateForm.quantity" min="1" max="100" />
                                <p v-if="generateForm.errors.quantity" class="text-sm text-destructive">{{ generateForm.errors.quantity }}</p>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="generateVouchers" :disabled="generateForm.processing">Generate</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <CardTitle>Voucher List</CardTitle>
                        <div class="flex gap-2">
                            <div class="relative w-full md:w-64">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search code..." v-model="search" class="pl-8" />
                            </div>
                            <Select v-model="status">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Filter Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="used">Used</SelectItem>
                                    <SelectItem value="unused">Unused</SelectItem>
                                    <SelectItem value="expired">Expired</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Code</TableHead>
                                <TableHead>Tariff</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Expires</TableHead>
                                <TableHead>Source</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="voucher in vouchers.data" :key="voucher.id">
                                <TableCell class="font-mono font-medium">{{ voucher.code }}</TableCell>
                                <TableCell>{{ voucher.tariff?.name }}</TableCell>
                                <TableCell>
                                    <Badge :variant="voucher.used ? 'secondary' : 'outline'">
                                        {{ voucher.used ? 'Used' : 'Active' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(voucher.created_at) }}</TableCell>
                                <TableCell>{{ formatDate(voucher.expires_at) }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline" v-if="voucher.payment_id">M-Pesa</Badge>
                                    <span v-else class="text-muted-foreground text-sm">Admin</span>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="vouchers.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No vouchers found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    
                    <!-- Simple Pagination -->
                    <div class="mt-4 flex justify-center gap-2" v-if="vouchers.links.length > 3">
                        <Button 
                            v-for="(link, i) in vouchers.links" 
                            :key="i"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            as-child
                            class="h-8 w-8 p-0"
                        >
                            <Link :href="link.url || '#'" v-if="link.url">
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label"></span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
