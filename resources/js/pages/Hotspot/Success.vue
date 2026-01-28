<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CheckCircle } from 'lucide-vue-next';
</script>

<template>
    <Head title="Connected Successfully" />

    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <Card class="w-full max-w-md text-center">
            <CardHeader>
                <div class="flex justify-center mb-4">
                    <CheckCircle class="h-16 w-16 text-green-500" />
                </div>
                <CardTitle class="text-2xl">Connected Successfully!</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-muted-foreground">
                    You are now connected to the WiFi hotspot.
                </p>
                <p class="text-sm text-muted-foreground">
                    Enjoy your internet access!
                </p>
                <Button @click="window.close()" variant="outline" class="w-full">
                    Close Window
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
