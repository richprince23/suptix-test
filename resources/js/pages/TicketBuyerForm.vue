<template>
    <main class="bg-background min-h-screen">
        <NavBar text-color='text-foreground' :background-color="'bg-background'" class="bg-background" />
        <div class="mx-auto max-w-7xl px-4  py-8 md:py-8 sm:px-12 lg:px-16">
            <!-- Header -->
            <div class="mb-6">
                <Button variant="outline" @click="goBack" class="rounded  flex cursor-pointer items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Back to Event
                </Button>
            </div>

            <!-- Simple Event Header -->
            <div class="bg-whites rounded-all p-4 mb-6">
                <h1 class="text-lg font-semibold text-foreground mb-1">{{ event.title }}</h1>
                <p class="text-sm text-foreground/70">
                    {{ formatDate(event.start_date) }} • {{ formatTime(event.start_time) }}
                    <span v-if="event.venue_name"> • {{ event.venue_name }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Buyer and Attendee Form -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Buyer Information -->
                    <div class="bg-whites rounded-all p-6">
                        <h2 class="text-lg font-semibold text-foreground mb-6">Buyer Information</h2>

                        <form @submit.prevent="submitPurchase" class="space-y-6">
                            <!-- Honeypot field (hidden from users but visible to bots) -->
                            <input v-model="form.honeypot" type="text" style="display: none;" tabindex="-1"
                                autocomplete="off" />

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <Label for="buyer_email">
                                        Email Address <span class="text-red-500">*</span>
                                    </Label>
                                    <Input type="email" id="buyer_email" v-model="form.buyer_email" required
                                        class="rounded pri-borders py-5 mt-1"
                                        :class="{ 'border-red-500': errors.buyer_email }"
                                        placeholder="your@email.com" />
                                    <p v-if="errors.buyer_email" class="mt-1 text-sm text-red-600">
                                        {{ errors.buyer_email }}</p>
                                </div>

                                <div>
                                    <Label for="buyer_phone">Phone Number</Label>
                                    <Input type="tel" id="buyer_phone" v-model="form.buyer_phone"
                                        class="rounded pri-borders py-5 mt-1"
                                        :class="{ 'border-red-500': errors.buyer_phone }"
                                        placeholder="+233 20 123 4567" />
                                    <p v-if="errors.buyer_phone" class="mt-1 text-sm text-red-600">
                                        {{ errors.buyer_phone }}</p>
                                </div>
                            </div>


                            <div class="flex items-center space-x-3">
                                <Checkbox id="showInfo" @change="showAttendeeInfo" />
                                <Label for="showInfo" class="cursor-pointer font-normal">
                                    Customize Attendee Information
                                </Label>
                            </div>
                            <!-- Attendee Information -->
                            <Transition enter-active-class="transition-all duration-500 ease-out"
                                enter-from-class="opacity-0" enter-to-class="opacity-100"
                                leave-active-class="transition-all duration-500 ease-in" leave-from-class="opacity-100"
                                leave-to-class="opacity-0">

                                <!-- <div :class="showTicketHolderInfo == true ? '' : 'hidden'"> -->
                                <!-- <div v-show="showTicketHolderInfo == true"> -->
                                <div >
                                    <h4 class="text-lg font-semibold text-foreground mb-2">Attendee information</h4>
                                    <div v-for="(ticketGroup, index) in ticketGroups" :key="index"
                                        class="border-t pt-6">
                                        <h3 class="text-md font-semibold text-foreground mb-2">
                                            {{ ticketGroup.ticketType.name }} - {{ ticketGroup.quantity }} ticket(s)
                                        </h3>

                                        <div v-for="(attendee, attendeeIndex) in ticketGroup.attendees"
                                            :key="attendeeIndex" class="bg-whites rounded p-4 mb-4">
                                            <h4 class="text-sm font-medium text-foreground/80 mb-3">
                                                Attendee {{ attendeeIndex + 1 }}
                                            </h4>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <Label :for="`attendee_name_${index}_${attendeeIndex}`">
                                                        Full Name <span class="text-red-500">*</span>
                                                    </Label>
                                                    <Input type="text" :id="`attendee_name_${index}_${attendeeIndex}`"
                                                        v-model="attendee.name" required
                                                        class="rounded pri-borders py-5 mt-1" placeholder="Full Name" />
                                                </div>

                                                <div>
                                                    <Label :for="`attendee_email_${index}_${attendeeIndex}`">
                                                        Email Address
                                                    </Label>
                                                    <Input type="email" :id="`attendee_email_${index}_${attendeeIndex}`"
                                                        v-model="attendee.email" class="rounded pri-borders py-5 mt-1"
                                                        placeholder="attendee@email.com (optional)" />
                                                </div>
                                            </div>

                                            <!-- Custom form fields (only shown when event has required fields) -->
                                            <div v-if="hasRequiredCustomFields" class="space-y-4">
                                                <h2 class="text-lg sm:text-xl font-semibold border-b pb-2 mt-8">Additional
                                                    Information</h2>
                                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                    <template v-for="field in custom_fields" :key="field.id">
                                                        <!-- Text input -->
                                                        <div v-if="field.field_type === 'text'"
                                                            :class="{ 'sm:col-span-2': field.field_type === 'text' && !field.placeholder }">
                                                            <Label :for="`field_${index}_${attendeeIndex}_${field.id}`">
                                                                {{ field.label }}<span v-if="field.is_required"
                                                                    class="text-red-500 ml-1">*</span>
                                                            </Label>
                                                            <Input :id="`field_${index}_${attendeeIndex}_${field.id}`"
                                                                v-model="(attendee.custom_fields[field.id] as string)"
                                                                type="text" :placeholder="field.placeholder || ''"
                                                                :required="field.is_required"
                                                                class="rounded pri-borders py-5 mt-1"
                                                                :class="{ 'border-red-500': errors['custom_fields.' + field.id] }" />
                                                            <p v-if="errors['custom_fields.' + field.id]"
                                                                class="mt-1 text-sm text-red-600">
                                                                {{ errors['custom_fields.' + field.id] }}
                                                            </p>
                                                        </div>

                                                        <!-- Textarea -->
                                                        <div v-else-if="field.field_type === 'textarea'"
                                                            class="sm:col-span-2">
                                                            <Label :for="`field_${index}_${attendeeIndex}_${field.id}`">
                                                                {{ field.label }}<span v-if="field.is_required"
                                                                    class="text-red-500 ml-1">*</span>
                                                            </Label>
                                                            <Textarea :id="`field_${index}_${attendeeIndex}_${field.id}`"
                                                                v-model="(attendee.custom_fields[field.id] as string)"
                                                                :placeholder="field.placeholder || ''"
                                                                :required="field.is_required"
                                                                class="rounded pri-borders py-3 mt-1"
                                                                :class="{ 'border-red-500': errors['custom_fields.' + field.id] }" />
                                                            <p v-if="errors['custom_fields.' + field.id]"
                                                                class="mt-1 text-sm text-red-600">
                                                                {{ errors['custom_fields.' + field.id] }}
                                                            </p>
                                                        </div>

                                                        <!-- Select / Dropdown -->
                                                        <div v-else-if="field.field_type === 'select'">
                                                            <Label :for="`field_${index}_${attendeeIndex}_${field.id}`">
                                                                {{ field.label }}<span v-if="field.is_required"
                                                                    class="text-red-500 ml-1">*</span>
                                                            </Label>
                                                            <Select v-model="(attendee.custom_fields[field.id] as string)">
                                                                <SelectTrigger :id="`field_${index}_${attendeeIndex}_${field.id}`"
                                                                    class="mt-1 w-full rounded pri-borders py-5"
                                                                    :class="{ 'border-red-500': errors['custom_fields.' + field.id] }">
                                                                    <SelectValue placeholder="Select an option..." />
                                                                </SelectTrigger>
                                                                <SelectContent
                                                                    class="bg-background rounded pri-borders px-3 py-1 shadow-sm">
                                                                    <SelectItem v-for="opt in field.options"
                                                                        :key="opt.value" :value="opt.value">
                                                                        {{ opt.value }}
                                                                    </SelectItem>
                                                                </SelectContent>
                                                            </Select>
                                                            <p v-if="errors['custom_fields.' + field.id]"
                                                                class="mt-1 text-sm text-red-600">
                                                                {{ errors['custom_fields.' + field.id] }}
                                                            </p>
                                                        </div>

                                                        <!-- Radio buttons -->
                                                        <div v-else-if="field.field_type === 'radio'">
                                                            <Label>
                                                                {{ field.label }}<span v-if="field.is_required"
                                                                    class="text-red-500 ml-1">*</span>
                                                            </Label>
                                                            <RadioGroup v-model="(attendee.custom_fields[field.id] as string)"
                                                                class="mt-2 space-y-2">
                                                                <div v-for="opt in field.options" :key="opt.value"
                                                                    class="flex items-center space-x-2">
                                                                    <RadioGroupItem
                                                                        :id="`field_${index}_${attendeeIndex}_${field.id}_${opt.value}`"
                                                                        :value="opt.value" />
                                                                    <Label :for="`field_${index}_${attendeeIndex}_${field.id}_${opt.value}`"
                                                                        class="cursor-pointer font-normal">
                                                                        {{ opt.value }}
                                                                    </Label>
                                                                </div>
                                                            </RadioGroup>
                                                            <p v-if="errors['custom_fields.' + field.id]"
                                                                class="mt-1 text-sm text-red-600">
                                                                {{ errors['custom_fields.' + field.id] }}
                                                            </p>
                                                        </div>

                                                        <!-- Checkboxes (multi-select) -->
                                                        <div v-else-if="field.field_type === 'checkbox'"
                                                            class="sm:col-span-2">
                                                            <Label>
                                                                {{ field.label }}<span v-if="field.is_required"
                                                                    class="text-red-500 ml-1">*</span>
                                                            </Label>
                                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                                <div v-for="opt in field.options" :key="opt.value"
                                                                    class="flex items-center space-x-2">
                                                                    <Checkbox
                                                                        :id="`field_${index}_${attendeeIndex}_${field.id}_${opt.value}`"
                                                                        :checked="isCheckboxOptionChecked(attendee, field.id, opt.value)"
                                                                        @update:checked="(checked: boolean | 'indeterminate') => toggleCheckboxOption(attendee, field.id, opt.value, checked === true)" />
                                                                    <Label :for="`field_${index}_${attendeeIndex}_${field.id}_${opt.value}`"
                                                                        class="cursor-pointer font-normal text-sm">
                                                                        {{ opt.value }}
                                                                    </Label>
                                                                </div>
                                                            </div>
                                                            <p v-if="errors['custom_fields.' + field.id]"
                                                                class="mt-1 text-sm text-red-600">
                                                                {{ errors['custom_fields.' + field.id] }}
                                                            </p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </form>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-card rounded-all p-6 sticky top-8">
                        <h3 class="text-lg font-semibold text-foreground mb-4">Order Summary</h3>

                        <div class="space-y-3 mb-6">
                            <div v-for="selection in ticketSelections" :key="selection.ticket_type_id"
                                class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="font-medium text-foreground">
                                        {{ getTicketTypeName(selection.ticket_type_id) }}</div>
                                    <div class="text-sm text-foreground/70">× {{ selection.quantity }}</div>
                                </div>
                                <div class="text-sm font-medium text-foreground">
                                    GHS
                                    {{ (getTicketTypePrice(selection.ticket_type_id) * selection.quantity).toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="appliedDiscount" class="border-t pt-4 mb-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-foreground/70">Subtotal:</span>
                                <span class="text-foreground">GHS {{ subtotalAmount.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-green-600">
                                <span>Discount ({{ appliedDiscount.code }}):</span>
                                <span>-GHS {{ appliedDiscount.amount.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center text-lg font-bold">
                                <span>Total:</span>
                                <span>GHS {{ finalAmount.toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Proceed to Payment Button -->
                        <div class="w-full text-center">
                            <Button @click="submitPurchase" :disabled="processing || !isFormValid"
                                class="cursor-pointer w-full lg:text-xs mt-6 bg-primary cursor-pointer py-5 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ processing ? 'Processing...' : `Proceed to Payment - GHS ${finalAmount.toFixed(2)}` }}
                            </Button>

                            <p class="text-xs text-foreground/70 text-center mt-3">
                                Secure payment processing. Your information is protected.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Button from '@/components/ui/button/Button.vue'
import NavBar from '@/components/NavBar.vue'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Label } from '@/components/ui/label'
import Input from '@/components/ui/input/Input.vue'

interface TicketSelection {
    ticket_type_id: number
    quantity: number
}

interface TicketType {
    id: number
    name: string
    price: number
    description?: string
}

interface Event {
    id: number
    title: string
    start_date: string
    start_time: string
    cover_image_url?: string
    venue_name?: string
    venue?: {
        name: string
    }
}

interface Attendee {
    name: string
    email: string
    custom_fields: Record<number, string | string[]>
}

interface EventFormField {
    id: number;
    label: string;
    field_type: 'text' | 'textarea' | 'select' | 'radio' | 'checkbox';
    options?: { value: string }[] | null;
    placeholder?: string;
    is_required: boolean;
    sort_order: number;
}


interface TicketGroup {
    ticketType: TicketType
    quantity: number
    attendees: Attendee[]
}

const page = usePage()
const props = defineProps<{
    event: Event
    ticketTypes: TicketType[]
    ticketSelections: TicketSelection[]
    appliedDiscount?: any
    seo: any
    custom_fields: EventFormField[]
    errors: Record<string, string>
}>()

// Form state
const processing = ref(false)
const form = ref({
    buyer_email: '',
    buyer_phone: '',
    honeypot: '',
    custom_fields: <Record<number, string | string[]>>{}
})

const showTicketHolderInfo = ref(false)

const showAttendeeInfo =()=> {

     showTicketHolderInfo.value = !showTicketHolderInfo.value

     console.log(showTicketHolderInfo.value)
}

const hasRequiredCustomFields = computed(() =>
    props.custom_fields?.some((field) => field.is_required) ?? false
)

const isCustomFieldFilled = (attendee: Attendee, field: EventFormField): boolean => {
    const value = attendee.custom_fields[field.id]

    if (field.field_type === 'checkbox') {
        return Array.isArray(value) && value.length > 0
    }

    return typeof value === 'string' && value.trim().length > 0
}

const initializeAttendeeCustomFields = (): Record<number, string | string[]> => {
    const fields: Record<number, string | string[]> = {}
    props.custom_fields?.forEach((field) => {
        fields[field.id] = field.field_type === 'checkbox' ? [] : ''
    })
    return fields
}

const isCheckboxOptionChecked = (attendee: Attendee, fieldId: number, optionValue: string): boolean => {
    const values = attendee.custom_fields[fieldId]
    return Array.isArray(values) && values.includes(optionValue)
}

const toggleCheckboxOption = (attendee: Attendee, fieldId: number, optionValue: string, checked: boolean) => {
    if (!Array.isArray(attendee.custom_fields[fieldId])) {
        attendee.custom_fields[fieldId] = []
    }

    const values = attendee.custom_fields[fieldId] as string[]

    if (checked) {
        if (!values.includes(optionValue)) {
            values.push(optionValue)
        }
    } else {
        attendee.custom_fields[fieldId] = values.filter((value) => value !== optionValue)
    }
}

// Create ticket groups with attendees - use ref to make it reactive and persistent
const ticketGroups = ref<TicketGroup[]>([])

// Initialize ticket groups
const initializeTicketGroups = () => {
    ticketGroups.value = props.ticketSelections.map(selection => {
        const ticketType = props.ticketTypes.find(t => t.id === selection.ticket_type_id)!
        return {
            ticketType,
            quantity: selection.quantity,
            attendees: Array.from({ length: selection.quantity }, () => ({
                name: 'Guest',
                email: form.value.buyer_email,
                custom_fields: initializeAttendeeCustomFields(),
            }))

        }
    })
}

// Watch for changes to buyer_email and update all attendee emails accordingly
watch(
    () => form.value.buyer_email,
    (newEmail) => {
        ticketGroups.value.forEach(group => {
            group.attendees.forEach(attendee => {
                attendee.email = newEmail
            })
        })
    }
)

// Calculations
const subtotalAmount = computed(() => {
    return props.ticketSelections.reduce((total, selection) => {
        const ticketType = props.ticketTypes.find(t => t.id === selection.ticket_type_id)
        return total + (ticketType ? ticketType.price * selection.quantity : 0)
    }, 0)
})

const finalAmount = computed(() => {
    let amount = subtotalAmount.value
    if (props.appliedDiscount) {
        amount -= props.appliedDiscount.amount
    }
    return Math.max(0, amount)
})

// Form validation
const isFormValid = computed(() => {
    if (!form.value.buyer_email?.trim()) return false

    if (hasRequiredCustomFields.value) {
        const requiredFieldsValid = ticketGroups.value.every(group =>
            group.attendees.every(attendee =>
                props.custom_fields
                    .filter((field) => field.is_required)
                    .every((field) => isCustomFieldFilled(attendee, field))
            )
        )
        if (!requiredFieldsValid) return false
    }

    if (!showTicketHolderInfo.value) return true

    return ticketGroups.value.every(group =>
        group.attendees.every(attendee => attendee.name?.trim().length > 0)
    )
})

// Helper functions
const getTicketTypeName = (ticketTypeId: number) => {
    const ticketType = props.ticketTypes.find(t => t.id === ticketTypeId)
    return ticketType?.name || 'Unknown'
}

const getTicketTypePrice = (ticketTypeId: number) => {
    const ticketType = props.ticketTypes.find(t => t.id === ticketTypeId)
    return ticketType?.price || 0
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatTime = (time: string) => {
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    })
}

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
}

const submitPurchase = () => {
    processing.value = true

    // Prepare tickets data for backend — custom_fields sent per-attendee
    const ticketsData = ticketGroups.value.map(group => ({
        ticket_type_id: group.ticketType.id,
        quantity: group.quantity,
        attendees: group.attendees.map(attendee => ({
            name: attendee.name,
            email: attendee.email || null,
            custom_fields: attendee.custom_fields,
        }))
    }))

    router.post(route('events.tickets.purchase', props.event.id), {
        buyer_email: form.value.buyer_email,
        buyer_phone: form.value.buyer_phone,
        tickets: ticketsData,
        discount_id: props.appliedDiscount?.id || null,
        honeypot: form.value.honeypot,
    }, {
        onFinish: () => {
            processing.value = false
        }
    })
}

// Auto-fill user email if authenticated and initialize ticket groups
onMounted(() => {
    initializeTicketGroups()

    // Then auto-fill user email
    const user = page.props.auth?.user
    if (user && user.email) {
        form.value.buyer_email = user.email
    }
})
</script>
