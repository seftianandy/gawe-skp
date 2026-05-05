<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: null,
});
</script>

<template>
    <Head title="Terms of Service" />

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-6 py-16">
            <Link :href="dashboard()" class="text-sm text-slate-400 transition hover:text-white">
                Kembali ke dashboard
            </Link>

            <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/20">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">Legal</p>
                <h1 class="mt-3 text-3xl font-bold text-white">Terms of Service</h1>
                <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300">
                    Layanan GaweSKP disediakan untuk membantu pengelolaan laporan SKP, pengunggahan lampiran PDF,
                    dan integrasi penyimpanan Google Drive. Dengan menggunakan layanan ini, Anda menyetujui
                    penggunaan data sesuai kebutuhan operasional aplikasi.
                </p>

                <div class="mt-8 grid gap-4 text-sm leading-7 text-slate-300">
                    <section class="rounded-2xl border border-white/8 bg-slate-900/60 p-5">
                        <h2 class="text-base font-semibold text-white">1. Penggunaan Layanan</h2>
                        <p class="mt-2">
                            Anda bertanggung jawab atas konten laporan, lampiran PDF, dan data yang diunggah ke akun Anda.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-white/8 bg-slate-900/60 p-5">
                        <h2 class="text-base font-semibold text-white">2. Integrasi Google</h2>
                        <p class="mt-2">
                            Saat menghubungkan Google Drive, aplikasi hanya meminta akses yang diperlukan untuk mengunggah file laporan dan lampiran.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-white/8 bg-slate-900/60 p-5">
                        <h2 class="text-base font-semibold text-white">3. Konten Pengguna</h2>
                        <p class="mt-2">
                            Anda menyatakan memiliki hak untuk mengunggah file yang disimpan di sistem, termasuk PDF laporan dan lampiran pendukung.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
