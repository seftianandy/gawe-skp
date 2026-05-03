<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Sparkles, BarChart3, ShieldCheck, ArrowRight, CheckCircle2, ClipboardList, Activity } from 'lucide-vue-next';
import { dashboard, login, register } from '@/routes';
import AppLogo from '@/components/AppLogo.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const features = [
    {
        icon: ClipboardList,
        title: 'Hasil Kerja Terstruktur',
        desc: 'Kelola hasil kerja, indikator kinerja, rencana aksi, dan realisasi dalam satu tempat yang tertata rapi.',
    },
    {
        icon: Sparkles,
        title: 'Narasi AI Otomatis',
        desc: 'Generate narasi laporan kinerja formal berstandar EYD secara otomatis menggunakan kecerdasan buatan.',
    },
    {
        icon: Activity,
        title: 'Perilaku Kerja',
        desc: 'Dokumentasikan perilaku kerja beserta bukti foto pendukung sesuai nilai organisasi.',
    },
    {
        icon: BarChart3,
        title: 'Pantau Progres',
        desc: 'Dashboard ringkas menampilkan distribusi status laporan dan capaian kinerja secara real-time.',
    },
    {
        icon: FileText,
        title: 'Ekspor PDF & Google Drive',
        desc: 'Unduh laporan SKP dalam format PDF formal siap cetak, atau unggah langsung ke Google Drive Anda hanya dengan satu klik.',
    },
    {
        icon: ShieldCheck,
        title: 'Data Aman & Terkelola',
        desc: 'Setiap laporan tersimpan aman dan dapat diakses kembali setiap saat sesuai periode.',
    },
];
</script>

<template>
    <Head title="Sistem Informasi Kinerja ASN" />

    <div class="min-h-screen bg-[#0a0a0a] text-white antialiased">

        <!-- ── Navbar ──────────────────────────────────────────────────── -->
        <header class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-[#0a0a0a]/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10">
                        <AppLogo />
                    </div>
                    <span class="text-sm font-semibold tracking-tight text-white">Gawe<span class="text-slate-400">SKP</span></span>
                </div>

                <nav class="flex items-center gap-2">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard()"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-white px-4 py-1.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                        >
                            Dashboard
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-lg px-4 py-1.5 text-sm font-medium text-slate-400 transition hover:text-white"
                        >
                            Masuk
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-white px-4 py-1.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                        >
                            Daftar Sekarang
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- ── Hero ───────────────────────────────────────────────────── -->
        <section class="relative overflow-hidden pt-32 pb-24">
            <!-- Background grid -->
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]" />
            <!-- Glow blobs -->
            <div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 h-[500px] w-[800px] rounded-full bg-blue-600/10 blur-[120px]" />
            <div class="pointer-events-none absolute top-40 -right-40 h-80 w-80 rounded-full bg-indigo-500/10 blur-[100px]" />
            <div class="pointer-events-none absolute top-40 -left-40 h-80 w-80 rounded-full bg-cyan-500/10 blur-[100px]" />

            <div class="relative mx-auto max-w-4xl px-6 text-center">
                <!-- Badge -->
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-slate-400">
                    <Sparkles class="size-3 text-blue-400" />
                    Sistem Informasi Kinerja ASN
                </div>

                <h1 class="text-4xl font-bold tracking-tight text-white md:text-6xl">
                    Laporan SKP yang
                    <span class="bg-gradient-to-r from-[#42d392] via-[#35c47a] to-[#00bd7e] bg-clip-text text-transparent"> lebih cepat</span>,<br>lebih rapi, lebih akurat.
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-slate-400">
                    Kelola hasil kerja, indikator kinerja, dan perilaku kerja ASN dalam satu platform terintegrasi.
                    Narasi laporan formal dihasilkan otomatis oleh AI sesuai kaidah EYD.
                </p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-white/10 transition hover:bg-slate-100"
                    >
                        Buka Dashboard
                        <ArrowRight class="size-4" />
                    </Link>
                    <template v-else>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-white/10 transition hover:bg-slate-100"
                        >
                            Mulai Gratis
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="login()"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-medium text-white transition hover:bg-white/10"
                        >
                            Masuk ke Akun
                        </Link>
                    </template>
                </div>

                <!-- Social proof pill -->
                <div class="mt-8 flex items-center justify-center gap-2 text-xs text-slate-500">
                    <CheckCircle2 class="size-3.5 text-emerald-500" />
                    Bebas iklan &nbsp;·&nbsp;
                    <CheckCircle2 class="size-3.5 text-emerald-500" />
                    Data tersimpan aman &nbsp;·&nbsp;
                    <CheckCircle2 class="size-3.5 text-emerald-500" />
                    Ekspor PDF kapan saja
                </div>
            </div>
        </section>

        <!-- ── Preview Card (mock UI) ──────────────────────────────────── -->
        <section class="mx-auto max-w-5xl px-6 pb-24">
            <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-gradient-to-b from-slate-800/60 to-slate-900/60 p-6 shadow-2xl shadow-black/40 backdrop-blur-sm">
                <!-- fake browser bar -->
                <div class="mb-4 flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-red-500/60" />
                    <div class="h-2.5 w-2.5 rounded-full bg-amber-500/60" />
                    <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/60" />
                    <div class="ml-3 flex-1 rounded-md bg-white/5 px-3 py-1 text-xs text-slate-500">
                        skp.instansi.go.id/dashboard
                    </div>
                </div>

                <!-- mock stat cards -->
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div v-for="(stat, i) in [
                        { label: 'Total Laporan', val: '12', color: 'text-blue-400' },
                        { label: 'Bulan Berjalan', val: '3', color: 'text-sky-400' },
                        { label: 'Status Final', val: '9', color: 'text-emerald-400' },
                    ]" :key="i" class="rounded-xl border border-white/5 bg-white/4 p-3">
                        <p class="text-xs text-slate-500">{{ stat.label }}</p>
                        <p :class="['mt-1 text-2xl font-bold', stat.color]">{{ stat.val }}</p>
                    </div>
                </div>

                <!-- mock laporan list -->
                <div class="space-y-2">
                    <div v-for="(row, i) in [
                        { periode: 'April 2026', status: 'Final', color: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' },
                        { periode: 'Maret 2026', status: 'Submit', color: 'text-sky-400 bg-sky-500/10 border-sky-500/20' },
                        { periode: 'Februari 2026', status: 'Draft', color: 'text-amber-400 bg-amber-500/10 border-amber-500/20' },
                    ]" :key="i"
                        class="flex items-center justify-between rounded-xl border border-white/5 bg-white/3 px-4 py-2.5"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-7 w-7 rounded-lg bg-white/5 flex items-center justify-center">
                                <FileText class="size-3.5 text-slate-400" />
                            </div>
                            <span class="text-sm font-medium text-slate-300">{{ row.periode }}</span>
                        </div>
                        <span :class="['rounded-full border px-2 py-0.5 text-xs font-semibold', row.color]">
                            {{ row.status }}
                        </span>
                    </div>
                </div>

                <!-- glow overlay -->
                <div class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-t from-[#0a0a0a]/60 via-transparent to-transparent" />
            </div>
        </section>

        <!-- ── Features ───────────────────────────────────────────────── -->
        <section class="mx-auto max-w-6xl px-6 pb-24">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold text-white md:text-3xl">Semua yang Anda butuhkan</h2>
                <p class="mt-3 text-sm text-slate-400">Dirancang khusus untuk kebutuhan pelaporan kinerja ASN yang efisien.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(feat, i) in features"
                    :key="i"
                    class="group rounded-2xl border border-white/8 bg-white/3 p-5 transition-all duration-200 hover:border-white/15 hover:bg-white/5"
                >
                    <div class="mb-4 flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/8 text-blue-400 group-hover:bg-blue-500/15 transition-colors">
                        <component :is="feat.icon" class="size-4" />
                    </div>
                    <h3 class="mb-1.5 text-sm font-semibold text-white">{{ feat.title }}</h3>
                    <p class="text-xs leading-relaxed text-slate-500">{{ feat.desc }}</p>
                </div>
            </div>
        </section>

        <!-- ── CTA ────────────────────────────────────────────────────── -->
        <section class="mx-auto max-w-6xl px-6 pb-24">
            <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-gradient-to-br from-slate-800/60 to-slate-900/60 p-10 text-center">
                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:32px_32px]" />
                <div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 h-40 w-96 rounded-full bg-blue-500/10 blur-3xl" />

                <div class="relative">
                    <h2 class="text-2xl font-bold text-white md:text-3xl">Siap memulai?</h2>
                    <p class="mt-3 text-sm text-slate-400">
                        Buat laporan SKP pertama Anda hari ini — gratis, tanpa instalasi.
                    </p>
                    <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                        >
                            Buka Dashboard <ArrowRight class="size-4" />
                        </Link>
                        <template v-else>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                            >
                                Daftar Sekarang <ArrowRight class="size-4" />
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-medium text-white transition hover:bg-white/10"
                            >
                                Masuk
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Footer ─────────────────────────────────────────────────── -->
        <footer class="border-t border-white/5 py-8">
            <div class="mx-auto max-w-6xl px-6 flex flex-col items-center justify-between gap-3 md:flex-row">
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <div class="flex h-5 w-5 items-center justify-center rounded-md bg-white/8">
                        <AppLogo />
                    </div>
                    GaweSKP — Generate Laporan SKP ASN dengan AI
                </div>
                <p class="text-xs text-slate-600">
                    Dibangun oleh TIM IT
                </p>
            </div>
        </footer>

    </div>
</template>
