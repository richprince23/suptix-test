<template>
    <SeoHead :seo="seo" />
    <div class="min-h-screen bg-gray-50">
        <NavBar class="fixed z-20 w-full bg-black border-b border-gray-200 shadow-sm" />

        <div class="pt-20 pb-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4 mt-12">Submit Payment Information</h1>
                    <p class="text-xl text-gray-600">Set up your payment account to start receiving payments for your events</p>
                </div>

                <div class="bg-white shadow-sm p-8">
                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 p-4 mb-8">
                        <div class="flex items-center">
                            <Info class="h-5 w-5 text-blue-600 mr-2" />
                            <div>
                                <h3 class="font-medium text-blue-900">Account Verification Required</h3>
                                <p class="text-blue-800 text-sm mt-1">
                                    We use Paystack to verify your account details. Your information will be validated in real-time
                                    to ensure secure and accurate payments.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Step 1: Input Details -->
                        <div v-if="!verificationResult" class="space-y-6">
                            <!-- Business Name -->
                            <div>
                                <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Name *
                                </label>
                                <input
                                    v-model="form.business_name"
                                    type="text"
                                    id="business_name"
                                    required
                                    placeholder="Your business or organization name"
                                    class="w-full px-3 py-2 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <p class="text-sm text-gray-500 mt-1">This will appear on your Paystack subaccount</p>
                            </div>

                            <!-- Payment Channel -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Channel *</label>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.payment_channel"
                                            type="radio"
                                            value="bank"
                                            required
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <span class="ml-2 text-gray-700">Bank Account</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.payment_channel"
                                            type="radio"
                                            value="mobile_money"
                                            required
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <span class="ml-2 text-gray-700">Mobile Money</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Bank Selection -->
                            <div>
                                <label for="bank_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ form.payment_channel === 'mobile_money' ? 'Mobile Money Provider' : 'Bank' }} *
                                </label>
                                <select
                                    v-model="form.bank_code"
                                    id="bank_code"
                                    required
                                    @change="bankChanged"
                                    class="w-full px-3 py-2 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">{{ form.payment_channel === 'mobile_money' ? 'Select Mobile Money Provider' : 'Select Bank' }}</option>
                                    <template v-if="form.payment_channel === 'bank'">
                                        <option v-for="(name, code) in bankOptions" :key="code" :value="code">
                                            {{ name }}
                                        </option>
                                    </template>
                                    <template v-else-if="form.payment_channel === 'mobile_money'">
                                        <option v-for="(name, code) in mobileMoneyOptions" :key="code" :value="code">
                                            {{ name }}
                                        </option>
                                    </template>
                                </select>
                            </div>

                            <!-- Account Number -->
                            <div>
                                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ form.payment_channel === 'mobile_money' ? 'Mobile Money Number' : 'Account Number' }} *
                                </label>
                                <input
                                    v-model="form.account_number"
                                    type="text"
                                    id="account_number"
                                    required
                                    :placeholder="form.payment_channel === 'mobile_money' ? '024XXXXXXX' : 'Your account number'"
                                    class="w-full px-3 py-2 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <!-- Verify Account Button -->
                            <div v-if="form.bank_code && form.account_number && form.business_name">
                                <button
                                    type="button"
                                    @click="verifyAccount"
                                    :disabled="isVerifying"
                                    class="w-full px-4 py-3 bg-green-600 text-white font-semibold hover:bg-green-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isVerifying" class="flex items-center justify-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Verifying Account...
                                    </span>
                                    <span v-else>Verify Account Details</span>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Verification Result & Confirmation -->
                        <div v-if="verificationResult" class="space-y-6">
                            <!-- Account Details Summary -->
                            <div class="bg-green-50 border border-green-200 p-6">
                                <div class="flex items-center mb-4">
                                    <CheckCircle class="h-6 w-6 text-green-600 mr-3" />
                                    <h3 class="text-lg font-semibold text-green-900">Account Verified Successfully</h3>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-green-800">Business Name:</span>
                                        <p class="text-green-700">{{ form.business_name }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-800">Payment Channel:</span>
                                        <p class="text-green-700 capitalize">{{ form.payment_channel?.replace('_', ' ') }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-800">{{ form.payment_channel === 'mobile_money' ? 'Provider' : 'Bank' }}:</span>
                                        <p class="text-green-700">{{ banks[form.bank_code] }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-800">Account Number:</span>
                                        <p class="text-green-700">{{ form.account_number }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <span class="font-medium text-green-800">Account Name:</span>
                                        <p class="text-green-700 font-semibold">{{ verificationResult.account_name }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200">
                                    <p class="text-blue-800 text-sm">
                                        <strong>Note:</strong> These details will be submitted for admin approval. Once approved,
                                        you'll be able to receive payments with a 7% service fee.
                                    </p>
                                </div>
                            </div>

                            <!-- Edit Button -->
                            <div class="flex justify-between items-center">
                                <button
                                    type="button"
                                    @click="resetForm"
                                    class="text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    ← Edit Details
                                </button>
                            </div>
                        </div>

                        <!-- Hidden organizer_id field -->
                        <input type="hidden" v-model="form.organizer_id" />

                        <!-- Submit Button -->
                        <div class="pt-6">
                            <button
                                type="submit"
                                :disabled="isSubmitting || !verificationResult"
                                class="w-full cursor-pointer bg-primary text-white py-3 px-6 font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="isSubmitting">Submitting...</span>
                                <span v-else>Submit Payment Information</span>
                            </button>
                            <p class="text-sm text-gray-500 mt-2 text-center">
                                Your information will be reviewed and approved before payments can be processed
                            </p>
                        </div>
                    </form>

                    <!-- Success Message -->
                    <div v-if="showSuccess" class="mt-6 p-4 bg-green-50 border border-green-200">
                        <div class="flex items-center">
                            <CheckCircle class="h-5 w-5 text-green-600 mr-2" />
                            <p class="text-green-800">{{ successMessage }}</p>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="errors && Object.keys(errors).length > 0" class="mt-6 p-4 bg-red-50 border border-red-200">
                        <div class="flex items-center mb-2">
                            <AlertCircle class="h-5 w-5 text-red-600 mr-2" />
                            <h3 class="font-medium text-red-900">Please fix the following errors:</h3>
                        </div>
                        <ul class="list-disc list-inside text-red-800 text-sm">
                            <li v-for="(error, field) in errors" :key="field">{{ error[0] }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import { router } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, Info } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

defineProps<{
    seo: any;
}>();

const form = ref({
    business_name: '',
    payment_channel: '',
    bank_code: '',
    account_number: '',
    organizer_id: '', // This will be set from the current user's organizer
});

const banks = ref({});
const isSubmitting = ref(false);
const isVerifying = ref(false);
const showSuccess = ref(false);
const successMessage = ref('');
const errors = ref({});
const verificationResult = ref(null);

const bankOptions = computed(() => {
    const filtered = {};
    for (const [code, name] of Object.entries(banks.value)) {
        if (!['MTN', 'VOD', 'ATL', 'ZEEPAY'].includes(code)) {
            filtered[code] = name;
        }
    }
    return filtered;
});

const mobileMoneyOptions = computed(() => {
    const filtered = {};
    for (const [code, name] of Object.entries(banks.value)) {
        if (['MTN', 'VOD', 'ATL', 'ZEEPAY'].includes(code)) {
            filtered[code] = name;
        }
    }
    return filtered;
});

onMounted(async () => {
    // Load banks
    try {
        const response = await fetch('/api/banks');
        const data = await response.json();
        banks.value = data.banks;
    } catch (error) {
        console.error('Failed to load banks:', error);
    }

    // TODO: Set organizer_id from authenticated user
    // This would normally come from your auth system
    form.value.organizer_id = '1'; // Placeholder
});

const bankChanged = () => {
    verificationResult.value = null;
    errors.value = {};
};

const resetForm = () => {
    verificationResult.value = null;
    errors.value = {};
};

const verifyAccount = async () => {
    if (!form.value.bank_code || !form.value.account_number || !form.value.business_name) return;

    isVerifying.value = true;
    errors.value = {};

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch('/api/verify-account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                account_number: form.value.account_number,
                bank_code: form.value.bank_code,
            }),
        });

        const data = await response.json();

        if (data.success) {
            verificationResult.value = data;
        } else {
            errors.value = { account_number: [data.message] };
        }
    } catch (error) {
        errors.value = { account_number: ['Failed to verify account. Please try again.'] };
    } finally {
        isVerifying.value = false;
    }
};

const submitForm = () => {
    if (!verificationResult.value) {
        errors.value = { account_number: ['Please verify your account first'] };
        return;
    }

    isSubmitting.value = true;
    errors.value = {};

    router.post('/api/payment-info', form.value, {
        onSuccess: () => {
            showSuccess.value = true;
            successMessage.value = 'Payment information submitted successfully! You will be notified once it has been reviewed and approved.';
            form.value = {
                business_name: '',
                payment_channel: '',
                bank_code: '',
                account_number: '',
                organizer_id: form.value.organizer_id,
            };
            verificationResult.value = null;

            setTimeout(() => {
                showSuccess.value = false;
            }, 10000);
        },
        onError: (errorResponse) => {
            errors.value = errorResponse;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>
