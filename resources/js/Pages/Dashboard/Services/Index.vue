<script setup lang="ts">
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head, Link, router} from '@inertiajs/vue3'

type Service = {
    id: number;
    name: string;
    description?: string | null;
    duration_minutes: number;
    price?: number | null;
    color?: string | null;
    is_active: boolean;
};

defineProps<{
    services: Service[];
}>();

const deleteService = (id: number) => {
    if (confirm('Are you sure you want to delete this service?')) {
        router.delete(route('dashboard.services.destroy', id));
    }
}
</script>

<template>
    <Head title="Services" />
    <ManagerLayout>
        <template #title>
            Services
        </template>
<!-- 
        <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold mb-1">Business Services</h3>

            <p class="text-muted mb-0">
                Manage services customers can book online.
            </p>
        </div>

        <Link
            :href="route('dashboard.services.create')"
            class="admin-primary-btn"
            style="margin-top: 28px;"
        >
            <i class="bi bi-plus-lg me-2"></i>
            Create Service
        </Link>
    </div> -->

    <div class="mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="fw-bold mb-1">Business Services</h3>

            <p class="text-muted mb-0">
                Manage services customers can book online.
            </p>
        </div>
    </div>

    <div class="mt-3">
        <Link
            :href="route('dashboard.services.create')"
            class="admin-primary-btn"
        >
            <i class="bi bi-plus-lg me-2"></i>
            Create Service
        </Link>
    </div>
</div>


        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="service in services" :key="service.id">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div
                                    style="width:16px;height:16px;border-radius:50%;"
                                    :style="{ background: service.color || '#2563ff' }"
                                ></div>

                                <div>
                                    <strong>{{ service.name }}</strong>

                                    <div class="text-muted small">
                                        {{ service.description || '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ service.duration_minutes }} min
                        </td>

                        <td>
                            {{ service.price ? '$' + service.price : '-' }}
                        </td>

                        <td>
                            <span
                                class="admin-badge"
                                :class="service.is_active ? 'admin-badge-success' : 'admin-badge-inactive'"
                            >
                                {{ service.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td class="text-end">
                            <Link
                                :href="route('dashboard.services.edit', service.id)"
                                class="btn btn-sm btn-outline-primary me-2"
                            >
                                Edit
                            </Link>

                            <button
                                class="btn btn-sm btn-outline-danger"
                                @click="deleteService(service.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="services.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">
                            No services created yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>


    </ManagerLayout>
</template>