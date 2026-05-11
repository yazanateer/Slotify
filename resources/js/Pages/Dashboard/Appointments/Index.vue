<script setup lang="ts">
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head } from '@inertiajs/vue3';

type Service = {
    id: number;
    name: string;
};

type Appointment = {
    id: number;
    customer_name: string;
    customer_phone: string;
    appointment_date: string;
    start_time: string;
    end_time: string;
    status: string;
    service?: Service | null;
};

defineProps<{
    appointments: Appointment[];
}>();
</script>

<template>
    <Head title="Appointments" />

    <ManagerLayout>
        <template #title>
            Appointments
        </template>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">Appointments</h3>
            <p class="text-muted mb-0">
                View all bookings for your business.
            </p>
        </div>

        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="appointment in appointments" :key="appointment.id">
                        <td>
                            <strong>{{ appointment.customer_name }}</strong>
                            <div class="text-muted small">
                                {{ appointment.customer_phone }}
                            </div>
                        </td>

                        <td>{{ appointment.service?.name || '-' }}</td>

                        <td>{{ appointment.appointment_date }}</td>

                        <td>
                            {{ appointment.start_time }} - {{ appointment.end_time }}
                        </td>

                        <td>
                            <span class="admin-badge admin-badge-success">
                                {{ appointment.status }}
                            </span>
                        </td>
                    </tr>

                    <tr v-if="appointments.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">
                            No appointments yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </ManagerLayout>
</template>