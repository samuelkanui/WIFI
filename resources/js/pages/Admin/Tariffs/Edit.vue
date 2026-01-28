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

const props = defineProps<{
    tariff: {
        id: number;
        name: string;
        price: number;
        duration_minutes: number;
        data_limit_bytes: number;
        download_speed_kbps: number;
        upload_speed_kbps: number;
    };
}>();

const form = useForm({
    name: props.tariff.name,
    price: props.tariff.price,
    duration_minutes: props.tariff.duration_minutes,
    data_limit_bytes: props.tariff.data_limit_bytes ? props.tariff.data_limit_bytes / (1024 * 1024) : '', // Convert back to MB
    download_speed_kbps: props.tariff.download_speed_kbps,
    upload_speed_kbps: props.tariff.upload_speed_kbps,
});

const submit = () => {
    // Transform MB to Bytes for submission if set
    let dataToSubmit = { ...form.data() };
    if (dataToSubmit.data_limit_bytes) {
        dataToSubmit.data_limit_bytes = dataToSubmit.data_limit_bytes * 1024 * 1024;
    }

    form.transform((data) => ({
        ...data,
        data_limit_bytes: data.data_limit_bytes ? data.data_limit_bytes * 1024 * 1024 : null,
    })).put(route('tariffs.update', props.tariff.id));
};
</script>

<template>
    <Head title="Edit Tariff" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Tariffs', href: route('tariffs.index') },
            { title: 'Edit', href: '' },
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
                    <h2 class="text-2xl font-bold tracking-tight">Edit Tariff</h2>
                    <p class="text-muted-foreground">Modify existing package details.</p>
                </div>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Tariff Details</CardTitle>
                        <CardDescription>
                            Updating this will update the profile on the MikroTik router.
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
                            Save Changes
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
