<template>
    <div class="space-y-6">
        <!-- Ticket Types -->
        <div class="grid gap-4">
            <div v-for="ticketType in ticketTypes" :key="ticketType.id" class="rounded  p-4 transition-colors hover:border-blue-500">
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">{{ ticketType.name }}</h3>
                        <p class="text-sm foreground/70 line-clamp-1 overflow-hidden text-ellipsis" v-if="ticketType.description">
                            {{ ticketType.description }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold w-max">GHS {{ ticketType.price }}</div>
                        <div class="text-sm foreground/60">{{ ticketType.available_quantity || ticketType.quantity }} available</div>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="flex flex-col sm:flex-row items-start justify-between">
                     <div class="flex items-center gap-3">
                         <label class="text-sm font-medium">Quantity:</label>
                         <div class="flex items-center gap-2">
                             <Button
                                 variant="default"
                                 size="sm"

                                 class="cursor-pointer rounded "

                                 @click="decreaseQuantity(ticketType.id)"
                                 :disabled="!selectedTickets[ticketType.id] || selectedTickets[ticketType.id].quantity <= 0"
                             >
                                 -
                             </Button>
                             <span class="w-8 text-center">{{ getQuantity(ticketType.id) }}</span>
                             <Button
                                 variant="default"
                                 size="sm"

                                 class="cursor-pointer rounded "

                                 @click="increaseQuantity(ticketType.id)"
                                 :disabled="getQuantity(ticketType.id) >= Math.min(ticketType.available_quantity || ticketType.quantity, 10)"
                             >
                                 +
                             </Button>
                         </div>
                     </div>
                     <div v-if="ticketType.sales_start || ticketType.sales_end" class="text-right text-xs text-blue-600 mt-1">
                         <div v-if="ticketType.sales_start">
                            Sales start: {{ formatDateTime(ticketType.sales_start) }}
                         </div>
                         <div v-if="ticketType.sales_end">
                            Sales end: {{ formatDateTime(ticketType.sales_end) }}
                         </div>
                     </div>
                 </div>
            </div>
        </div>

        <!-- Selected Tickets Summary -->
        <div v-if="hasSelectedTickets" class="border-t pt-6">
            <h3 class="mb-4 text-lg font-semibold">Selected Tickets</h3>
            <div class="space-y-3">
                <div
                    v-for="(selection, ticketTypeId) in selectedTickets"
                    :key="ticketTypeId"
                    v-show="selection.quantity > 0"
                    class="flex items-center justify-between rounded-lg bg-card rounded p-3"
                >
                    <div>
                        <span class="font-medium">{{ getTicketTypeName(ticketTypeId) }}</span>
                        <span class="ml-2 foreground/60">× {{ selection.quantity }}</span>
                    </div>
                    <div class="font-semibold">GHS {{ (getTicketTypePrice(ticketTypeId) * selection.quantity) }}</div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between border-t pt-4">
                <span class="text-lg font-semibold">Total:</span>
                <span class="text-xl font-bold">GHS {{ totalAmount }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { computed, reactive, watch } from 'vue';

interface TicketType {
    id: number;
    name: string;
    description?: string;
    price: number;
    quantity: number;
    available_quantity?: number;
    sold_tickets_count?: number;
    total_capacity?: number;
    sales_start?: string;
    sales_end?: string;
}

interface TicketSelection {
    ticket_type_id: number;
    quantity: number;
}

const props = defineProps<{
    ticketTypes: TicketType[];
}>();

const emit = defineEmits<{
    update: [selections: TicketSelection[]];
}>();

const selectedTickets = reactive<Record<number, { quantity: number }>>({});

const hasSelectedTickets = computed(() => {
    return Object.values(selectedTickets).some((selection) => selection.quantity > 0);
});

const totalAmount = computed(() => {
    return Object.entries(selectedTickets).reduce((total, [ticketTypeId, selection]) => {
        const ticketType = props.ticketTypes.find((t) => t.id === Number(ticketTypeId));
        if (ticketType && selection.quantity > 0) {
            return total + ticketType.price * selection.quantity;
        }
        return total;
    }, 0);
});

const getQuantity = (ticketTypeId: number): number => {
    return selectedTickets[ticketTypeId]?.quantity || 0;
};

const getTicketTypeName = (ticketTypeId: number): string => {
    const ticketType = props.ticketTypes.find((t) => t.id === Number(ticketTypeId));
    return ticketType?.name || '';
};

const getTicketTypePrice = (ticketTypeId: number): number => {
    const ticketType = props.ticketTypes.find((t) => t.id === Number(ticketTypeId));
    return ticketType?.price || 0;
};

const increaseQuantity = (ticketTypeId: number) => {
    if (!selectedTickets[ticketTypeId]) {
        selectedTickets[ticketTypeId] = { quantity: 0 };
    }
    const ticketType = props.ticketTypes.find((t) => t.id === ticketTypeId);
    const availableQuantity = ticketType?.available_quantity || ticketType?.quantity || 0;
    if (ticketType && selectedTickets[ticketTypeId].quantity < Math.min(availableQuantity, 10)) {
        selectedTickets[ticketTypeId].quantity++;
    }
};

const decreaseQuantity = (ticketTypeId: number) => {
    if (selectedTickets[ticketTypeId] && selectedTickets[ticketTypeId].quantity > 0) {
        selectedTickets[ticketTypeId].quantity--;
    }
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

// Watch for changes and emit updates
watch(
    selectedTickets,
    () => {
        const selections: TicketSelection[] = Object.entries(selectedTickets)
            .filter(([_, selection]) => selection.quantity > 0)
            .map(([ticketTypeId, selection]) => ({
                ticket_type_id: Number(ticketTypeId),
                quantity: selection.quantity,
            }));

        emit('update', selections);
    },
    { deep: true },
);
</script>
