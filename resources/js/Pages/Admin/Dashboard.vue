<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <template #header>
            <h2 class="fw-semibold fs-4 text-dark">
                Slotify Admin Dashboard
            </h2>
        </template>

        <div class="container py-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="h5 mb-2">Welcome, SaaS Admin</h3>
                    <p class="text-muted mb-0">
                        From here you will manage businesses, manager, subscriptions, and platform settings.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>