<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: null,
});
</script>

<template>
    <Head title="Privacy Policy" />

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-6 py-16">
            <Link :href="dashboard()" class="text-sm text-slate-400 transition hover:text-white">
                Kembali ke dashboard
            </Link>

            <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/20">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">Legal</p>
                <h1 class="mt-3 text-3xl font-bold text-white">Privacy Policy</h1>
                <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300">
                    Kami menyimpan data seperlunya untuk menjalankan fitur inti aplikasi, termasuk akun pengguna,
                    laporan SKP, lampiran PDF, dan token integrasi Google Drive yang dibutuhkan untuk proses upload.
                </p>

                <div class="mt-8 grid gap-4 text-sm leading-7 text-slate-300">
                    <section class="rounded-2xl border border-white/8 bg-slate-900/60 p-5">
                        <h2 class="text-base font-semibold text-white">1. Data yang Diproses</h2>
                        <p class="mt-2">
                            Aplikasi dapat memproses nama, email, data laporan, lampiran PDF, serta metadata file untuk kebutuhan penyimpanan dan ekspor.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-white/8 bg-slate-900/60 p-5">
                        <h2 class="text-base font-semibold text-white">2. Akses Google Drive</h2>
                        <p class="mt-2">
                            Token OAuth Google disimpan untuk mengunggah file ke Google Drive milik pengguna sesuai izin yang diberikan.
                        </p>
                    </section>

                    <section class="rounded-2xl border border-white/8 bg-slate-900/60 p-5">
                        <h2 class="text-base font-semibold text-white">3. Penyimpanan File</h2>
                        <p class="mt-2">
                            File PDF disimpan di storage aplikasi dan dapat dipindahkan ke Google Drive. Lampiran tetap terpisah dari PDF laporan utama.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
