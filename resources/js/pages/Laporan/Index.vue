<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Sparkles, Trash2, PencilLine, FileDown, ClipboardList, Activity, BadgeCheck, FileText } from 'lucide-vue-next';
import ExportLaporanPdfController from '@/actions/App/Http/Controllers/ExportLaporanPdfController';
import GenerateLaporanAiController from '@/actions/App/Http/Controllers/GenerateLaporanAiController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { create, destroy, edit, show, index as laporanIndex } from '@/routes/laporan';
import type { LaporanListItem } from '@/types';

const props = defineProps<{
    laporan: LaporanListItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Laporan', href: laporanIndex() },
        ],
    },
});

const aiForm = useForm({});
const generatingId = ref<number | null>(null);

const statusColor = (status: string) => {
    const s = status?.toLowerCase();
    if (s === 'final')  return 'text-emerald-600 bg-emerald-50 border-emerald-200';
    if (s === 'draft')  return 'text-amber-600 bg-amber-50 border-amber-200';
    return 'text-blue-600 bg-blue-50 border-blue-200';
};

function handleGenerate(laporanId: number): void {
    generatingId.value = laporanId;
    const action = GenerateLaporanAiController(laporanId);
    aiForm.submit(action.method, action.url, {
        preserveScroll: true,
        onFinish: () => { generatingId.value = null; },
    });
}

function handleDelete(laporanId: number): void {
    if (!window.confirm('Hapus laporan ini?')) return;
    router.delete(destroy(laporanId).url, { preserveScroll: true });
}

function handleExportPdf(laporanId: number): void {
    const action = ExportLaporanPdfController(laporanId);
    window.location.href = action.url;
}
</script>

<template>
    <Head title="Laporan SKP" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 animate-fade-in">

        <!-- ── Hero Header ─────────────────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 shadow-lg">
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:32px_32px]" />
            <div class="pointer-events-none absolute -top-20 -right-20 h-60 w-60 rounded-full bg-sky-500/10 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-emerald-500/10 blur-3xl" />

            <div class="relative flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10">
                            <FileText class="size-4 text-white" />
                        </div>
                        <span class="text-xs font-medium text-slate-400 uppercase tracking-widest">Manajemen SKP</span>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Laporan SKP</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Buat, perbarui, generate narasi AI, dan ekspor dokumen kinerja bulanan.
                    </p>
                </div>
                <Button
                    class="shrink-0 bg-white text-slate-900 hover:bg-slate-100 transition-all duration-200 shadow-md font-semibold"
                    as-child
                >
                    <Link :href="create()">
                        <Plus class="size-4 mr-1.5" />
                        Buat Laporan
                    </Link>
                </Button>
            </div>
        </div>

        <!-- ── Summary Stats ───────────────────────────────────────────── -->
        <div v-if="laporan.length > 0" class="grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <FileText class="size-4" />
                    <span class="text-xs font-medium">Total Laporan</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ laporan.length }}</p>
            </div>
            <div class="rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <ClipboardList class="size-4" />
                    <span class="text-xs font-medium">Total Hasil Kerja</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">
                    {{ laporan.reduce((s, l) => s + (l.hasil_kerja_count ?? 0), 0) }}
                </p>
            </div>
            <div class="rounded-xl border border-border/60 bg-card p-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <Activity class="size-4" />
                    <span class="text-xs font-medium">Total Perilaku</span>
                </div>
                <p class="mt-2 text-2xl font-bold text-foreground">
                    {{ laporan.reduce((s, l) => s + (l.perilaku_kerja_count ?? 0), 0) }}
                </p>
            </div>
        </div>

        <!-- ── Laporan List ────────────────────────────────────────────── -->
        <div class="grid gap-3">
            <!-- Empty State -->
            <div
                v-if="laporan.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card py-20 text-center"
            >
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-muted">
                    <FileText class="size-7 text-muted-foreground/50" />
                </div>
                <p class="mt-4 text-sm font-semibold text-foreground">Belum ada laporan SKP</p>
                <p class="mt-1 max-w-xs text-xs text-muted-foreground">
                    Buat laporan pertama untuk mulai mengisi hasil kerja dan perilaku kerja.
                </p>
                <Button class="mt-5" as-child>
                    <Link :href="create()">
                        <Plus class="size-4 mr-1.5" />
                        Buat Laporan Pertama
                    </Link>
                </Button>
            </div>

            <!-- Laporan Cards -->
            <div
                v-for="(item, idx) in laporan"
                :key="item.id"
                class="group relative overflow-hidden rounded-xl border border-border/60 bg-card p-5 transition-all duration-200 hover:shadow-md hover:border-border"
                :style="{ animationDelay: `${idx * 50}ms` }"
            >
                <!-- Status accent bar -->
                <div
                    class="absolute left-0 top-0 h-full w-1 rounded-l-xl transition-all duration-300"
                    :class="{
                        'bg-emerald-400': item.status?.toLowerCase() === 'final',
                        'bg-amber-400':   item.status?.toLowerCase() === 'draft',
                        'bg-blue-400':    !['final','draft'].includes(item.status?.toLowerCase()),
                    }"
                />

                <div class="flex flex-col gap-4 pl-3 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Left: Info -->
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary transition-colors duration-200">
                            <FileText class="size-5" />
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-semibold text-foreground">{{ item.periode_label }}</h3>
                                <span :class="['inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold', statusColor(item.status)]">
                                    <BadgeCheck class="size-3" />
                                    {{ item.status?.toUpperCase() }}
                                </span>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                <span class="flex items-center gap-1">
                                    <ClipboardList class="size-3" />
                                    {{ item.hasil_kerja_count }} hasil kerja
                                </span>
                                <span class="flex items-center gap-1">
                                    <Activity class="size-3" />
                                    {{ item.perilaku_kerja_count }} perilaku kerja
                                </span>
                                <span
                                    :class="[
                                        'flex items-center gap-1',
                                        item.isi_laporan ? 'text-emerald-600' : 'text-muted-foreground'
                                    ]"
                                >
                                    <Sparkles class="size-3" />
                                    {{ item.isi_laporan ? 'Narasi AI tersedia' : 'Narasi AI belum ada' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex shrink-0 flex-wrap items-center gap-1.5 pl-14 lg:pl-0">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs"
                            as-child
                        >
                            <Link :href="show(item.id)">
                                <FileText class="size-3 mr-1.5" />
                                Isi Laporan
                            </Link>
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-colors"
                            :disabled="generatingId === item.id"
                            @click="handleGenerate(item.id)"
                        >
                            <Sparkles
                                class="size-3 mr-1.5"
                                :class="{ 'animate-spin': generatingId === item.id }"
                            />
                            {{ generatingId === item.id ? 'Generating...' : 'Generate AI' }}
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs"
                            as-child
                        >
                            <Link :href="edit(item.id)">
                                <PencilLine class="size-3 mr-1.5" />
                                Edit
                            </Link>
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs"
                            @click="handleExportPdf(item.id)"
                        >
                            <FileDown class="size-3 mr-1.5" />
                            PDF
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs text-destructive hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors"
                            @click="handleDelete(item.id)"
                        >
                            <Trash2 class="size-3" />
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
