<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router} from '@inertiajs/vue3';


type Business = {
    id: number;
    name: string;
};

type Manager = {
    id: number;
    name: string;
    email: string;
    business?: Business | null;
}

defineProps<{
    managers: Manager[];
}>();


const deleteManager = (id: number) => {
    if (confirm('Are you sure you want to delete this manager?')) {
        router.delete(route('admin.managers.destroy', id));
    }
}


</script>

<template>
    <Head title="Managers" />
    <AdminLayout>
        <template #title>
            Managers
        </template>

       <div class="mb-4">
        <div>
            <h3 class="fw-bold mb-1">Manager Accounts</h3>
            <p class="text-muted mb-0">
                Create and manage business managers across Slotify.
            </p>
        </div>

        <div class="mt-3">
            <Link
                :href="route('admin.managers.create')"
                class="admin-primary-btn"
            >
                <i class="bi bi-person-plus me-2"></i>
                Create Manager
            </Link>
        </div>
    </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Business</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="manager in managers" :key="manager.id">
                        <td>
                            <strong>{{ manager.name }}</strong>
                        </td>

                        <td>{{ manager.email }}</td>

                        <td>{{ manager.business?.name || '-' }}</td>

                        <td class="text-end">
                            <Link
                                :href="route('admin.managers.edit', manager.id)"
                                class="btn btn-sm btn-outline-primary me-2"
                            >
                                Edit
                            </Link>

                            <button
                                class="btn btn-sm btn-outline-danger"
                                @click="deleteManager(manager.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="managers.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">
                            No managers created yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>