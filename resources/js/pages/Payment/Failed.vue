<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <!-- Error Header -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <XCircleIcon class="h-8 w-8 text-red-600" />
                </div>
                <h1 class="mb-2 text-3xl font-bold text-gray-900">Payment Failed</h1>
                <p class="text-gray-600">We encountered an issue processing your payment. Your order has not been confirmed.</p>
            </div>

            <!-- Error Details Card -->
            <div class="mb-8 overflow-hidden bg-white shadow-md">
                <div class="px-6 py-8">
                    <div class="text-center">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">What happened?</h3>
                        <div class="space-y-3 text-gray-600">
                            <p v-if="$page.props.flash?.error" class="font-medium text-red-600">
                                {{ $page.props.flash.error }}
                            </p>
                            <p v-else>Your payment could not be processed. This could be due to:</p>
                            <ul class="mx-auto max-w-md space-y-2 text-left">
                                <li class="flex items-start">
                                    <span class="mr-2 text-red-500">•</span>
                                    <span>Insufficient funds in your account</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2 text-red-500">•</span>
                                    <span>Incorrect payment details</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2 text-red-500">•</span>
                                    <span>Network connectivity issues</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-2 text-red-500">•</span>
                                    <span>Bank authorization declined</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's Next -->
            <div class="mb-8 border border-yellow-200 bg-yellow-50 p-6">
                <h3 class="mb-4 text-lg font-semibold text-yellow-900">What can you do?</h3>
                <ul class="space-y-2 text-yellow-800">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Check your payment method and try again</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Contact your bank to ensure your card is authorized for online payments</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Try using a different payment method</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Contact the event organizer if you continue to experience issues</span>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                <button
                    @click="goBack"
                    class="inline-flex items-center justify-center border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    <ArrowLeftIcon class="mr-2 h-5 w-5" />
                    Try Again
                </button>
                <Link
                    :href="route('events.index')"
                    class="inline-flex items-center justify-center border border-gray-300 bg-white px-6 py-3 text-base font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    Browse Other Events
                </Link>
            </div>

            <!-- Support Information -->
            <div class="mt-12 text-center">
                <p class="text-sm text-gray-500 mb-4">
                    Need help? Contact our support team:
                </p>
                <div class="space-y-2">
                    <p class="text-sm text-gray-600">
                        <a href="mailto:support@suptix.live" class="font-medium text-indigo-600 hover:text-indigo-500">support@suptix.live</a>
                    </p>
                    <p class="text-sm text-gray-600">
                        <a href="tel:+233242728115" class="font-medium text-green-600 hover:text-green-700">+233242728115</a>
                    </p>
                    <p class="text-sm text-gray-600">
                        <a href="https://wa.me/233245001476" target="_blank" class="font-medium text-green-600 hover:text-green-700">WhatsApp: +233245001476</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, XCircleIcon } from 'lucide-vue-next';

defineProps<{
    seo?: any;
}>();

const goBack = () => {
    history.back();
};
</script>
