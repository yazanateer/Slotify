<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3'

import { useI18n } from 'vue-i18n';

const { t } = useI18n();

</script>

<template>
<Head :title="t('admin.dashboard.title')" />
    <AdminLayout>
        <template #header>
            <h2 class="fw-semibold fs-4 text-dark">
                {{ t('admin.dashboard.header') }}
            </h2>
        </template>

        <div class="container py-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="h5 mb-2">{{ t('admin.dashboard.welcome') }}</h3>
                    <p class="text-muted mb-0">
                        {{ t('admin.dashboard.description') }}
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>