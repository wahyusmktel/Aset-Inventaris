<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import M3TextField from '@/Components/M3TextField.vue';
import M3Button from '@/Components/M3Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Pemulihan Kata Sandi - Material Design 3" />

        <div class="mb-6 text-center sm:text-left">
            <h2 class="text-2xl font-bold text-surface-foreground tracking-tight">
                Pemulihan Kata Sandi
            </h2>
            <p class="text-sm text-surface-on-variant mt-1 leading-relaxed">
                Lupa kata sandi? Masukkan email Anda dan kami akan mengirimkan tautan reset kata sandi.
            </p>
        </div>

        <!-- Status Message -->
        <div
            v-if="status"
            class="mb-4 p-3 rounded-m3-sm bg-primary-container text-primary-on-container text-xs font-medium flex items-center gap-2"
        >
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Field -->
            <M3TextField
                id="email"
                type="email"
                v-model="form.email"
                label="Alamat Email"
                leading-icon="mail"
                required
                autofocus
                autocomplete="username"
                :error-message="form.errors.email"
            />

            <!-- Submit Button -->
            <div class="pt-2">
                <M3Button
                    type="submit"
                    variant="filled"
                    size="large"
                    full-width
                    :loading="form.processing"
                    icon="send"
                >
                    {{ form.processing ? 'Mengirim...' : 'Kirim Tautan Reset' }}
                </M3Button>
            </div>

            <!-- Back to Login Link -->
            <div class="pt-3 text-center">
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:underline"
                >
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Kembali ke halaman Masuk</span>
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
