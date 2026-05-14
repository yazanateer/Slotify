<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import '@/../../resources/css/Pages/booking.css';
import BookingDetailsModal from '@/../../resources/js/Components/Booking/BookingDetailsModal.vue';
import { useI18n } from 'vue-i18n';
const {t} = useI18n();


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

const availablePage = ref(0);
const availableDaysPerPage = 7;
const availableSearchRange = 30; // next 30 days

const allAvailableDays = computed(() => {
    const days = [];

    for (let i = 0; i < availableSearchRange; i++) {
        const date = new Date();
        date.setDate(date.getDate() + i);

        const dayOfWeek = date.getDay();

        if (!props.availabilityDays.includes(dayOfWeek)) {
            continue;
        }

        days.push({
            date: formatDate(date),
            dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
            dayNumber: date.getDate(),
            month: date.toLocaleDateString('en-US', { month: 'short' }),
        });
    }

    return days;
});

const visibleAvailableDays = computed(() => {
    const start = availablePage.value * availableDaysPerPage;
    return allAvailableDays.value.slice(start, start + availableDaysPerPage);
});

const canGoPrevDays = computed(() => availablePage.value > 0);

const canGoNextDays = computed(() => {
    return (availablePage.value + 1) * availableDaysPerPage < allAvailableDays.value.length;
});

const nextAvailableDaysPage = () => {
    if (canGoNextDays.value) {
        availablePage.value++;
    }
};

const prevAvailableDaysPage = () => {
    if (canGoPrevDays.value) {
        availablePage.value--;
    }
};

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

                <div class="booking-date-carousel">
                    <button
                        type="button"
                        class="booking-date-arrow"
                        :disabled="!canGoPrevDays"
                        @click="prevAvailableDaysPage"
                    >
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <div class="booking-date-grid">
                        <button
                            v-for="day in visibleAvailableDays"
                            :key="day.date"
                            type="button"
                            class="booking-date-option"
                            :class="{
                                'booking-date-option--selected': selectedDate === day.date,
                                'booking-date-option--closed': !selectedService,
                            }"
                            :disabled="!selectedService"
                            @click="selectedDate = day.date"
                        >
                            <span>{{ day.dayName }}</span>
                            <strong>{{ day.dayNumber }}</strong>
                            <small>{{ day.month }}</small>
                            <em>Available</em>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="booking-date-arrow"
                        :disabled="!canGoNextDays"
                        @click="nextAvailableDaysPage"
                    >
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <p v-if="!selectedService" class="booking-helper">
                    Select a service first to unlock date selection.
                </p>

                <p v-if="selectedService && allAvailableDays.length === 0" class="booking-helper">
                    No available booking days found.
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

            <BookingDetailsModal
                v-if="currentStep === 'details'"
                v-model:customer-name="customerName"
                v-model:customer-phone="customerPhone"
                v-model:customer-email="customerEmail"
                :booking-success="bookingSuccess"
                :booking-error="bookingError"
                :confirming="confirming"
                :selected-service="selectedService"
                :selected-date="selectedDate"
                :selected-slot="selectedSlot"
                @close="currentStep = 'booking'"
                @confirm="confirmAppointment"
            />
        </div>
    </div>
</template>
