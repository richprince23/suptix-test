<script setup lang="ts">
import { DateFormatter, type DateValue, getLocalTimeZone, today } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const items = [
    { value: 0, label: 'Today' },
    { value: 1, label: 'Tomorrow' },
    { value: 3, label: 'In 3 days' },
    { value: 7, label: 'In a week' },
];

// const value = ref<DateValue>()

const props = defineProps<{
    modelValue?: DateValue;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: DateValue | undefined];
}>();

// Use computed for two-way binding
const value = computed({
    get: () => props.modelValue,
    set: (newValue: DateValue) => emit('update:modelValue', newValue),
});
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="ghost" :class="cn('w-min justify-start border-0 bg-transparent text-left font-normal', !value && 'text-white')">
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ value ? df.format(value.toDate(getLocalTimeZone())) : 'Pick a date' }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="flex w-auto flex-col gap-y-2 p-2">
            <Select
                @update:model-val="
                    (v: DateValue) => {
                        if (!v) return;
                        value = today(getLocalTimeZone()).add({ days: Number(v) });
                    }
                "
            >
                <SelectTrigger>
                    <SelectValue placeholder="Select" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="item in items" :key="item.value" :value="item.value.toString()">
                        {{ item.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Calendar v-model="value" />
        </PopoverContent>
    </Popover>
</template>
