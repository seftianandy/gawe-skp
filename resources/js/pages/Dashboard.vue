<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart3, CalendarRange, CheckCircle2, FileText, ArrowRight, TrendingUp, Clock } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { index as laporanIndex, show as laporanShow } from '@/routes/laporan';

const props = defineProps<{
    summary: {
        jumlah_laporan: number;
        laporan_bulan_berjalan: number;
        status: {
            draft: number;
            submit: number;
            final: number;
        };
    };
    laporanTerbaru: Array<{
        id: number;
        periode: string;
        bulan: number;
        tahun: number;
        status: string;
    }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const totalLaporan = computed(() =>
    props.summary.status.draft + props.summary.status.submit + props.summary.status.final,
);

const finalPct = computed(() =>
    totalLaporan.value > 0
        ? Math.round((props.summary.status.final / totalLaporan.value) * 100)
        : 0,
);

const statusColor = (status: string) => {
    const s = status?.toLowerCase();
    if (s === 'final')  return 'text-emerald-600 bg-emerald-50 border-emerald-200';
    if (s === 'submit') return 'text-sky-600 bg-sky-50 border-sky-200';
    if (s === 'draft')  return 'text-amber-600 bg-amber-50 border-amber-200';
    return 'text-slate-600 bg-slate-50 border-slate-200';
};

const formatPeriode = (periode: string) =>
    new Date(periode).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 animate-fade-in">

        <!-- ── Hero Header ─────────────────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 shadow-lg">
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:32px_32px]" />
            <div class="pointer-events-none absolute -top-20 -right-20 h-60 w-60 rounded-full bg-primary/10 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-amber-500/10 blur-3xl" />

            <div class="relative space-y-2">
                <div class="flex items-center gap-2 text-slate-400">
                    <TrendingUp class="size-4" />
                    <span class="text-xs font-medium uppercase tracking-widest">Sistem Informasi Kinerja ASN</span>
                </div>
                <h1 class="text-2xl font-bold text-white">Dashboard SKP</h1>
                <p class="text-sm text-slate-400">
                    Pantau progres laporan bulanan, status penyelesaian, dan akses cepat ke laporan terbaru.
                </p>
            </div>
        </div>

        <!-- ── Stat Cards ──────────────────────────────────────────────── -->
        <div class="grid gap-3 md:grid-cols-3">
            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">Total Laporan</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary/10 text-primary group-hover:bg-primary/20 transition-colors">
                        <FileText class="size-3.5" />
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-foreground">{{ summary.jumlah_laporan }}</p>
                <p class="mt-1 text-xs text-muted-foreground">laporan SKP tercatat</p>
            </div>

            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">Bulan Berjalan</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600 group-hover:bg-sky-200 transition-colors">
                        <CalendarRange class="size-3.5" />
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-foreground">{{ summary.laporan_bulan_berjalan }}</p>
                <p class="mt-1 text-xs text-muted-foreground">laporan aktif periode ini</p>
            </div>

            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">Status Final</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 group-hover:bg-emerald-200 transition-colors">
                        <CheckCircle2 class="size-3.5" />
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-foreground">{{ summary.status.final }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ finalPct }}% dari total laporan
                </p>
            </div>
        </div>

        <!-- ── Main Content ────────────────────────────────────────────── -->
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">

            <!-- Ringkasan Status -->
            <div class="rounded-xl border border-border/60 bg-card shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-border/50 px-5 py-4">
                    <div>
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                            <BarChart3 class="size-4 text-muted-foreground" />
                            Ringkasan Status Laporan
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">Distribusi progres laporan SKP Anda.</p>
                    </div>
                    <Link
                        :href="laporanIndex()"
                        class="flex items-center gap-1 text-xs font-medium text-primary transition-opacity hover:opacity-70"
                    >
                        Kelola laporan
                        <ArrowRight class="size-3" />
                    </Link>
                </div>

                <div class="p-5 grid gap-4">
                    <!-- Progress bar keseluruhan -->
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-xs text-muted-foreground">
                            <span>Tingkat penyelesaian</span>
                            <span class="font-semibold text-foreground">{{ finalPct }}%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-emerald-500 transition-all duration-700"
                                :style="{ width: `${finalPct}%` }"
                            />
                        </div>
                    </div>

                    <!-- Tiga kotak status -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-xl border border-amber-200/60 bg-amber-50/60 p-4">
                            <div class="flex items-center gap-1.5 text-amber-600">
                                <Clock class="size-3.5" />
                                <p class="text-xs font-medium">Draft</p>
                            </div>
                            <p class="mt-2 text-2xl font-bold text-amber-700">{{ summary.status.draft }}</p>
                            <p class="mt-0.5 text-xs text-amber-600/70">
                                {{ totalLaporan > 0 ? Math.round((summary.status.draft / totalLaporan) * 100) : 0 }}% dari total
                            </p>
                        </div>
                        <div class="rounded-xl border border-sky-200/60 bg-sky-50/60 p-4">
                            <div class="flex items-center gap-1.5 text-sky-600">
                                <TrendingUp class="size-3.5" />
                                <p class="text-xs font-medium">Submit</p>
                            </div>
                            <p class="mt-2 text-2xl font-bold text-sky-700">{{ summary.status.submit }}</p>
                            <p class="mt-0.5 text-xs text-sky-600/70">
                                {{ totalLaporan > 0 ? Math.round((summary.status.submit / totalLaporan) * 100) : 0 }}% dari total
                            </p>
                        </div>
                        <div class="rounded-xl border border-emerald-200/60 bg-emerald-50/60 p-4">
                            <div class="flex items-center gap-1.5 text-emerald-600">
                                <CheckCircle2 class="size-3.5" />
                                <p class="text-xs font-medium">Final</p>
                            </div>
                            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ summary.status.final }}</p>
                            <p class="mt-0.5 text-xs text-emerald-600/70">
                                {{ finalPct }}% dari total
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Terbaru -->
            <div class="rounded-xl border border-border/60 bg-card shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-border/50 px-5 py-4">
                    <div>
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                            <FileText class="size-4 text-muted-foreground" />
                            Laporan Terbaru
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">Lima laporan yang terakhir dibuat.</p>
                    </div>
                </div>

                <div class="p-4 grid gap-2">
                    <!-- Empty state -->
                    <div
                        v-if="laporanTerbaru.length === 0"
                        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-10 text-center"
                    >
                        <FileText class="size-8 text-muted-foreground/30" />
                        <p class="mt-3 text-xs font-medium text-muted-foreground">Belum ada laporan</p>
                        <p class="mt-0.5 text-xs text-muted-foreground/70">Mulai dari membuat laporan bulanan pertama.</p>
                    </div>

                    <!-- Item -->
                    <div
                        v-for="(item, idx) in laporanTerbaru"
                        :key="item.id"
                        class="group flex items-center justify-between gap-3 rounded-xl border border-border/50 bg-background px-4 py-3 transition-all duration-150 hover:border-border hover:shadow-sm"
                        :style="{ animationDelay: `${idx * 50}ms` }"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                {{ item.bulan }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground">
                                    {{ formatPeriode(item.periode) }}
                                </p>
                                <p class="text-xs text-muted-foreground">{{ item.tahun }}</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span :class="['inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold capitalize', statusColor(item.status)]">
                                {{ item.status }}
                            </span>
                            <Link
                                :href="laporanShow(item.id)"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                                <ArrowRight class="size-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.35s ease both;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
