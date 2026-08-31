<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import M3TextField from '@/Components/M3TextField.vue';
import M3Button from '@/Components/M3Button.vue';
import M3Checkbox from '@/Components/M3Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});


const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk - Portal Aset & Inventaris SMK Telkom Lampung" />

        <div class="my-auto space-y-6">
            <!-- Header Title & Subtitle (Telkom Schools Identity) -->
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold tracking-wide">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    <span>Portal Resmi SIM-ASET</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-surface-foreground tracking-tight">
                    Masuk ke Sistem Aset
                </h2>
                <p class="text-xs sm:text-sm text-surface-on-variant leading-relaxed">
                    Silakan masukkan email dan kata sandi akun petugas/administrator <strong>SMK Telkom Lampung</strong>.
                </p>
            </div>

            <!-- Status Notification Message -->
            <div
                v-if="status"
                class="p-3.5 rounded-2xl bg-emerald-50 text-emerald-900 text-xs font-semibold flex items-center gap-2 border border-emerald-200"
            >
                <span class="material-symbols-outlined text-[18px] text-emerald-700">check_circle</span>
                <span>{{ status }}</span>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" autocomplete="off" class="space-y-3.5">
                <!-- Email Field (No Autofill) -->
                <M3TextField
                    id="email"
                    name="user_email_address"
                    type="email"
                    v-model="form.email"
                    label="Alamat Email Resmi"
                    leading-icon="mail"
                    required
                    autocomplete="off"
                    data-lpignore="true"
                    data-form-type="other"
                    :error-message="form.errors.email"
                />

                <!-- Password Field (No Autofill) -->
                <M3TextField
                    id="password"
                    name="user_account_password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    label="Kata Sandi"
                    leading-icon="lock"
                    :trailing-icon="showPassword ? 'visibility_off' : 'visibility'"
                    required
                    autocomplete="new-password"
                    data-lpignore="true"
                    data-form-type="other"
                    :error-message="form.errors.password"
                    @click-trailing-icon="showPassword = !showPassword"
                />

                <!-- Remember Me & Forgot Password Row -->
                <div class="flex items-center justify-between pt-0.5">
                    <M3Checkbox id="remember-me" v-model="form.remember">
                        <span class="text-xs sm:text-sm text-surface-foreground font-medium">Ingat akun saya</span>
                    </M3Checkbox>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-bold text-primary hover:underline hover:text-primary-hover transition-colors"
                    >
                        Lupa kata sandi?
                    </Link>
                </div>

                <!-- Submit Button -->
                <div class="pt-1.5">
                    <M3Button
                        type="submit"
                        variant="filled"
                        size="large"
                        full-width
                        :loading="form.processing"
                        icon="login"
                    >
                        {{ form.processing ? 'Memverifikasi...' : 'Masuk ke Aplikasi' }}
                    </M3Button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
