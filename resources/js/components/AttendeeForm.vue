<template>
    <div class="space-y-6">
        <div class="border-b pb-4">
            <!-- <h3 class="text-lg font-semibold">Attendee Information</h3> -->
            <p class="text-sm text-gray-600">Please provide names for all ticket holders</p>
        </div>

        <div class="space-y-6">
            <div v-for="(ticketGroup, index) in ticketGroups" :key="index" class="rounded  p-4">
                <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <h4 class="font-semibold">{{ ticketGroup.ticketType.name }}</h4>
                    <div class="text-sm text-gray-500">
                        GHS {{ ticketGroup.ticketType.price }} × {{ ticketGroup.quantity }} = GHS
                        {{ (ticketGroup.ticketType.price * ticketGroup.quantity) }}
                    </div>
                </div>

                <div class="grid gap-4">
                    <div
                        v-for="(attendee, attendeeIndex) in ticketGroup.attendees"
                        :key="attendeeIndex"
                        class="grid grid-cols-1 gap-3 rounded pri-borders bg-gray-100/50 p-3 md:grid-cols-2"
                    >
                        <div class="mb-2 text-sm font-medium text-gray-700 md:col-span-2">Ticket {{ attendeeIndex + 1 }}</div>

                        <div>
                            <Label for="name">Full Name</Label>
                            <Input
                                :id="`name_${index}_${attendeeIndex}`"
                                v-model="attendee.name"
                                type="text"
                                required
                                placeholder="Enter full name"
                                :class="{ 'border-red-500': errors![`tickets.${index}.attendees.${attendeeIndex}.name`] }"
                                class="rounded  my-1"
                            />
                            <p v-if="errors![`tickets.${index}.attendees.${attendeeIndex}.name`]" class="mt-1 text-sm text-red-600">
                                {{ errors![`tickets.${index}.attendees.${attendeeIndex}.name`] }}
                            </p>
                        </div>

                        <div>
                            <Label for="email">Email (optional)</Label>
                            <Input
                                :id="`email_${index}_${attendeeIndex}`"
                                v-model="attendee.email"
                                type="email"
                                placeholder="Enter email address"
                                :class="{ 'border-red-500': errors![`tickets.${index}.attendees.${attendeeIndex}.email`] }"
                                class="rounded  my-1"
                            />
                            <p v-if="errors![`tickets.${index}.attendees.${attendeeIndex}.email`]" class="mt-1 text-sm text-red-600">
                                {{ errors![`tickets.${index}.attendees.${attendeeIndex}.email`] }}
                            </p>
                        </div>

                        <!-- Quick Fill Button -->
                        <div class="mt-2 md:col-span-2">
                            <Button variant="outline" size="sm" @click="fillFromBuyer(attendee)" type="button" class="cursor-pointer rounded-all sec-border text-xs">
                                Use buyer information
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { reactive, watch } from 'vue';

interface TicketType {
    id: number;
    name: string;
    price: number;
}

interface TicketSelection {
    ticket_type_id: number;
    quantity: number;
}

interface Attendee {
    name: string;
    email: string;
}

interface TicketGroup {
    ticketType: TicketType;
    ticket_type_id: number;
    quantity: number;
    attendees: Attendee[];
}

const props = defineProps<{
    ticketTypes: TicketType[];
    selections: TicketSelection[];
    buyerEmail?: string;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    update: [ticketGroups: TicketGroup[]];
}>();

const ticketGroups = reactive<TicketGroup[]>([]);

// Watch for selection changes and update attendee forms
watch(
    () => props.selections,
    (newSelections) => {
        // Clear existing groups
        ticketGroups.splice(0, ticketGroups.length);

        // Create new groups based on selections
        newSelections.forEach((selection) => {
            const ticketType = props.ticketTypes.find((t) => t.id === selection.ticket_type_id);
            if (ticketType) {
                const attendees: Attendee[] = [];

                // Create attendee objects for each ticket
                for (let i = 0; i < selection.quantity; i++) {
                    attendees.push({
                        name: '',
                        email: '',
                    });
                }

                ticketGroups.push({
                    ticketType,
                    ticket_type_id: selection.ticket_type_id,
                    quantity: selection.quantity,
                    attendees,
                });
            }
        });
    },
    { immediate: true, deep: true },
);

const fillFromBuyer = (attendee: Attendee) => {
    if (props.buyerEmail) {
        // Extract name from email if available
        const emailParts = props.buyerEmail.split('@')[0].split('.');
        if (emailParts.length >= 2) {
            const firstName = emailParts[0].charAt(0).toUpperCase() + emailParts[0].slice(1);
            const lastName = emailParts[1].charAt(0).toUpperCase() + emailParts[1].slice(1);
            attendee.name = `${firstName} ${lastName}`;
        }
        attendee.email = props.buyerEmail;
    }
};

// Emit updates when attendee info changes
watch(
    ticketGroups,
    () => {
        emit('update', ticketGroups);
    },
    { deep: true },
);
</script>
