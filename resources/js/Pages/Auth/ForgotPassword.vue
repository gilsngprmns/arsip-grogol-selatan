<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AppIcon from '@/Components/AppIcon.vue';
import { institution } from '@/config/institution';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

// ── CSRF token dari cookie yang diset Laravel / Sanctum ──────────────────────
const getCsrfToken = () => {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
};

// ── State langkah (step) ─────────────────────────────────────────────────────
const step = ref(1);
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);

// ── Loading & pesan error ────────────────────────────────────────────────────
const loading = ref({ sendOtp: false, verifyOtp: false, resetPassword: false });
const errorMessage = ref('');
const otpMessage = ref('');

// ── OTP digit input ──────────────────────────────────────────────────────────
const otpDigits = ref(['', '', '', '', '', '']);

const otpValid = computed(() => {
    return otpDigits.value.every(d => d !== '') && otpDigits.value.join('').length === 6;
});

const otpCode = computed(() => otpDigits.value.join(''));

// ── Step 1: Kirim OTP ────────────────────────────────────────────────────────
const startReset = async () => {
    errorMessage.value = '';
    otpMessage.value = '';

    if (!email.value || !/\S+@\S+\.\S+/.test(email.value)) {
        errorMessage.value = 'Masukkan alamat email yang valid.';
        return;
    }

    loading.value.sendOtp = true;
    try {
        await axios.post(route('password.otp.send'), { email: email.value }, {
            headers: { 'X-XSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
        });
        step.value = 2;
    } catch (err) {
        const data = err.response?.data;
        if (data?.message) {
            errorMessage.value = data.message;
        } else if (err.response?.status === 429) {
            errorMessage.value = 'Terlalu banyak permintaan. Tunggu beberapa menit sebelum mencoba lagi.';
        } else {
            errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        loading.value.sendOtp = false;
    }
};

// ── Step 2: Verifikasi OTP ───────────────────────────────────────────────────
const verifyOtp = async () => {
    errorMessage.value = '';
    otpMessage.value = '';

    if (!otpValid.value) {
        errorMessage.value = 'Masukkan kode verifikasi 6 digit lengkap.';
        return;
    }

    loading.value.verifyOtp = true;
    try {
        await axios.post(route('password.otp.verify'), {
            email: email.value,
            otp: otpCode.value,
        }, {
            headers: { 'X-XSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
        });
        step.value = 3;
    } catch (err) {
        const data = err.response?.data;
        if (data?.errors?.otp) {
            otpMessage.value = data.errors.otp[0];
        } else if (data?.message) {
            otpMessage.value = data.message;
        } else {
            errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        loading.value.verifyOtp = false;
    }
};

// ── Step 3: Reset Password ───────────────────────────────────────────────────
const resetPassword = async () => {
    errorMessage.value = '';
    otpMessage.value = '';

    if (password.value.length < 8) {
        errorMessage.value = 'Kata sandi minimal 8 karakter.';
        return;
    }
    if (password.value !== passwordConfirmation.value) {
        errorMessage.value = 'Konfirmasi kata sandi tidak cocok.';
        return;
    }

    loading.value.resetPassword = true;
    try {
        await axios.post(route('password.otp.reset'), {
            email: email.value,
            otp: otpCode.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        }, {
            headers: { 'X-XSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
        });
        // Redirect ke login setelah sukses
        window.location.href = route('login');
    } catch (err) {
        const data = err.response?.data;
        if (data?.errors?.otp) {
            otpMessage.value = data.errors.otp[0];
        } else if (data?.errors?.password) {
            errorMessage.value = data.errors.password[0];
        } else if (data?.message) {
            errorMessage.value = data.message;
        } else {
            errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        loading.value.resetPassword = false;
    }
};

// ── Kirim ulang OTP ──────────────────────────────────────────────────────────
const resendOtp = async () => {
    errorMessage.value = '';
    otpMessage.value = '';
    loading.value.sendOtp = true;
    try {
        await axios.post(route('password.otp.send'), { email: email.value }, {
            headers: { 'X-XSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' }
        });
        otpMessage.value = 'Kode verifikasi baru telah dikirim ke ' + email.value;
    } catch (err) {
        const data = err.response?.data;
        if (data?.message) {
            errorMessage.value = data.message;
        } else if (err.response?.status === 429) {
            otpMessage.value = 'Terlalu banyak permintaan. Tunggu beberapa menit.';
        } else {
            errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    } finally {
        loading.value.sendOtp = false;
    }
};

// ── Helper OTP input ─────────────────────────────────────────────────────────
const handleOtpInput = (index, event) => {
    const value = event.target.value;
    if (isNaN(value)) return;
    otpDigits.value[index] = value.substring(value.length - 1);
    if (value && index < 5) {
        const next = document.getElementById(`otp-${index + 1}`);
        if (next) next.focus();
    }
};

const handleOtpKeydown = (index, event) => {
    if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
        const prev = document.getElementById(`otp-${index - 1}`);
        if (prev) prev.focus();
    }
};

const handleOtpPaste = (event) => {
    event.preventDefault();
    const paste = event.clipboardData.getData('text').replace(/\D/g, '').substring(0, 6);
    if (!paste) return;
    const digits = paste.split('');
    digits.forEach((digit, i) => { if (i < 6) otpDigits.value[i] = digit; });
    const nextIndex = Math.min(digits.length, 5);
    const nextInput = document.getElementById(`otp-${nextIndex}`);
    if (nextInput) nextInput.focus();
};

// ── Navigasi step ────────────────────────────────────────────────────────────
const goToStep = (targetStep) => {
    step.value = targetStep;
    errorMessage.value = '';
    otpMessage.value = '';
    if (targetStep === 1) {
        otpDigits.value = ['', '', '', '', '', ''];
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa kata sandi" />

        <div class="mb-8">
            <p class="text-sm font-medium text-brand-blue">Pemulihan akun</p>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-brand-navy">
                <template v-if="step === 1">Lupa kata sandi?</template>
                <template v-else-if="step === 2">Verifikasi kode</template>
                <template v-else>Atur kata sandi baru</template>
            </h1>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                <template v-if="step === 1">
                    Masukkan alamat email akun Anda. Kami akan mengirim kode verifikasi 6 digit untuk mengatur ulang kata sandi.
                </template>
                <template v-else-if="step === 2">
                    Kode verifikasi telah dikirim ke <strong>{{ email }}</strong>. Masukkan kode 6 digit yang Anda terima.
                </template>
                <template v-else>
                    Buat kata sandi baru untuk akun Anda. Gunakan kombinasi yang mudah diingat, tetapi sulit ditebak orang lain.
                </template>
            </p>
            <p v-if="step === 1" class="mt-2 text-xs leading-6 text-slate-400">
                {{ institution.adminNote }}
            </p>
        </div>

        <!-- Step 1: Email -->
        <div v-if="step === 1">
            <form class="space-y-5" @submit.prevent="startReset">
                <div>
                    <InputLabel for="email" value="Alamat email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        v-model="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="contoh@email.go.id"
                    />
                    <InputError class="mt-2" :message="errorMessage" />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        :href="route('login')"
                        class="text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-blue"
                    >
                        Kembali ke masuk
                    </Link>
                    <PrimaryButton :disabled="loading.sendOtp">
                        <span v-if="loading.sendOtp" class="flex items-center gap-2">
                            <AppIcon name="loader" :size="16" :stroke-width="4" class="h-4 w-4 animate-spin" decorative />
                            Mengirim...
                        </span>
                        <span v-else>Kirim Kode Verifikasi</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Step 2: OTP -->
        <div v-else-if="step === 2">
            <div v-if="otpMessage && !errorMessage" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ otpMessage }}
            </div>

            <InputError v-if="errorMessage && !otpMessage" :message="errorMessage" />
            <InputError v-else-if="otpMessage" :message="otpMessage" />

            <div class="mt-6">
                <InputLabel value="Kode Verifikasi (6 digit)" />
                <div class="mt-2 flex gap-2">
                    <template v-for="(digit, index) in otpDigits" :key="index">
                        <input
                            :id="`otp-${index}`"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            :value="digit"
                            class="h-12 w-12 rounded-lg border border-slate-300 text-center text-lg font-semibold shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                            @input="handleOtpInput(index, $event)"
                            @keydown="handleOtpKeydown(index, $event)"
                            @paste="handleOtpPaste($event)"
                        />
                    </template>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    class="text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-blue"
                    @click="goToStep(1)"
                >
                    ← Ubah email
                </button>
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 hover:bg-slate-50"
                        @click="resendOtp"
                        :disabled="loading.sendOtp"
                    >
                        Kirim Ulang
                    </button>
                    <PrimaryButton
                        :disabled="!otpValid || loading.verifyOtp"
                        @click="verifyOtp"
                    >
                        <span v-if="loading.verifyOtp" class="flex items-center gap-2">
                            <AppIcon name="loader" :size="16" :stroke-width="4" class="h-4 w-4 animate-spin" decorative />
                            Memverifikasi...
                        </span>
                        <span v-else>Verifikasi Kode</span>
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- Step 3: New Password -->
        <div v-else-if="step === 3">
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                Kode verifikasi berhasil! Silakan buat kata sandi baru.
            </div>

            <InputError v-if="errorMessage" :message="errorMessage" />

            <form class="space-y-5" @submit.prevent="resetPassword">
                <div>
                    <InputLabel for="password" value="Kata sandi baru" />
                    <div class="relative mt-2">
                        <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                            v-model="password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition duration-200 hover:text-slate-700"
                            @click="showPassword = !showPassword"
                            :title="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        >
                            <AppIcon v-if="!showPassword" name="eye" :size="20" :stroke-width="1.8" class="h-5 w-5" decorative />
                            <AppIcon v-else name="eye-off" :size="20" :stroke-width="1.8" class="h-5 w-5" decorative />
                        </button>
                    </div>
                    <InputError class="mt-2" :message="errorMessage" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Konfirmasi kata sandi" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 shadow-sm focus:border-brand-blue focus:ring-brand-blue"
                        v-model="passwordConfirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="errorMessage" />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="button"
                        class="text-sm font-semibold text-slate-500 transition duration-200 hover:text-brand-blue"
                        @click="goToStep(2)"
                    >
                        ← Kembali ke verifikasi
                    </button>
                    <PrimaryButton class="w-full sm:w-auto" :disabled="loading.resetPassword">
                        <span v-if="loading.resetPassword" class="flex items-center justify-center gap-2">
                            <AppIcon name="loader" :size="16" :stroke-width="4" class="h-4 w-4 animate-spin" decorative />
                            Menyimpan...
                        </span>
                        <span v-else>Simpan Kata Sandi Baru</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
