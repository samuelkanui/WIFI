<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({
    name: '',
    price: '',
    duration_minutes: '',
    data_limit_bytes: '', // We'll input in MB and convert
    download_speed_kbps: '2048', // Default 2Mbps
    upload_speed_kbps: '1024',   // Default 1Mbps
});

const submit = () => {
    // Convert MB input to bytes if present
    if (form.data_limit_bytes) {
        form.data_limit_bytes = (form.data_limit_bytes * 1024 * 1024).toString();
    }
    
    // Duration is in minutes, user might input hours, but let's stick to minutes for simplicity for now
    // or add a switcher. Standardizing on minutes as per model.

    form.post(route('tariffs.store'));
};
</script>

<template>
    <Head title="Create Tariff" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Tariffs', href: route('tariffs.index') },
            { title: 'Create', href: route('tariffs.create') },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-4 p-4 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('tariffs.index')">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Create Tariff</h2>
                    <p class="text-muted-foreground">Add a new internet package.</p>
                </div>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Tariff Details</CardTitle>
                        <CardDescription>
                            Define the pricing and limits for this hotspot package.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g., 1 Hour Unlimited"
                                    required
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Price (KES)</Label>
                                <Input
                                    id="price"
                                    type="number"
                                    v-model="form.price"
                                    placeholder="50"
                                    min="0"
                                    required
                                />
                                <InputError :message="form.errors.price" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="duration">Duration (Minutes)</Label>
                            <Input
                                id="duration"
                                type="number"
                                v-model="form.duration_minutes"
                                placeholder="60"
                                min="1"
                            />
                            <p class="text-xs text-muted-foreground">
                                Leave empty for unlimited time.
                            </p>
                            <InputError :message="form.errors.duration_minutes" />
                        </div>

                        <div class="space-y-2">
                            <Label for="data_limit">Data Limit (MB)</Label>
                            <Input
                                id="data_limit"
                                type="number"
                                v-model="form.data_limit_bytes"
                                placeholder="1024"
                                min="1"
                            />
                            <p class="text-xs text-muted-foreground">
                                Enter limit in Megabytes (MB). Leave empty for unlimited data.
                            </p>
                            <InputError :message="form.errors.data_limit_bytes" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="download_speed">Download Speed (Kbps)</Label>
                                <Input
                                    id="download_speed"
                                    type="number"
                                    v-model="form.download_speed_kbps"
                                    placeholder="2048"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">1 Mbps = 1024 Kbps</p>
                                <InputError :message="form.errors.download_speed_kbps" />
                            </div>

                            <div class="space-y-2">
                                <Label for="upload_speed">Upload Speed (Kbps)</Label>
                                <Input
                                    id="upload_speed"
                                    type="number"
                                    v-model="form.upload_speed_kbps"
                                    placeholder="1024"
                                    required
                                />
                                <InputError :message="form.errors.upload_speed_kbps" />
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="ghost" as-child>
                            <Link :href="route('tariffs.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Create Tariff
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
