<script setup lang="ts">
import BookingSuccessContent from './BookingSuccessContent.vue';

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
    bookingSuccess: boolean;
    bookingError: string;
    confirming: boolean;
    selectedService: Service | null;
    selectedDate: string;
    selectedSlot: Slot | null;
    customerName: string;
    customerPhone: string;
    customerEmail: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
    (e: 'update:customerName', value: string): void;
    (e: 'update:customerPhone', value: string): void;
    (e: 'update:customerEmail', value: string): void;
}>();
</script>

<template>
    <div class="booking-modal-backdrop">
        <div class="booking-modal">
            <button
                v-if="!bookingSuccess"
                type="button"
                class="booking-modal-close"
                @click="emit('close')"
            >
                <i class="bi bi-x-lg"></i>
            </button>

            <div v-if="!bookingSuccess">
                <div class="booking-card-header">
                    <div>
                        <h2>Your Details</h2>
                        <p>Enter your information to confirm the appointment.</p>
                    </div>

                    <span class="booking-step">Final Step</span>
                </div>

                <div class="booking-form-grid">
                    <div>
                        <label class="booking-label">Full Name</label>
                        <input
                            :value="customerName"
                            type="text"
                            class="booking-input"
                            placeholder="Enter your name"
                            @input="emit('update:customerName', ($event.target as HTMLInputElement).value)"
                        />
                    </div>

                    <div>
                        <label class="booking-label">Phone Number</label>
                        <input
                            :value="customerPhone"
                            type="tel"
                            class="booking-input"
                            placeholder="05X-XXXXXXX"
                            @input="emit('update:customerPhone', ($event.target as HTMLInputElement).value)"
                        />
                    </div>

                    <div>
                        <label class="booking-label">Email Optional</label>
                        <input
                            :value="customerEmail"
                            type="email"
                            class="booking-input"
                            placeholder="you@example.com"
                            @input="emit('update:customerEmail', ($event.target as HTMLInputElement).value)"
                        />
                    </div>
                </div>

                <div class="booking-summary-box">
                    <h3>Appointment Summary</h3>
                    <p><strong>Service:</strong> {{ selectedService?.name }}</p>
                    <p><strong>Date:</strong> {{ selectedDate }}</p>
                    <p><strong>Time:</strong> {{ selectedSlot?.label }}</p>
                </div>

                <p v-if="bookingError" class="text-danger fw-semibold mt-3 mb-0">
                    {{ bookingError }}
                </p>

                <div class="booking-actions">
                    <button
                        type="button"
                        class="booking-secondary-btn"
                        @click="emit('close')"
                    >
                        Back
                    </button>

                    <button
                        type="button"
                        class="booking-primary-btn"
                        :disabled="!customerName || !customerPhone || confirming"
                        @click="emit('confirm')"
                    >
                        {{ confirming ? 'Confirming...' : 'Confirm Appointment' }}
                        <i class="bi bi-check2-circle"></i>
                    </button>
                </div>
            </div>

            <BookingSuccessContent
                v-else
                :selected-service="selectedService"
                :selected-date="selectedDate"
                :selected-slot="selectedSlot"
                :customer-name="customerName"
                :customer-phone="customerPhone"
            />
        </div>
    </div>
</template>