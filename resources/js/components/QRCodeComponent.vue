<template>
    <div class="rounded-all border-gray-200 bg-white p-6 text-center">
        <!-- Loading State -->
        <div v-if="isLoading" class="mx-auto mb-3 flex h-40 w-40 items-center justify-center rounded-lg bg-gray-100">
            <div class="text-center">
                <Loader2 class="mx-auto mb-2 h-8 w-8 animate-spin text-gray-400" />
                <p class="text-xs text-gray-500">Generating QR Code...</p>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="mx-auto mb-3 flex h-40 w-40 items-center justify-center rounded-lg border-2 border-dashed border-red-200 bg-red-50"
        >
            <div class="text-center">
                <AlertCircle class="mx-auto mb-2 h-8 w-8 text-red-400" />
                <p class="text-xs text-red-500">Failed to generate</p>
            </div>
        </div>

        <!-- QR Code -->
        <div v-else-if="qrCodeUrl" class="cursor-pointer" @click="showLargeQR = true">
            <img :src="qrCodeUrl" :alt="`QR Code for ${ticketNumber}`" class="mx-auto mb-3 h-40 w-40 transition-transform hover:scale-105" />
        </div>

        <!-- Placeholder -->
        <div v-else class="mx-auto mb-3 flex h-40 w-40 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-100">
            <QrCode class="h-12 w-12 text-gray-400" />
        </div>

        <p class="text-sm font-medium text-gray-600">{{ ticketNumber }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ qrCodeUrl ? 'Click to enlarge • Scan at event entrance' : 'QR Code will load shortly' }}</p>

        <!-- Generate Button if not loaded -->
        <button
            v-if="!qrCodeUrl && !isLoading && !error"
            @click="generateQRCode"
            class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors hover:bg-blue-700"
        >
            Generate QR Code
        </button>

        <!-- Retry Button if error -->
        <button
            v-if="error"
            @click="retryGeneration"
            class="mt-3 rounded-lg bg-red-600 px-4 py-2 text-sm text-white transition-colors hover:bg-red-700"
        >
            Retry
        </button>
    </div>

    <!-- Large QR Code Modal -->
    <div v-if="showLargeQR" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black" @click="showLargeQR = false">
        <div class="mx-4 max-w-sm rounded-lg bg-white p-8" @click.stop>
            <div class="text-center">
                <h3 class="mb-4 text-lg font-semibold">Ticket QR Code</h3>
                <img :src="qrCodeUrl" :alt="`Large QR Code for ${ticketNumber}`" class="mx-auto mb-4 h-64 w-64" />
                <p class="mb-2 text-sm font-medium text-gray-600">{{ ticketNumber }}</p>
                <p class="mb-4 text-xs text-gray-500">Present this QR code at the event entrance</p>
                <button @click="showLargeQR = false" class="rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AlertCircle, Loader2, QrCode } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { nextTick, onMounted, ref } from 'vue';

const props = defineProps<{
    ticketNumber: string;
    ticketId: number;
}>();

const qrCodeUrl = ref<string>('');
const isLoading = ref(false);
const error = ref(false);
const showLargeQR = ref(false);

const generateQRCode = async () => {
    if (isLoading.value || qrCodeUrl.value) return;

    isLoading.value = true;
    error.value = false;

    try {
        // Add a small delay to prevent blocking the main thread
        await nextTick();

        const dataUrl = await QRCode.toDataURL(props.ticketNumber, {
            errorCorrectionLevel: 'M',
            type: 'image/png',
            quality: 0.92,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#FFFFFF',
            },
            width: 200,
            scale: 4,
        });

        qrCodeUrl.value = dataUrl;
    } catch (err) {
        console.error('Failed to generate QR code:', err);
        error.value = true;
    } finally {
        isLoading.value = false;
    }
};

const retryGeneration = () => {
    error.value = false;
    generateQRCode();
};

// Auto-generate on mount with a delay to prevent blocking
onMounted(() => {
    // Stagger QR code generation to prevent browser freeze
    const delay = Math.random() * 1000 + 500; // Random delay between 500ms-1.5s
    setTimeout(() => {
        generateQRCode();
    }, delay);
});
</script>

<style scoped>
/* Add any custom styles here */
</style>
