<script setup lang="ts">
type Service = {
    id: number;
    name: string;
};

type Slot = {
    start_time: string;
    end_time: string;
    label: string;
};

defineProps<{
    selectedService: Service | null;
    selectedDate: string;
    selectedSlot: Slot | null;
    customerName: string;
    customerPhone: string;
}>();
</script>

<template>
    <div class="text-center">
        <i
            class="bi bi-check-circle-fill"
            style="font-size: 56px; color: #16a34a;"
        ></i>

        <h2 class="mt-3">Appointment Confirmed</h2>

        <p class="text-muted">
            Your appointment has been booked successfully.
        </p>

        <div class="booking-summary-box text-start">
            <p><strong>Service:</strong> {{ selectedService?.name }}</p>
            <p><strong>Date:</strong> {{ selectedDate }}</p>
            <p><strong>Time:</strong> {{ selectedSlot?.label }}</p>
            <p><strong>Name:</strong> {{ customerName }}</p>
            <p><strong>Phone:</strong> {{ customerPhone }}</p>
        </div>
    </div>
</template>