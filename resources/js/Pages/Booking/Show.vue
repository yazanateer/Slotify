<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

type Business = {
    id: number;
    name: string;
    slug: string;
    logo?: string | null;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
};

type Service = {
    id: number;
    name: string;
    description?: string | null;
    duration_minutes: number;
    price?: number | null;
    color?: string | null;
};

type Slot = {
    start_time: string;
    end_time: string;
    label: string;
};

const props = defineProps<{
    business: Business;
    services: Service[];
    availabilityDays: number[];
}>();

const currentStep = ref<'booking' | 'details'>('booking');

const customerName = ref('');
const customerPhone = ref('');
const customerEmail = ref('');

const selectedServiceId = ref<number | null>(null);
const selectedDate = ref<string>('');
const slots = ref<Slot[]>([]);
const selectedSlot = ref<Slot | null>(null);
const loadingSlots = ref(false);

const bookingSuccess = ref(false);
const bookingError = ref('');
const confirming = ref(false);

const selectedService = computed(() => {
    return props.services.find((service) => service.id === selectedServiceId.value) ?? null;
});

const today = new Date().toISOString().split('T')[0];

const canLoadSlots = computed(() => {
    return selectedServiceId.value !== null && selectedDate.value !== '';
});

const selectService = (service: Service) => {
    selectedServiceId.value = service.id;
    selectedSlot.value = null;
    currentStep.value = 'booking';
};

const selectSlot = (slot: Slot) => {
    selectedSlot.value = slot;
};

const loadSlots = async () => {
    if (!canLoadSlots.value) {
        slots.value = [];
        return;
    }

    loadingSlots.value = true;
    selectedSlot.value = null;

    try {
        const response = await axios.get(route('booking.slots', props.business.slug), {
            params: {
                service_id: selectedServiceId.value,
                date: selectedDate.value,
            },
        });

        slots.value = response.data.slots;
    } catch (error) {
        console.error(error);
        slots.value = [];
    } finally {
        loadingSlots.value = false;
    }
};

watch([selectedServiceId, selectedDate], () => {
    loadSlots();
});

const goToDetails = () => {
    if (!selectedService.value || !selectedDate.value || !selectedSlot.value) return;

    currentStep.value = 'details';
};

const confirmAppointment = async () => {
    if (!selectedService.value || !selectedDate.value || !selectedSlot.value) return;

    bookingError.value = '';
    confirming.value = true;

    try {
        await axios.post(route('booking.appointments.store', props.business.slug), {
            service_id: selectedService.value.id,
            appointment_date: selectedDate.value,
            start_time: selectedSlot.value.start_time,
            end_time: selectedSlot.value.end_time,
            customer_name: customerName.value,
            customer_phone: customerPhone.value,
            customer_email: customerEmail.value || null,
        });

        bookingSuccess.value = true;
    } catch (error: any) {
        bookingError.value =
            error.response?.data?.message || 'Something went wrong. Please try again.';
    } finally {
        confirming.value = false;
    }
};

const formatDate = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const nextSevenDays = computed(() => {
    const days = [];

    for (let i = 0; i < 7; i++) {
        const date = new Date();
        date.setDate(date.getDate() + i);

        const dayOfWeek = date.getDay();
        const isOpen = props.availabilityDays.includes(dayOfWeek);

        days.push({
            date: formatDate(date),
            dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
            dayNumber: date.getDate(),
            month: date.toLocaleDateString('en-US', { month: 'short' }),
            isOpen,
        });
    }

    return days;
});

</script>

<template>
    <Head :title="`Book with ${business.name}`" />

    <div class="booking-page">
        <div class="booking-shell">
            <div class="booking-hero">
                <div class="booking-brand">
                    <div class="booking-logo">
                        <img
                            v-if="business.logo"
                            :src="business.logo"
                            :alt="business.name"
                        />
                        <span v-else>{{ business.name.charAt(0) }}</span>
                    </div>

                    <div>
                        <p class="booking-eyebrow">Online Booking</p>
                        <h1>{{ business.name }}</h1>

                        <p class="booking-subtitle">
                            Choose a service, select a date, and pick an available time.
                        </p>
                    </div>
                </div>

                <div class="booking-info">
                    <span v-if="business.address">
                        <i class="bi bi-geo-alt"></i>
                        {{ business.address }}
                    </span>

                    <span v-if="business.phone">
                        <i class="bi bi-telephone"></i>
                        {{ business.phone }}
                    </span>
                </div>
            </div>

            <div v-if="!bookingSuccess" class="booking-card">
                <div class="booking-card-header">
                    <div>
                        <h2>Select a Service</h2>
                        <p>Pick the service you want to book.</p>
                    </div>

                    <span class="booking-step">Step 1 of 3</span>
                </div>

                <div v-if="services.length > 0" class="booking-services">
                    <button
                        v-for="service in services"
                        :key="service.id"
                        type="button"
                        class="booking-service"
                        :class="{ 'booking-service--selected': selectedServiceId === service.id }"
                        @click="selectService(service)"
                    >
                        <div class="booking-service-left">
                            <div
                                class="booking-service-dot"
                                :style="{ background: service.color || '#2563ff' }"
                            ></div>

                            <div>
                                <h3>{{ service.name }}</h3>
                                <p>{{ service.description || 'No description provided.' }}</p>
                            </div>
                        </div>

                        <div class="booking-service-meta">
                            <span>
                                <i class="bi bi-clock"></i>
                                {{ service.duration_minutes }} min
                            </span>

                            <strong v-if="service.price">
                                ₪{{ service.price }}
                            </strong>
                        </div>
                    </button>
                </div>

                <div v-else class="booking-empty">
                    <i class="bi bi-calendar-x"></i>
                    <h3>No services available</h3>
                    <p>This business has not published bookable services yet.</p>
                </div>
            </div>

            <div v-if="!bookingSuccess" class="booking-card">
                <div class="booking-card-header">
                    <div>
                        <h2>Choose Date</h2>
                        <p>Select the day you want to visit.</p>
                    </div>

                    <span class="booking-step">Step 2 of 3</span>
                </div>

            <div class="booking-date-grid">
                <button
                    v-for="day in nextSevenDays"
                    :key="day.date"
                    type="button"
                    class="booking-date-option"
                    :class="{
                        'booking-date-option--selected': selectedDate === day.date,
                        'booking-date-option--closed': !day.isOpen || !selectedService,
                    }"
                    :disabled="!day.isOpen || !selectedService"
                    @click="selectedDate = day.date"
                >
                    <span>{{ day.dayName }}</span>
                    <strong>{{ day.dayNumber }}</strong>
                    <small>{{ day.month }}</small>

                    <em>
                        {{ day.isOpen ? 'Open' : 'Closed' }}
                    </em>
                </button>
            </div>

                <p v-if="!selectedService" class="booking-helper">
                    Select a service first to unlock date selection.
                </p>
            </div>

            <div v-if="!bookingSuccess" class="booking-card">
                <div class="booking-card-header">
                    <div>
                        <h2>Available Times</h2>
                        <p>Pick one of the available appointment slots.</p>
                    </div>

                    <span class="booking-step">Step 3 of 3</span>
                </div>

                <div v-if="loadingSlots" class="booking-loading">
                    <i class="bi bi-arrow-repeat"></i>
                    Loading available slots...
                </div>

                <div v-else-if="!canLoadSlots" class="booking-empty small-empty">
                    <i class="bi bi-clock"></i>
                    <h3>Select service and date</h3>
                    <p>Available times will appear here.</p>
                </div>

                <div v-else-if="slots.length > 0" class="booking-slots">
                    <button
                        v-for="slot in slots"
                        :key="`${slot.start_time}-${slot.end_time}`"
                        type="button"
                        class="booking-slot"
                        :class="{ 'booking-slot--selected': selectedSlot?.start_time === slot.start_time }"
                        @click="selectSlot(slot)"
                    >
                        <i class="bi bi-clock"></i>
                        {{ slot.label }}
                    </button>
                </div>

                <div v-else class="booking-empty small-empty">
                    <i class="bi bi-calendar-x"></i>
                    <h3>No slots available</h3>
                    <p>Try another date or service.</p>
                </div>

                <div class="booking-actions">
                    <div>
                        <p v-if="selectedService" class="booking-selected">
                            Service:
                            <strong>{{ selectedService.name }}</strong>
                        </p>

                        <p v-if="selectedDate" class="booking-selected">
                            Date:
                            <strong>{{ selectedDate }}</strong>
                        </p>

                        <p v-if="selectedSlot" class="booking-selected">
                            Time:
                            <strong>{{ selectedSlot.label }}</strong>
                        </p>

                        <p v-if="!selectedSlot" class="booking-selected text-muted">
                            Select a time slot to continue.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="booking-primary-btn"
                        :disabled="!selectedService || !selectedDate || !selectedSlot"
                        @click="goToDetails"
                    >
                        Continue
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div v-if="currentStep === 'details'" class="booking-modal-backdrop">
                <div class="booking-modal">
                    <button
                        v-if="!bookingSuccess"
                        type="button"
                        class="booking-modal-close"
                        @click="currentStep = 'booking'"
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
                                    v-model="customerName"
                                    type="text"
                                    class="booking-input"
                                    placeholder="Enter your name"
                                />
                            </div>

                            <div>
                                <label class="booking-label">Phone Number</label>
                                <input
                                    v-model="customerPhone"
                                    type="tel"
                                    class="booking-input"
                                    placeholder="05X-XXXXXXX"
                                />
                            </div>

                            <div>
                                <label class="booking-label">Email Optional</label>
                                <input
                                    v-model="customerEmail"
                                    type="email"
                                    class="booking-input"
                                    placeholder="you@example.com"
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
                                @click="currentStep = 'booking'"
                            >
                                Back
                            </button>

                            <button
                                type="button"
                                class="booking-primary-btn"
                                :disabled="!customerName || !customerPhone || confirming"
                                @click="confirmAppointment"
                            >
                                {{ confirming ? 'Confirming...' : 'Confirm Appointment' }}
                                <i class="bi bi-check2-circle"></i>
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center">
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
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.booking-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at top right, rgba(37, 99, 255, 0.14), transparent 32%),
        linear-gradient(135deg, #f8fbff 0%, #f4f7fb 100%);
    padding: 40px 16px;
    color: #071533;
}

.booking-shell {
    max-width: 920px;
    margin: 0 auto;
}

.booking-hero,
.booking-card {
    background: #ffffff;
    border: 1px solid #e5ecf6;
    border-radius: 30px;
    padding: 32px;
    box-shadow: 0 18px 45px rgba(7, 21, 51, 0.08);
    margin-bottom: 24px;
}

.booking-brand {
    display: flex;
    align-items: center;
    gap: 18px;
}

.booking-logo {
    width: 72px;
    height: 72px;
    border-radius: 24px;
    background: linear-gradient(135deg, #2563ff, #3b82f6);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: 900;
    box-shadow: 0 16px 34px rgba(37, 99, 255, 0.24);
    overflow: hidden;
    flex-shrink: 0;
}

.booking-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.booking-eyebrow {
    margin: 0 0 6px;
    color: #2563ff;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.booking-brand h1 {
    margin: 0;
    font-size: 34px;
    font-weight: 900;
    letter-spacing: -0.03em;
}

.booking-subtitle,
.booking-card-header p,
.booking-helper {
    color: #6b7890;
}

.booking-subtitle {
    margin: 8px 0 0;
}

.booking-info {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 24px;
    color: #6b7890;
    font-size: 14px;
}

.booking-info span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #f4f7fb;
    border: 1px solid #e5ecf6;
    border-radius: 999px;
    padding: 8px 12px;
}

.booking-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 24px;
}

.booking-card-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 900;
}

.booking-card-header p {
    margin: 6px 0 0;
}

.booking-step {
    background: rgba(37, 99, 255, 0.09);
    color: #2563ff;
    border: 1px solid rgba(37, 99, 255, 0.16);
    border-radius: 999px;
    padding: 7px 13px;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.booking-services,
.booking-slots {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.booking-service {
    width: 100%;
    border: 1.5px solid #e5ecf6;
    background: #ffffff;
    border-radius: 22px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    text-align: left;
    transition: 0.18s ease;
    cursor: pointer;
}

.booking-service:hover,
.booking-service--selected {
    border-color: #2563ff;
    background: rgba(37, 99, 255, 0.07);
}

.booking-service--selected {
    box-shadow: 0 14px 34px rgba(37, 99, 255, 0.1);
}

.booking-service-left {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.booking-service-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    margin-top: 5px;
    flex-shrink: 0;
}

.booking-service h3 {
    margin: 0;
    color: #071533;
    font-size: 18px;
    font-weight: 850;
}

.booking-service p {
    margin: 6px 0 0;
    color: #6b7890;
    font-size: 14px;
}

.booking-service-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 7px;
    color: #6b7890;
    white-space: nowrap;
}

.booking-service-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
}

.booking-date-input {
    width: 100%;
    border: 1.5px solid #e5ecf6;
    border-radius: 18px;
    padding: 16px 18px;
    font-size: 16px;
    font-weight: 700;
    color: #071533;
    background: #ffffff;
}

.booking-date-input:focus,
.booking-input:focus {
    outline: none;
    border-color: #2563ff;
    box-shadow: 0 0 0 4px rgba(37, 99, 255, 0.1);
}

.booking-date-input:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.booking-helper {
    margin: 12px 0 0;
    font-size: 14px;
}

.booking-slot {
    border: 1.5px solid #e5ecf6;
    background: #ffffff;
    color: #071533;
    border-radius: 18px;
    padding: 15px 18px;
    font-weight: 800;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: 0.18s ease;
}

.booking-slot:hover,
.booking-slot--selected {
    border-color: #2563ff;
    background: rgba(37, 99, 255, 0.07);
    color: #2563ff;
}

.booking-slot--selected {
    box-shadow: 0 14px 34px rgba(37, 99, 255, 0.1);
}

.booking-empty,
.booking-loading {
    text-align: center;
    padding: 42px 20px;
    color: #6b7890;
}

.small-empty {
    padding: 30px 20px;
}

.booking-empty i,
.booking-loading i {
    font-size: 36px;
    color: #2563ff;
    margin-bottom: 12px;
}

.booking-empty h3 {
    color: #071533;
    font-weight: 850;
}

.booking-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    border-top: 1px solid #e5ecf6;
    margin-top: 28px;
    padding-top: 24px;
}

.booking-selected {
    margin: 0 0 4px;
    color: #071533;
}

.booking-primary-btn {
    border: 0;
    border-radius: 16px;
    padding: 13px 22px;
    background: linear-gradient(135deg, #2563ff, #3b82f6);
    color: #ffffff;
    font-weight: 850;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 16px 34px rgba(37, 99, 255, 0.24);
    transition: 0.18s ease;
}

.booking-primary-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

.booking-primary-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.booking-form-grid {
    display: grid;
    gap: 18px;
}

.booking-label {
    display: block;
    margin-bottom: 8px;
    color: #071533;
    font-weight: 800;
    font-size: 14px;
}

.booking-input {
    width: 100%;
    border: 1.5px solid #e5ecf6;
    border-radius: 18px;
    padding: 16px 18px;
    font-size: 16px;
    font-weight: 600;
    color: #071533;
    background: #ffffff;
}

.booking-summary-box {
    margin-top: 24px;
    background: #f4f7fb;
    border: 1px solid #e5ecf6;
    border-radius: 22px;
    padding: 22px;
}

.booking-summary-box h3 {
    margin: 0 0 14px;
    font-size: 18px;
    font-weight: 900;
}

.booking-summary-box p {
    margin: 0 0 8px;
    color: #071533;
}

.booking-secondary-btn {
    border: 1.5px solid #e5ecf6;
    border-radius: 16px;
    padding: 13px 22px;
    background: #ffffff;
    color: #071533;
    font-weight: 850;
}

.booking-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(7, 21, 51, 0.55);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.booking-modal {
    position: relative;
    width: 100%;
    max-width: 720px;
    max-height: 90vh;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e5ecf6;
    border-radius: 30px;
    padding: 32px;
    box-shadow: 0 30px 80px rgba(7, 21, 51, 0.25);
}

.booking-modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 40px;
    height: 40px;
    border: 1px solid #e5ecf6;
    border-radius: 14px;
    background: #ffffff;
    color: #071533;
}

.booking-modal-close:hover {
    background: rgba(37, 99, 255, 0.08);
    color: #2563ff;
}

@media (max-width: 768px) {
    .booking-hero,
    .booking-card,
    .booking-modal {
        padding: 24px;
        border-radius: 24px;
    }

    .booking-brand {
        align-items: flex-start;
    }

    .booking-brand h1 {
        font-size: 26px;
    }

    .booking-card-header,
    .booking-service,
    .booking-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .booking-service-meta {
        align-items: flex-start;
    }

    .booking-primary-btn {
        justify-content: center;
        width: 100%;
    }
}

.booking-date-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 12px;
}

.booking-date-option {
    border: 1.5px solid #e5ecf6;
    background: #ffffff;
    border-radius: 18px;
    padding: 16px 10px;
    color: #071533;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    transition: 0.18s ease;
}

.booking-date-option span {
    font-size: 13px;
    color: #6b7890;
    font-weight: 800;
}

.booking-date-option strong {
    font-size: 24px;
    font-weight: 900;
}

.booking-date-option small {
    color: #6b7890;
    font-weight: 700;
}

.booking-date-option em {
    margin-top: 6px;
    font-style: normal;
    font-size: 11px;
    font-weight: 800;
    color: #16a34a;
}

.booking-date-option:hover:not(:disabled),
.booking-date-option--selected {
    border-color: #2563ff;
    background: rgba(37, 99, 255, 0.08);
    color: #2563ff;
}

.booking-date-option--selected {
    box-shadow: 0 14px 34px rgba(37, 99, 255, 0.12);
}

.booking-date-option--closed {
    opacity: 0.45;
    cursor: not-allowed;
}

.booking-date-option--closed em {
    color: #6b7890;
}

@media (max-width: 768px) {
    .booking-date-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>