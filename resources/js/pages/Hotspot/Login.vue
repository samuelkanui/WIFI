<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    link?: string;
    mac?: string;
}>();

const form = useForm({
    code: '',
    link: props.link || '',
    mac: props.mac || '',
});

const submit = () => {
    form.post('/hotspot/login', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="WiFi Hotspot Login" />

    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <Card class="w-full max-w-md">
            <CardHeader class="text-center">
                <CardTitle class="text-2xl">WiFi Hotspot Login</CardTitle>
                <CardDescription>
                    Enter your voucher code to connect
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="code">Voucher Code</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            type="text"
                            placeholder="ABCD123456"
                            class="uppercase font-mono text-lg tracking-wider"
                            :class="{ 'border-destructive': form.errors.code }"
                            required
                            autofocus
                            maxlength="10"
                        />
                        <InputError :message="form.errors.code" />
                    </div>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full"
                        size="lg"
                    >
                        {{ form.processing ? 'Connecting...' : 'Connect to WiFi' }}
                    </Button>

                    <div class="text-center text-sm text-muted-foreground space-y-1">
                        <p>Don't have a voucher?</p>
                        <a href="/" class="text-primary hover:underline font-medium">
                            Purchase one here
                        </a>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
