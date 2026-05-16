<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

type Business = {
    id: number;
    name: string;
    slug: string;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
    timezone: string;
    is_active: boolean;
};

const props = defineProps<{
    business: Business;
}>();

const { t } = useI18n();

const form = useForm({
    name: props.business.name,
    slug: props.business.slug,
    phone: props.business.phone ?? '',
    email: props.business.email ?? '',
    address: props.business.address ?? '',
    timezone: props.business.timezone,
    is_active: props.business.is_active,
});

const submit = () => {
    form.put(route('admin.businesses.update', props.business.id));
};
</script>

<template>
    <Head :title="t('admin.businesses.editBusiness')" />

    <AdminLayout>
        <template #header>
            <h2 class="fw-semibold fs-4 text-dark mb-0">{{t('admin.businesses.editBusiness')}}</h2>
        </template>

        <div class="container py-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <label class="form-label">{{t('admin.businesses.businessName')}}</label>
                            <input v-model="form.name" type="text" class="form-control" />
                            <div v-if="form.errors.name" class="text-danger small mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ t('admin.businesses.slug') }}</label>
                            <input v-model="form.slug" type="text" class="form-control" />
                            <div v-if="form.errors.slug" class="text-danger small mt-1">
                                {{ form.errors.slug }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{t('common.phone')}}</label>
                            <input v-model="form.phone" type="text" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{t('common.email')}}</label>
                            <input v-model="form.email" type="email" class="form-control" />
                            <div v-if="form.errors.email" class="text-danger small mt-1">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{t('common.address')}}</label>
                            <input v-model="form.address" type="text" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ t('common.timezone') }}</label>
                            <input v-model="form.timezone" type="text" class="form-control" />
                        </div>

                        <div class="form-check mb-4">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                class="form-check-input"
                                type="checkbox"
                            />
                            <label for="is_active" class="form-check-label">
                                {{ t('common.active')}}
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" :disabled="form.processing">
                                {{ t('admin.businesses.updateBusiness') }}
                            </button>

                            <Link :href="route('admin.businesses.index')" class="btn btn-light">
                                {{ t('common.cancel')}}
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>