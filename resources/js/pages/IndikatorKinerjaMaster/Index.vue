<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { PencilLine, Plus, Trash2, Layers3, Target, BarChart3 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { create, destroy as destroyRoute, edit, index as indikatorKinerjaMasterIndex } from '@/routes/indikator-kinerja-master';
import type { IndikatorKinerjaMasterItem } from '@/types';

const props = defineProps<{
    indikatorKinerjaMasters: IndikatorKinerjaMasterItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Indikator Kinerja Master', href: indikatorKinerjaMasterIndex() },
        ],
    },
});

const totalKategori = computed(
    () => new Set(props.indikatorKinerjaMasters.map((i) => i.kategori)).size,
);

const kategoriGroups = computed(() => {
    const map = new Map<string, number>();
    for (const item of props.indikatorKinerjaMasters) {
        map.set(item.kategori, (map.get(item.kategori) ?? 0) + 1);
    }
    return map;
});

const kategoriColor = (kategori: string): string => {
    const map: Record<string, string> = {
        kualitas:  'bg-blue-100 text-blue-700 border-blue-200',
        kuantitas: 'bg-violet-100 text-violet-700 border-violet-200',
        waktu:     'bg-amber-100 text-amber-700 border-amber-200',
        biaya:     'bg-rose-100 text-rose-700 border-rose-200',
    };
    return map[kategori?.toLowerCase()] ?? 'bg-slate-100 text-slate-700 border-slate-200';
};

function handleDestroy(id: number): void {
    if (!window.confirm('Hapus indikator master ini?')) return;
    router.delete(destroyRoute(id).url, { preserveScroll: true });
}
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 animate-fade-in">

        <!-- ── Hero Header ─────────────────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 shadow-lg">
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:32px_32px]" />
            <div class="pointer-events-none absolute -top-20 -right-20 h-60 w-60 rounded-full bg-cyan-500/10 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-indigo-500/10 blur-3xl" />

            <div class="relative flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-slate-400">
                        <Layers3 class="size-4" />
                        <span class="text-xs font-medium uppercase tracking-widest">Master Data</span>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Indikator Kinerja Master</h1>
                    <p class="max-w-2xl text-sm text-slate-400">
                        Kelola daftar indikator yang dapat dipakai ulang saat membuat hasil kerja.
                    </p>
                </div>
                <Button class="shrink-0 bg-white font-semibold text-slate-900 shadow-md hover:bg-slate-100 transition-all duration-200" as-child>
                    <Link :href="create()">
                        <Plus class="mr-1.5 size-4" />
                        Tambah Indikator
                    </Link>
                </Button>
            </div>
        </div>

        <!-- ── Stat Cards ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <Layers3 class="size-4" />
                    <span class="text-xs font-medium">Total Indikator</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ indikatorKinerjaMasters.length }}</p>
            </div>
            <div class="rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <Target class="size-4" />
                    <span class="text-xs font-medium">Kategori</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ totalKategori }}</p>
            </div>
            <div class="col-span-2 rounded-xl border border-border/60 bg-card p-4 md:col-span-1">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <BarChart3 class="size-4" />
                    <span class="text-xs font-medium">Sebaran Kategori</span>
                </div>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span
                        v-for="[kat, count] in kategoriGroups"
                        :key="kat"
                        :class="['inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium capitalize', kategoriColor(kat)]"
                    >
                        {{ kat }} <span class="font-bold">{{ count }}</span>
                    </span>
                    <span v-if="indikatorKinerjaMasters.length === 0" class="text-xs italic text-muted-foreground">
                        Belum ada data.
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Indikator List ──────────────────────────────────────────── -->
        <div class="grid gap-2.5">

            <!-- Empty State -->
            <div
                v-if="indikatorKinerjaMasters.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card py-20 text-center"
            >
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-muted">
                    <Layers3 class="size-7 text-muted-foreground/40" />
                </div>
                <p class="mt-4 text-sm font-semibold text-foreground">Belum ada indikator master</p>
                <p class="mt-1 max-w-sm text-xs text-muted-foreground">
                    Tambahkan indikator master agar dapat dipilih saat membuat hasil kerja.
                </p>
                <Button class="mt-5" as-child>
                    <Link :href="create()">
                        <Plus class="mr-1.5 size-4" />
                        Tambah Indikator
                    </Link>
                </Button>
            </div>

            <!-- Item Cards -->
            <div
                v-for="(item, idx) in indikatorKinerjaMasters"
                :key="item.id"
                class="group relative overflow-hidden rounded-xl border border-border/60 bg-card transition-all duration-200 hover:shadow-md hover:border-border"
                :style="{ animationDelay: `${idx * 40}ms` }"
            >
                <!-- Accent bar kiri sesuai kategori -->
                <div
                    class="absolute left-0 top-0 h-full w-1 rounded-l-xl"
                    :class="{
                        'bg-blue-400':   item.kategori?.toLowerCase() === 'kualitas',
                        'bg-violet-400': item.kategori?.toLowerCase() === 'kuantitas',
                        'bg-amber-400':  item.kategori?.toLowerCase() === 'waktu',
                        'bg-rose-400':   item.kategori?.toLowerCase() === 'biaya',
                        'bg-slate-400':  !['kualitas','kuantitas','waktu','biaya'].includes(item.kategori?.toLowerCase()),
                    }"
                />

                <div class="flex flex-col gap-3 py-4 pl-5 pr-4 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Kiri: Konten -->
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <!-- Nomor urut -->
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary transition-colors duration-200">
                            {{ idx + 1 }}
                        </div>

                        <div class="min-w-0 flex-1 space-y-1.5">
                            <!-- Nama indikator -->
                            <p class="text-sm font-semibold leading-snug text-foreground">
                                {{ item.nama_indikator }}
                            </p>

                            <!-- Meta info -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span :class="['inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold capitalize', kategoriColor(item.kategori)]">
                                    {{ item.kategori }}
                                </span>
                                <span class="flex items-center gap-1 text-xs text-muted-foreground">
                                    <Target class="size-3" />
                                    Target
                                    <span class="font-semibold text-foreground">{{ item.target }}</span>
                                    {{ item.satuan }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Aksi -->
                    <div class="flex shrink-0 items-center gap-1.5 pl-10 lg:pl-0">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-8 px-3 text-xs hover:bg-muted transition-colors"
                            as-child
                        >
                            <Link :href="edit(item.id)">
                                <PencilLine class="mr-1 size-3.5" />
                                Edit
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-8 w-8 p-0 text-muted-foreground hover:bg-red-50 hover:text-red-600 transition-colors"
                            @click="handleDestroy(item.id)"
                        >
                            <Trash2 class="size-3.5" />
                        </Button>
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
