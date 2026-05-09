<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router} from '@inertiajs/vue3'


type Business = {
    id: number;
    name: string;
    slug: string;
    phone: string;
    email: string;
    is_active: boolean;
}

defineProps<{
    businesses: Business[];
}>();

const deleteBusiness = (id: number) => {
    if(confirm('Are you sure you want to delete this business?')) {
        router.delete(route('admin.businesses.destroy', id))
    }
}
</script>


<template>
    <Head title="Businesses" />

    <AdminLayout>
        <template #title>
            Businesses
        </template>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Business Management</h3>
                <p class="text-muted mb-0">
                    Create and manage businesses using the Slotify platform.
                </p>
            </div>

            <Link :href="route('admin.businesses.create')" class="admin-primary-btn">
                <i class="bi bi-plus-lg me-2"></i>
                Create Business
            </Link>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Booking Link</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="business in businesses" :key="business.id">
                        <td>
                            <strong>{{ business.name }}</strong>
                        </td>

                        <td>
                            <span class="text-muted">
                                /book/{{ business.slug }}
                            </span>
                        </td>

                        <td>{{ business.phone || '-' }}</td>
                        <td>{{ business.email || '-' }}</td>

                        <td>
                            <span
                                class="admin-badge"
                                :class="business.is_active ? 'admin-badge-success' : 'admin-badge-inactive'"
                            >
                                {{ business.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td class="text-end">
                            <Link
                                :href="route('admin.businesses.edit', business.id)"
                                class="btn btn-sm btn-outline-primary me-2"
                            >
                                Edit
                            </Link>

                            <button
                                class="btn btn-sm btn-outline-danger"
                                @click="deleteBusiness(business.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="businesses.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">
                            No businesses created yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>