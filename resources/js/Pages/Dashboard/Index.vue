<script setup lang="ts">
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head } from '@inertiajs/vue3';

type Business = {
    id: number;
    name: string;
    slug: string;
    logo?: string | null;
};

defineProps<{
    business: Business;
    bookingLink: string;
}>();
</script>

<template>
    <Head title="Business Dashboard" />

    <ManagerLayout>
        <template #title>
            {{ business.name }}
        </template>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="admin-card">
                    <p class="text-muted mb-2">Today's Appointments</p>
                    <h2 class="fw-bold mb-0">0</h2>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="admin-card">
                    <p class="text-muted mb-2">Customers</p>
                    <h2 class="fw-bold mb-0">0</h2>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="admin-card">
                    <p class="text-muted mb-2">Services</p>
                    <h2 class="fw-bold mb-0">0</h2>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="admin-card">
                    <p class="text-muted mb-2">Business Status</p>

                    <div class="d-flex align-items-center gap-2">
                        <span class="admin-status-dot"></span>
                        <strong>Active</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Public Booking Link</h4>

                    <p class="text-muted mb-0">
                        Share this link with your customers so they can book appointments.
                    </p>
                </div>

                <button
                    class="admin-primary-btn"
                    @click="navigator.clipboard.writeText(bookingLink)"
                >
                    <i class="bi bi-copy me-2"></i>
                    Copy Link
                </button>
            </div>

            <div
                class="p-3 rounded-4"
                style="background: #f3f6fb; border: 1px solid #e5ecf6;"
            >
                <strong>{{ bookingLink }}</strong>
            </div>
        </div>

        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Quick Actions</h4>

                    <p class="text-muted mb-0">
                        Start configuring your business.
                    </p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div
                        class="p-4 rounded-4 h-100"
                        style="border: 1px solid #e5ecf6;"
                    >
                        <div class="mb-3">
                            <i class="bi bi-briefcase fs-3 text-primary"></i>
                        </div>

                        <h5 class="fw-bold">Create Services</h5>

                        <p class="text-muted mb-0">
                            Add services customers can book online.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div
                        class="p-4 rounded-4 h-100"
                        style="border: 1px solid #e5ecf6;"
                    >
                        <div class="mb-3">
                            <i class="bi bi-clock fs-3 text-primary"></i>
                        </div>

                        <h5 class="fw-bold">Set Availability</h5>

                        <p class="text-muted mb-0">
                            Configure working hours and appointment slots.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div
                        class="p-4 rounded-4 h-100"
                        style="border: 1px solid #e5ecf6;"
                    >
                        <div class="mb-3">
                            <i class="bi bi-whatsapp fs-3 text-primary"></i>
                        </div>

                        <h5 class="fw-bold">WhatsApp Automation</h5>

                        <p class="text-muted mb-0">
                            Automate confirmations and reminders.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>