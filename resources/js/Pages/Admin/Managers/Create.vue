<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

type Business = {
    id: number;
    name: string;
};

defineProps<{
    businesses: Business[];
}>();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    business_id: '',
});

const submit = () => {
    form.post(route('admin.managers.store'));
};
</script>

<template>
    <Head title="Create Manager" />

    <AdminLayout>
        <template #title>
            Create Manager
        </template>

        <div class="admin-card">
            <form @submit.prevent="submit">
                <div class="admin-form-group">
                    <label class="admin-label">Manager Name</label>
                    <input v-model="form.name" type="text" class="admin-input" />
                    <div v-if="form.errors.name" class="text-danger small mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Email</label>
                    <input v-model="form.email" type="email" class="admin-input" />
                    <div v-if="form.errors.email" class="text-danger small mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Phone</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        class="admin-input"
                    />

                    <div v-if="form.errors.phone" class="text-danger small mt-1">
                        {{ form.errors.phone }}
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Password</label>
                    <input v-model="form.password" type="password" class="admin-input" />
                    <div v-if="form.errors.password" class="text-danger small mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Business</label>
                    <select v-model="form.business_id" class="admin-input">
                        <option value="">Select business</option>
                        <option
                            v-for="business in businesses"
                            :key="business.id"
                            :value="business.id"
                        >
                            {{ business.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.business_id" class="text-danger small mt-1">
                        {{ form.errors.business_id }}
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="admin-primary-btn" :disabled="form.processing">
                        Create Manager
                    </button>

                    <Link :href="route('admin.managers.index')" class="btn btn-light">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>