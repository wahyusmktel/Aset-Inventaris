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
        <Head title="Masuk - Material Design 3" />

        <div class="my-auto space-y-6">
            <!-- Header Title & Subtitle -->
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    <span>Autentikasi Aman</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-surface-foreground tracking-tight">
                    Selamat Datang!
                </h2>
                <p class="text-xs sm:text-sm text-surface-on-variant">
                    Silakan masukkan email dan kata sandi untuk masuk ke dashboard.
                </p>
            </div>

            <!-- Status Notification Message -->
            <div
                v-if="status"
                class="p-3 rounded-2xl bg-primary-container/80 backdrop-blur-sm text-primary-on-container text-xs font-medium flex items-center gap-2 border border-primary-container"
            >
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
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
                    label="Alamat Email"
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
                        <span class="text-xs sm:text-sm text-surface-foreground font-medium">Ingat saya</span>
                    </M3Checkbox>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-semibold text-primary hover:underline hover:text-primary-hover transition-colors"
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
                        {{ form.processing ? 'Memverifikasi...' : 'Masuk ke Akun' }}
                    </M3Button>
                </div>

                <!-- Divider -->
                <div class="relative py-3 flex items-center justify-center">
                    <div class="w-full border-t border-outline-variant/60"></div>
                    <span class="absolute bg-white/90 backdrop-blur-md px-3 text-[11px] text-surface-on-variant font-medium select-none rounded-full">
                        atau lanjutkan dengan
                    </span>
                </div>

                <!-- Social Logins (Translucent Outlined Buttons) -->
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="h-11 px-4 rounded-full border border-outline-variant/80 bg-white/60 hover:bg-white/90 hover:border-primary/50 active:bg-primary/5 flex items-center justify-center gap-2 text-xs font-semibold text-surface-foreground transition-all duration-200 shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 10.03 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                        </svg>
                        <span>Google</span>
                    </button>

                    <button
                        type="button"
                        class="h-11 px-4 rounded-full border border-outline-variant/80 bg-white/60 hover:bg-white/90 hover:border-primary/50 active:bg-primary/5 flex items-center justify-center gap-2 text-xs font-semibold text-surface-foreground transition-all duration-200 shadow-sm cursor-pointer"
                    >
                        <svg class="w-4 h-4 fill-current text-surface-foreground" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                        <span>GitHub</span>
                    </button>
                </div>

                <!-- Footer: Register Link -->
                <div class="pt-3 text-center">
                    <p class="text-xs text-surface-on-variant">
                        Belum punya akun?
                        <Link
                            :href="route('register')"
                            class="text-primary font-bold hover:underline ml-1"
                        >
                            Daftar sekarang
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
