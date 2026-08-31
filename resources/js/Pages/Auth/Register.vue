<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import M3TextField from '@/Components/M3TextField.vue';
import M3Button from '@/Components/M3Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun Baru - Material Design 3" />

        <div class="mb-6 text-center sm:text-left">
            <h2 class="text-2xl font-bold text-surface-foreground tracking-tight">
                Buat Akun Baru
            </h2>
            <p class="text-sm text-surface-on-variant mt-1">
                Lengkapi data diri Anda untuk memulai pengalaman bersama kami.
            </p>
        </div>

        <form @submit.prevent="submit" autocomplete="off" class="space-y-3">
            <!-- Name Input -->
            <M3TextField
                id="name"
                name="user_fullname"
                type="text"
                v-model="form.name"
                label="Nama Lengkap"
                leading-icon="badge"
                required
                autofocus
                autocomplete="off"
                data-lpignore="true"
                :error-message="form.errors.name"
            />

            <!-- Email Input -->
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
                :error-message="form.errors.email"
            />

            <!-- Password Input -->
            <M3TextField
                id="password"
                name="user_new_password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                label="Kata Sandi"
                leading-icon="lock"
                :trailing-icon="showPassword ? 'visibility_off' : 'visibility'"
                required
                autocomplete="new-password"
                data-lpignore="true"
                :error-message="form.errors.password"
                @click-trailing-icon="showPassword = !showPassword"
            />

            <!-- Confirm Password Input -->
            <M3TextField
                id="password_confirmation"
                name="user_confirm_password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password_confirmation"
                label="Konfirmasi Kata Sandi"
                leading-icon="lock_reset"
                required
                autocomplete="new-password"
                data-lpignore="true"
                :error-message="form.errors.password_confirmation"
            />

            <!-- Submit Button -->
            <div class="pt-2">
                <M3Button
                    type="submit"
                    variant="filled"
                    size="large"
                    full-width
                    :loading="form.processing"
                    icon="person_add"
                >
                    {{ form.processing ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                </M3Button>
            </div>

            <!-- Footer: Login Link -->
            <div class="pt-4 text-center">
                <p class="text-xs text-surface-on-variant">
                    Sudah memiliki akun?
                    <Link
                        :href="route('login')"
                        class="text-primary font-semibold hover:underline ml-1"
                    >
                        Masuk sekarang
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
