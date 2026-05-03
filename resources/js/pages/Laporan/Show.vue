<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { PencilLine, Plus, Sparkles, Trash2, FileDown, ChevronDown, Activity, ClipboardList, User, Calendar, Building2, BadgeCheck, Upload } from 'lucide-vue-next';
import ExportLaporanPdfController from '@/actions/App/Http/Controllers/ExportLaporanPdfController';
import GenerateLaporanAiController from '@/actions/App/Http/Controllers/GenerateLaporanAiController';
import GenerateHasilKerjaAiController from '@/actions/App/Http/Controllers/GenerateHasilKerjaAiController';
import GeneratePerilakuAiController from '@/actions/App/Http/Controllers/GeneratePerilakuAiController';
import HasilKerjaController from '@/actions/App/Http/Controllers/HasilKerjaController';
import HasilKerjaPdfController from '@/actions/App/Http/Controllers/HasilKerjaPdfController';
import LaporanController from '@/actions/App/Http/Controllers/LaporanController';
import PerilakuKerjaController from '@/actions/App/Http/Controllers/PerilakuKerjaController';
import PerilakuPdfController from '@/actions/App/Http/Controllers/PerilakuPdfController';
import HasilKerjaModal from '@/components/laporan/HasilKerjaModal.vue';
import PerilakuKerjaModal from '@/components/laporan/PerilakuKerjaModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { edit as laporanEdit, index as laporanIndex } from '@/routes/laporan';
import type { HasilKerjaForm, IndikatorKinerjaMasterItem, LaporanDetail, PerilakuKerjaForm, SelectOption } from '@/types';

const props = defineProps<{
    laporan: LaporanDetail;
    statusOptions: SelectOption[];
    indikatorKinerjaMasters: IndikatorKinerjaMasterItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Laporan', href: laporanIndex() },
            { title: 'Detail', href: laporanIndex() },
        ],
    },
});

const hasilKerjaOpen = ref(false);
const perilakuOpen = ref(false);
const selectedHasilKerja = ref<HasilKerjaForm | null>(null);
const selectedPerilaku = ref<PerilakuKerjaForm | null>(null);
const expandedHasilKerja = ref<Set<number>>(new Set());

const expandedPerilaku = ref<Set<number>>(new Set());

const aiForm = useForm({});
const uploadAllForm = useForm({});
const generatingHasilKerjaId = ref<number | null>(null);
const generatingPerilakuId = ref<number | null>(null);
const uploadingHasilKerjaId = ref<number | null>(null);
const uploadingPerilakuId = ref<number | null>(null);

const periodLabel = computed(() =>
    new Date(props.laporan.periode).toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric',
    }),
);

const totalIndikator = computed(() =>
    props.laporan.hasil_kerja.reduce((sum, hk) => sum + (hk.indikators?.length ?? 0), 0),
);

const totalRealisasi = computed(() =>
    props.laporan.hasil_kerja.reduce((sum, hk) =>
        sum + (hk.indikators?.reduce((s, ind) => s + (ind.realisasi?.length ?? 0), 0) ?? 0), 0),
);

const statusColor = computed(() => {
    const s = props.laporan.status?.toLowerCase();
    if (s === 'final') return 'text-emerald-600 bg-emerald-50 border-emerald-200';
    if (s === 'draft') return 'text-amber-600 bg-amber-50 border-amber-200';
    return 'text-blue-600 bg-blue-50 border-blue-200';
});

function toggleHasilKerja(id: number): void {
    if (expandedHasilKerja.value.has(id)) {
        expandedHasilKerja.value.delete(id);
    } else {
        expandedHasilKerja.value.add(id);
    }
}

function togglePerilaku(id: number): void {
    if (expandedPerilaku.value.has(id)) {
        expandedPerilaku.value.delete(id);
    } else {
        expandedPerilaku.value.add(id);
    }
}

function openHasilKerjaModal(item?: HasilKerjaForm): void {
    selectedHasilKerja.value = item ?? null;
    hasilKerjaOpen.value = true;
}

function namaIndikator(item: HasilKerjaForm): string {
    return item.indikator_kinerja_master?.nama_indikator
        ?? item.indikators?.[0]?.deskripsi
        ?? 'Hasil kerja';
}

function openPerilakuModal(item?: PerilakuKerjaForm): void {
    selectedPerilaku.value = item ?? null;
    perilakuOpen.value = true;
}

function destroyHasilKerja(item: HasilKerjaForm): void {
    if (!window.confirm('Hapus hasil kerja ini?')) return;
    const action = HasilKerjaController.destroy({ laporan: props.laporan.id, hasil_kerja: item.id as number });
    router.delete(action.url, { preserveScroll: true });
}

function destroyPerilaku(item: PerilakuKerjaForm): void {
    if (!window.confirm('Hapus perilaku kerja ini?')) return;
    const action = PerilakuKerjaController.destroy({ laporan: props.laporan.id, perilaku_kerja: item.id as number });
    router.delete(action.url, { preserveScroll: true });
}

function handleGenerateAi(): void {
    const action = GenerateLaporanAiController(props.laporan.id);
    aiForm.submit(action.method, action.url, { preserveScroll: true });
}

function handleExportPdf(): void {
    const action = ExportLaporanPdfController(props.laporan.id);
    window.location.href = action.url;
}

function uploadAllToDrive(): void {
    const action = LaporanController.uploadAllToDrive(props.laporan.id);
    uploadAllForm.submit(action.method, action.url, { preserveScroll: true });
}

function generateHasilKerja(item: HasilKerjaForm): void {
    if (!item.id) return;
    generatingHasilKerjaId.value = item.id;
    const action = GenerateHasilKerjaAiController(item.id);
    router.post(action.url, {}, { preserveScroll: true, onFinish: () => { generatingHasilKerjaId.value = null; } });
}

function exportHasilKerjaPdf(item: HasilKerjaForm): void {
    if (!item.id) return;
    const action = HasilKerjaPdfController.export(item.id);
    window.location.href = action.url;
}

function uploadHasilKerjaToDrive(item: HasilKerjaForm): void {
    if (!item.id) return;
    uploadingHasilKerjaId.value = item.id;
    const action = HasilKerjaPdfController.upload(item.id);
    router.post(action.url, {}, { preserveScroll: true, onFinish: () => { uploadingHasilKerjaId.value = null; } });
}

function generatePerilaku(item: PerilakuKerjaForm): void {
    if (!item.id) return;
    generatingPerilakuId.value = item.id;
    const action = GeneratePerilakuAiController(item.id);
    router.post(action.url, {}, { preserveScroll: true, onFinish: () => { generatingPerilakuId.value = null; } });
}

function exportPerilakuPdf(item: PerilakuKerjaForm): void {
    if (!item.id) return;
    const action = PerilakuPdfController.export(item.id);
    window.location.href = action.url;
}

function uploadPerilakuToDrive(item: PerilakuKerjaForm): void {
    if (!item.id) return;
    uploadingPerilakuId.value = item.id;
    const action = PerilakuPdfController.upload(item.id);
    router.post(action.url, {}, { preserveScroll: true, onFinish: () => { uploadingPerilakuId.value = null; } });
}
</script>

<template>
    <Head :title="`Laporan ${periodLabel}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 animate-fade-in">

        <!-- ── Hero Header ─────────────────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 shadow-lg">
            <!-- decorative grid -->
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:32px_32px]" />
            <!-- accent glow -->
            <div class="pointer-events-none absolute -top-20 -right-20 h-60 w-60 rounded-full bg-blue-500/10 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-indigo-500/10 blur-3xl" />

            <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div class="space-y-3">
                    <span :class="['inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold tracking-wide', statusColor]">
                        <BadgeCheck class="size-3.5" />
                        {{ laporan.status?.toUpperCase() }}
                    </span>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Laporan {{ laporan.periode_label }}</h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Kelola hasil kerja, indikator kinerja, rencana aksi, realisasi, dan perilaku kerja.
                        </p>
                    </div>
                </div>
                <!-- Gunakan justify-end agar semua item di dalamnya menempel ke kanan -->
                <div class="flex flex-wrap justify-end gap-2 lg:max-w-xl">
                    <Button variant="outline" size="sm" class="border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white transition-all duration-200" as-child>
                        <Link :href="laporanEdit(laporan.id)">
                            <PencilLine class="size-3.5 mr-1.5" />
                            Edit Laporan
                        </Link>
                    </Button>
                    <Button size="sm" class="bg-blue-500 text-white hover:bg-blue-400 transition-all duration-200 shadow-md shadow-blue-500/20" @click="handleGenerateAi" :disabled="aiForm.processing">
                        <Sparkles class="size-3.5 mr-1.5" :class="{ 'animate-spin': aiForm.processing }" />
                        {{ aiForm.processing ? 'Generating...' : 'Generate Laporan' }}
                    </Button>
                    <Button size="sm" variant="outline" class="border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white transition-all duration-200" @click="handleExportPdf">
                        <FileDown class="size-3.5 mr-1.5" />
                        Export PDF
                    </Button>
                    <Button size="sm" variant="outline" class="border-white/20 bg-white/5 text-white hover:bg-white/10 hover:text-white transition-all duration-200" @click="uploadAllToDrive" :disabled="uploadAllForm.processing">
                        <Upload class="size-3.5 mr-1.5" />
                        {{ uploadAllForm.processing ? 'Uploading...' : 'Upload Semua ke Drive' }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- ── Stat Cards ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <ClipboardList class="size-4" />
                    <span class="text-xs font-medium">Hasil Kerja</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ laporan.hasil_kerja.length }}</p>
            </div>
            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <Activity class="size-4" />
                    <span class="text-xs font-medium">Total Indikator</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ totalIndikator }}</p>
            </div>
            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <BadgeCheck class="size-4" />
                    <span class="text-xs font-medium">Total Realisasi</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ totalRealisasi }}</p>
            </div>
            <div class="group rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <User class="size-4" />
                    <span class="text-xs font-medium">Perilaku Kerja</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ laporan.perilaku_kerja.length }}</p>
            </div>
        </div>

        <!-- ── Info + Narasi AI ────────────────────────────────────────── -->
        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <!-- Informasi Laporan -->
            <Card class="border-border/60 shadow-sm">
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-sm font-semibold">
                        <User class="size-4 text-muted-foreground" />
                        Informasi Pegawai
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div class="flex items-start gap-3 rounded-lg bg-muted/40 p-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <User class="size-4" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Nama Pegawai</p>
                            <p class="text-sm font-semibold">{{ laporan.user.nama }}</p>
                            <p class="text-xs text-muted-foreground">NIP {{ laporan.user.nip }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg bg-muted/40 p-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <Building2 class="size-4" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Instansi</p>
                            <p class="text-sm font-semibold">{{ laporan.user.nama_instansi }}</p>
                            <p class="text-xs text-muted-foreground">{{ laporan.user.jabatan }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg bg-muted/40 p-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <Calendar class="size-4" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Periode</p>
                            <p class="text-sm font-semibold">{{ laporan.periode_label }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Narasi AI -->
            <Card class="border-border/60 shadow-sm">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="flex items-center gap-2 text-sm font-semibold">
                            <Sparkles class="size-4 text-blue-500" />
                            Narasi Laporan AI
                        </CardTitle>
                        <Button
                            v-if="laporan.isi_laporan"
                            variant="ghost"
                            size="sm"
                            class="h-7 text-xs text-muted-foreground"
                            @click="handleGenerateAi"
                            :disabled="aiForm.processing"
                        >
                            Regenerate
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="laporan.isi_laporan"
                        class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50/60 to-indigo-50/40 p-4 text-sm leading-7 text-slate-700 whitespace-pre-line"
                    >
                        {{ laporan.isi_laporan }}
                    </div>
                    <div
                        v-else
                        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-10 text-center"
                    >
                        <Sparkles class="mb-2 size-8 text-muted-foreground/40" />
                        <p class="text-sm font-medium text-muted-foreground">Narasi belum tersedia</p>
                        <p class="mt-1 text-xs text-muted-foreground/70">Klik Generate Laporan untuk membuat narasi AI</p>
                        <Button size="sm" class="mt-4" @click="handleGenerateAi" :disabled="aiForm.processing">
                            <Sparkles class="size-3.5 mr-1.5" />
                            Generate Sekarang
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- ── Hasil Kerja Utama ───────────────────────────────────────── -->
        <Card class="border-border/60 shadow-sm">
            <CardHeader class="flex flex-row items-center justify-between gap-4 pb-3">
                <div>
                    <CardTitle class="flex items-center gap-2 text-sm font-semibold">
                        <ClipboardList class="size-4 text-muted-foreground" />
                        Hasil Kerja Utama
                    </CardTitle>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ laporan.hasil_kerja.length }} hasil kerja · {{ totalIndikator }} indikator · {{ totalRealisasi }} realisasi
                    </p>
                </div>
                <Button size="sm" @click="openHasilKerjaModal()">
                    <Plus class="size-3.5 mr-1.5" />
                    Tambah
                </Button>
            </CardHeader>
            <CardContent class="grid gap-3">
                <!-- Empty state -->
                <div
                    v-if="laporan.hasil_kerja.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-14 text-center"
                >
                    <ClipboardList class="mb-2 size-10 text-muted-foreground/30" />
                    <p class="text-sm font-medium text-muted-foreground">Belum ada hasil kerja</p>
                    <p class="mt-1 text-xs text-muted-foreground/70">Tambahkan hasil kerja utama untuk periode ini</p>
                    <Button size="sm" variant="outline" class="mt-4" @click="openHasilKerjaModal()">
                        <Plus class="size-3.5 mr-1.5" />
                        Tambah Hasil Kerja
                    </Button>
                </div>

                <!-- Hasil Kerja Cards -->
                <div
                    v-for="(item, idx) in laporan.hasil_kerja"
                :key="item.id ?? `hasil-${namaIndikator(item)}`"
                    class="overflow-hidden rounded-xl border border-border/60 bg-card transition-all duration-300"
                    :style="{ animationDelay: `${idx * 60}ms` }"
                >
                    <!-- Card Header -->
                    <div
                        class="flex cursor-pointer select-none items-start justify-between gap-4 p-4 transition-colors duration-150 hover:bg-muted/30"
                        @click="item.id && toggleHasilKerja(item.id)"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-blue-100 text-xs font-bold text-blue-600">
                                    {{ idx + 1 }}
                                </div>
                                <h3 class="truncate text-sm font-semibold text-foreground">{{ namaIndikator(item) }}</h3>
                            </div>
                            <p class="mt-1 pl-8 text-xs text-muted-foreground">
                                {{ item.indikators?.length ?? 0 }} indikator kinerja
                                <span v-if="item.isi_ai" class="ml-2 inline-flex items-center gap-1 text-emerald-600">
                                    <BadgeCheck class="size-3" /> AI tersedia
                                </span>
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-1.5" @click.stop>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-7 px-2 text-xs hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                :disabled="generatingHasilKerjaId === item.id"
                                @click="generateHasilKerja(item)"
                            >
                                <Sparkles class="size-3 mr-1" :class="{ 'animate-spin': generatingHasilKerjaId === item.id }" />
                                {{ generatingHasilKerjaId === item.id ? 'Generating...' : 'Generate AI' }}
                            </Button>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs hover:bg-muted transition-colors" @click="exportHasilKerjaPdf(item)">
                                <FileDown class="size-3 mr-1" />PDF
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-7 px-2 text-xs hover:bg-muted transition-colors"
                                :disabled="uploadingHasilKerjaId === item.id"
                                @click="uploadHasilKerjaToDrive(item)"
                            >
                                <Upload class="size-3 mr-1" />
                                {{ uploadingHasilKerjaId === item.id ? 'Uploading...' : 'Drive' }}
                            </Button>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs hover:bg-muted transition-colors" @click="openHasilKerjaModal(item)">
                                <PencilLine class="size-3 mr-1" />Edit
                            </Button>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs text-destructive hover:bg-red-50 hover:text-red-600 transition-colors" @click="destroyHasilKerja(item)">
                                <Trash2 class="size-3" />
                            </Button>
                            <div class="ml-1 flex h-6 w-6 items-center justify-center text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': item.id && expandedHasilKerja.has(item.id) }">
                                <ChevronDown class="size-4" />
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Content -->
                    <div
                        v-show="item.id && expandedHasilKerja.has(item.id)"
                        class="border-t border-border/50 bg-muted/10 px-4 pb-4 pt-3"
                    >
                        <!-- Narasi AI -->
                        <div
                            v-if="item.isi_ai"
                            class="mb-4 rounded-xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50/60 to-teal-50/40 p-4 text-sm leading-7 text-slate-700 whitespace-pre-line"
                        >
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                <Sparkles class="size-3" /> Narasi AI
                            </p>
                            {{ item.isi_ai }}
                        </div>
                        <div v-else class="mb-4 rounded-xl border border-dashed border-border px-4 py-5 text-center text-xs text-muted-foreground">
                            Narasi AI belum tersedia. Klik Generate AI untuk membuat.
                        </div>

                        <!-- Indikator Kinerja -->
                        <div class="grid gap-3">
                            <div
                                v-for="indikator in item.indikators"
                                :key="indikator.id ?? indikator.deskripsi"
                                class="rounded-xl border border-border/60 bg-background p-4"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 capitalize">
                                        {{ indikator.kategori }}
                                    </span>
                                    <p class="text-sm font-medium text-foreground">{{ indikator.deskripsi }}</p>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Target: <span class="font-semibold text-foreground">{{ indikator.target }} {{ indikator.satuan }}</span>
                                </p>

                                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                    <!-- Rencana Aksi -->
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Rencana Aksi</p>
                                        <div v-if="indikator.rencana_aksi?.length" class="grid gap-1.5">
                                            <div
                                                v-for="rencana in indikator.rencana_aksi"
                                                :key="rencana.id ?? rencana.deskripsi"
                                                class="flex items-start gap-2 rounded-lg bg-muted/50 px-3 py-2 text-xs"
                                            >
                                                <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400" />
                                                {{ rencana.deskripsi }}
                                            </div>
                                        </div>
                                        <p v-else class="text-xs italic text-muted-foreground">Belum ada rencana aksi.</p>
                                    </div>

                                    <!-- Realisasi -->
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Realisasi</p>
                                        <div v-if="indikator.realisasi?.length" class="grid gap-2">
                                            <div
                                                v-for="realisasi in indikator.realisasi"
                                                :key="realisasi.id ?? realisasi.output"
                                                class="rounded-lg border border-border/50 bg-muted/30 p-3 text-xs"
                                            >
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-semibold text-foreground">{{ realisasi.output }}</span>
                                                    <span class="shrink-0 text-muted-foreground">{{ realisasi.tanggal }}</span>
                                                </div>
                                                <p v-if="realisasi.keterangan" class="mt-1 text-muted-foreground">
                                                    {{ realisasi.keterangan }}
                                                </p>
                                                <div
                                                    v-if="realisasi.bukti_foto?.length"
                                                    class="mt-2 grid grid-cols-3 gap-1.5"
                                                >
                                                    <img
                                                        v-for="foto in realisasi.bukti_foto"
                                                        :key="foto.id"
                                                        :src="foto.url"
                                                        alt="Bukti realisasi"
                                                        class="h-16 w-full rounded-lg object-cover ring-1 ring-border/50 transition-opacity hover:opacity-90"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <p v-else class="text-xs italic text-muted-foreground">Belum ada realisasi.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- ── Perilaku Kerja ──────────────────────────────────────────── -->
        <Card class="border-border/60 shadow-sm">
            <CardHeader class="flex flex-row items-center justify-between gap-4 pb-3">
                <div>
                    <CardTitle class="flex items-center gap-2 text-sm font-semibold">
                        <Activity class="size-4 text-muted-foreground" />
                        Perilaku Kerja
                    </CardTitle>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ laporan.perilaku_kerja.length }} perilaku kerja tercatat
                    </p>
                </div>
                <Button size="sm" @click="openPerilakuModal()">
                    <Plus class="size-3.5 mr-1.5" />
                    Tambah
                </Button>
            </CardHeader>
            <CardContent class="grid gap-3">
                <!-- Empty state -->
                <div
                    v-if="laporan.perilaku_kerja.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-14 text-center"
                >
                    <Activity class="mb-2 size-10 text-muted-foreground/30" />
                    <p class="text-sm font-medium text-muted-foreground">Belum ada perilaku kerja</p>
                    <p class="mt-1 text-xs text-muted-foreground/70">Tambahkan catatan perilaku kerja dan bukti pendukung</p>
                    <Button size="sm" variant="outline" class="mt-4" @click="openPerilakuModal()">
                        <Plus class="size-3.5 mr-1.5" />
                        Tambah Perilaku
                    </Button>
                </div>

                <!-- Perilaku Kerja Cards -->
                <div
                    v-for="(item, idx) in laporan.perilaku_kerja"
                    :key="item.id ?? `perilaku-${item.nama}`"
                    class="overflow-hidden rounded-xl border border-border/60 bg-card transition-all duration-300"
                    :style="{ animationDelay: `${idx * 60}ms` }"
                >
                    <!-- Card Header -->
                    <div
                        class="flex cursor-pointer select-none items-start justify-between gap-4 p-4 transition-colors duration-150 hover:bg-muted/30"
                        @click="item.id && togglePerilaku(item.id)"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-amber-100 text-xs font-bold text-amber-600">
                                    {{ idx + 1 }}
                                </div>
                                <h3 class="truncate text-sm font-semibold text-foreground">{{ item.nama }}</h3>
                            </div>
                            <p class="mt-1 pl-8 text-xs text-muted-foreground">
                                {{ item.deskripsi ? item.deskripsi.substring(0, 80) + '...' : 'Lihat detail perilaku' }}
                                <span v-if="item.isi_ai" class="ml-2 inline-flex items-center gap-1 text-emerald-600">
                                    <BadgeCheck class="size-3" /> AI tersedia
                                </span>
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-1.5" @click.stop>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-7 px-2 text-xs hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                :disabled="generatingPerilakuId === item.id"
                                @click="generatePerilaku(item)"
                            >
                                <Sparkles class="size-3 mr-1" :class="{ 'animate-spin': generatingPerilakuId === item.id }" />
                                {{ generatingPerilakuId === item.id ? 'Generating...' : 'Generate AI' }}
                            </Button>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs hover:bg-muted transition-colors" @click="exportPerilakuPdf(item)">
                                <FileDown class="size-3 mr-1" />PDF
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-7 px-2 text-xs hover:bg-muted transition-colors"
                                :disabled="uploadingPerilakuId === item.id"
                                @click="uploadPerilakuToDrive(item)"
                            >
                                <Upload class="size-3 mr-1" />
                                {{ uploadingPerilakuId === item.id ? 'Uploading...' : 'Drive' }}
                            </Button>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs hover:bg-muted transition-colors" @click="openPerilakuModal(item)">
                                <PencilLine class="size-3 mr-1" />Edit
                            </Button>
                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs text-destructive hover:bg-red-50 hover:text-red-600 transition-colors" @click="destroyPerilaku(item)">
                                <Trash2 class="size-3" />
                            </Button>
                            <div class="ml-1 flex h-6 w-6 items-center justify-center text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': item.id && expandedPerilaku.has(item.id) }">
                                <ChevronDown class="size-4" />
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Content -->
                    <div
                        v-show="item.id && expandedPerilaku.has(item.id)"
                        class="border-t border-border/50 bg-muted/10 px-4 pb-4 pt-3"
                    >
                        <!-- Deskripsi Lengkap -->
                        <div class="mb-4 rounded-xl border border-border/50 bg-muted/30 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">Ekspektasi Perilaku</p>
                            <p class="text-sm text-foreground leading-relaxed">{{ item.deskripsi }}</p>
                        </div>

                        <!-- Narasi AI -->
                        <div
                            v-if="item.isi_ai"
                            class="mb-4 rounded-xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50/60 to-teal-50/40 p-4 text-sm leading-7 text-slate-700 whitespace-pre-line"
                        >
                            <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                <Sparkles class="size-3" /> Narasi Perilaku (AI)
                            </p>
                            {{ item.isi_ai }}
                        </div>
                        <div v-else class="mb-4 rounded-xl border border-dashed border-border px-4 py-5 text-center text-xs text-muted-foreground">
                            Narasi AI belum tersedia. Klik Generate AI untuk membuat.
                        </div>

                        <!-- Bukti Foto -->
                        <div v-if="item.bukti_perilaku?.length">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">Bukti Dukung Perilaku</p>
                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                <img
                                    v-for="foto in item.bukti_perilaku"
                                    :key="foto.id"
                                    :src="foto.url"
                                    alt="Bukti perilaku"
                                    class="h-24 w-full rounded-lg object-cover ring-1 ring-border/50 transition-all hover:ring-blue-400 hover:opacity-90 cursor-zoom-in"
                                />
                            </div>
                        </div>
                        <p v-else class="text-xs italic text-muted-foreground pl-8">Belum ada bukti foto pendukung.</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

    <HasilKerjaModal
        v-model:open="hasilKerjaOpen"
        :laporan-id="laporan.id"
        :editing-item="selectedHasilKerja"
        :indikator-kinerja-masters="indikatorKinerjaMasters"
    />

    <PerilakuKerjaModal
        v-model:open="perilakuOpen"
        :laporan-id="laporan.id"
        :editing-item="selectedPerilaku"
    />
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease both;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Smooth expand/collapse */
[v-show] {
    transition: opacity 0.2s ease;
}
</style>
