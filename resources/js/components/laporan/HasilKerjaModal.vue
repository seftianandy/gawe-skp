<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { MinusCircle, Plus, ClipboardList, Image, Target, Paperclip, Upload, FileText, X } from 'lucide-vue-next';
import HasilKerjaController from '@/actions/App/Http/Controllers/HasilKerjaController';
import InputError from '@/components/InputError.vue';
import PhotoUploader from '@/components/laporan/PhotoUploader.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import type { HasilKerjaForm, IndikatorKinerjaMasterItem } from '@/types';

type Props = {
    open: boolean;
    laporanId: number;
    indikatorKinerjaMasters: IndikatorKinerjaMasterItem[];
    editingItem?: HasilKerjaForm | null;
};

const props = withDefaults(defineProps<Props>(), {
    editingItem: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

// ─── Form Helpers ────────────────────────────────────────────────────────────

function createEmptyForm(): HasilKerjaForm {
    return {
        id: null,
        indikator_kinerja_id: null,
        isi_ai: null,
        indikator_kinerja_master: null,
        rencana_aksi: [{ id: null, deskripsi: '' }],
        bukti_foto: [],
        bukti_foto_baru: [],
        hapus_bukti_foto: [],
        lampiran_files: [],
        indikators: [],
    };
}

function cloneItem(item: HasilKerjaForm | null | undefined): HasilKerjaForm {
    return {
        id: item?.id ?? null,
        indikator_kinerja_id: item?.indikator_kinerja_id ? String(item.indikator_kinerja_id) : null,
        isi_ai: item?.isi_ai ?? null,
        indikator_kinerja_master: item?.indikator_kinerja_master ?? null,
        rencana_aksi: item?.rencana_aksi?.length
            ? item.rencana_aksi.map((r) => ({ id: r.id, deskripsi: r.deskripsi }))
            : [{ id: null, deskripsi: '' }],
        bukti_foto: item?.bukti_foto ?? [],
        bukti_foto_baru: [],
        hapus_bukti_foto: [],
        lampiran_files: item?.lampiran_files ?? [],
        indikators: item?.indikators ?? [],
    };
}

// ─── Form ────────────────────────────────────────────────────────────────────

const form = useForm<HasilKerjaForm>(createEmptyForm());
const lampiranForm = useForm({
    files: [] as File[],
});

form.transform((data) => ({
    ...data,
    rencana_aksi: data.rencana_aksi.map((item) => item.deskripsi ?? ''),
}));

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) return;
        form.defaults(cloneItem(props.editingItem));
        form.reset();
        form.clearErrors();
        lampiranForm.reset();
        lampiranForm.clearErrors();
    },
);

// ─── Computed ────────────────────────────────────────────────────────────────

const title = computed(() =>
    props.editingItem ? 'Edit Hasil Kerja' : 'Tambah Hasil Kerja',
);

const selectedMaster = computed((): IndikatorKinerjaMasterItem | null => {
    const selectedId = form.indikator_kinerja_id ?? props.editingItem?.indikator_kinerja_id ?? null;
    if (!selectedId) return props.editingItem?.indikator_kinerja_master ?? null;
    return (
        props.indikatorKinerjaMasters.find((m) => m.id === Number(selectedId))
        ?? props.editingItem?.indikator_kinerja_master
        ?? null
    );
});

function rencanaAksiError(index: number): string | undefined {
    return form.errors[`rencana_aksi.${index}`] ?? form.errors.rencana_aksi;
}

// ─── Rencana Aksi ─────────────────────────────────────────────────────────────

function addRencanaAksi(): void {
    form.rencana_aksi.push({ id: null, deskripsi: '' });
}

function removeRencanaAksi(index: number): void {
    if (form.rencana_aksi.length === 1) {
        form.rencana_aksi[0].deskripsi = '';
        return;
    }
    form.rencana_aksi.splice(index, 1);
}

// ─── Foto ─────────────────────────────────────────────────────────────────────

function handleNewFiles(files: File[]): void {
    const activeCount = form.bukti_foto.filter(
        (p) => !form.hapus_bukti_foto.includes(p.id),
    ).length;
    form.bukti_foto_baru = files.slice(0, Math.max(0, 5 - activeCount));
}

function handleDeletedIds(ids: number[]): void {
    form.hapus_bukti_foto = ids;
}

// ─── Lampiran PDF ────────────────────────────────────────────────────────────

function handleFiles(event: Event): void {
    lampiranForm.files = Array.from((event.target as HTMLInputElement).files ?? []);
}

function removeLampiranFile(index: number): void {
    lampiranForm.files = lampiranForm.files.filter((_, currentIndex) => currentIndex !== index);
}

function submitLampiran(): void {
    if (!props.editingItem?.id || lampiranForm.files.length === 0) {
        return;
    }

    const action = HasilKerjaController.uploadLampiran(props.editingItem.id);

    lampiranForm.post(action.url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            lampiranForm.files = [];
            lampiranForm.clearErrors();
            emit('update:open', false);
        },
    });
}

const lampiranSelectedCount = computed(() => lampiranForm.files.length);

// ─── Submit ───────────────────────────────────────────────────────────────────

function submit(): void {
    const action = props.editingItem?.id
        ? HasilKerjaController.update({ laporan: props.laporanId, hasil_kerja: props.editingItem.id })
        : HasilKerjaController.store(props.laporanId);

    form.submit(action.method, action.url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <!-- Mengubah max-w-2xl menjadi 4xl atau 5xl agar lebih lega -->
        <DialogContent class="max-h-[92vh] overflow-y-auto sm:max-w-4xl lg:max-w-5xl">

            <DialogHeader class="space-y-1">
                <DialogTitle class="flex items-center gap-2 text-base">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <ClipboardList class="size-4" />
                    </div>
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="text-xs">
                    Pilih indikator kinerja, isi rencana aksi, lalu unggah maksimal 5 foto bukti pendukung.
                </DialogDescription>
            </DialogHeader>

            <form class="mt-2 grid gap-5" @submit.prevent="submit">

                <!-- ── Indikator Kinerja ──────────────────────────────── -->
                <section class="grid gap-3 rounded-xl border border-border/60 bg-muted/20 p-4">
                    <div class="flex items-center gap-2">
                        <Target class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">Indikator Kinerja</h3>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="indikator_kinerja_id" class="text-xs text-muted-foreground">
                            Pilih indikator dari master data
                        </Label>
                        <Select v-model="form.indikator_kinerja_id">
                            <SelectTrigger id="indikator_kinerja_id" class="w-full h-auto min-h-[40px] py-2">
                                <!-- Tambahkan pembungkus dengan whitespace-normal agar teks di dalam trigger tidak satu baris -->
                                <div class="text-left whitespace-normal leading-snug pr-4">
                                    <SelectValue placeholder="Pilih indikator kinerja..." />
                                </div>
                            </SelectTrigger>
                            <SelectContent class="max-h-64">
                                <SelectItem
                                    v-for="master in indikatorKinerjaMasters"
                                    :key="master.id"
                                    :value="String(master.id)"
                                >
                                    <!-- Naikkan max-w agar teks indikator lebih terbaca -->
                                    <span class="block max-w-[50rem] truncate" :title="master.nama_indikator">
                                        {{ master.nama_indikator }}
                                    </span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.indikator_kinerja_id" />
                    </div>

                    <!-- Preview indikator terpilih -->
                    <Transition name="slide-down">
                        <div
                            v-if="selectedMaster"
                            class="rounded-lg border border-blue-100 bg-blue-50/60 p-3"
                        >
                            <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide mb-1.5">
                                Indikator terpilih
                            </p>
                            <p class="text-sm font-medium leading-relaxed text-foreground whitespace-normal break-words">
                                {{ selectedMaster.nama_indikator }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-md bg-white border border-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 capitalize">
                                    {{ selectedMaster.kategori }}
                                </span>
                                <span class="inline-flex items-center rounded-md bg-white border border-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                    Target {{ selectedMaster.target }} {{ selectedMaster.satuan }}
                                </span>
                            </div>
                        </div>
                    </Transition>
                </section>

                <!-- ── Rencana Aksi ───────────────────────────────────── -->
                <section class="grid gap-3 rounded-xl border border-border/60 bg-muted/20 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <ClipboardList class="size-4 text-muted-foreground" />
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">Rencana Aksi</h3>
                                <p class="text-xs text-muted-foreground">Langkah kerja yang akan dilaksanakan.</p>
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            class="h-8 shrink-0 text-xs"
                            @click="addRencanaAksi"
                        >
                            <Plus class="size-3.5 mr-1" />
                            Tambah
                        </Button>
                    </div>

                    <div class="grid gap-2">
                        <TransitionGroup name="list">
                            <div
                                v-for="(rencana, index) in form.rencana_aksi"
                                :key="rencana.id ?? `rencana-${index}`"
                                class="grid gap-1.5"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-muted-foreground">
                                        {{ index + 1 }}
                                    </span>
                                    <Input
                                        v-model="rencana.deskripsi"
                                        :placeholder="`Deskripsikan rencana aksi ${index + 1}...`"
                                        class="flex-1 text-sm"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 shrink-0 text-muted-foreground hover:text-destructive hover:bg-red-50 transition-colors"
                                        :disabled="form.rencana_aksi.length === 1"
                                        @click="removeRencanaAksi(index)"
                                    >
                                        <MinusCircle class="size-4" />
                                    </Button>
                                </div>
                                <InputError :message="rencanaAksiError(index)" class="pl-7" />
                            </div>
                        </TransitionGroup>
                    </div>
                </section>

                <!-- ── Bukti Foto ─────────────────────────────────────── -->
                <section class="grid gap-3 rounded-xl border border-border/60 bg-muted/20 p-4">
                    <div class="flex items-center gap-2">
                        <Image class="size-4 text-muted-foreground" />
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Bukti Foto</h3>
                            <p class="text-xs text-muted-foreground">Unggah maksimal 5 foto pendukung kegiatan.</p>
                        </div>
                    </div>

                    <PhotoUploader
                        label="Bukti foto"
                        :existing-photos="form.bukti_foto"
                        :deleted-ids="form.hapus_bukti_foto"
                        :new-files="form.bukti_foto_baru"
                        :error="form.errors['bukti_foto_baru.0'] ?? form.errors.bukti_foto_baru"
                        @update:new-files="handleNewFiles"
                        @update:deleted-ids="handleDeletedIds"
                    />
                </section>

                <!-- ── Lampiran PDF ─────────────────────────────────── -->
                <section class="grid gap-3 rounded-xl border border-border/60 bg-muted/20 p-4">
                    <div class="flex items-center gap-2">
                        <Paperclip class="size-4 text-muted-foreground" />
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Lampiran PDF</h3>
                            <p class="text-xs text-muted-foreground">
                                Upload beberapa file PDF terpisah dari laporan utama.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 rounded-xl border border-dashed border-border/70 bg-background/70 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-foreground">Unggah lampiran PDF</p>
                                <p class="text-xs text-muted-foreground">
                                    Hanya file PDF, maksimal 5 MB per file. Lampiran akan tersimpan sebagai lampiran-1.pdf, lampiran-2.pdf, dan seterusnya.
                                </p>
                            </div>

                            <label
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-primary/30 bg-primary/5 px-3 py-2 text-sm font-medium text-primary transition hover:bg-primary/10"
                                :class="{ 'pointer-events-none opacity-50': !props.editingItem?.id }"
                            >
                                <Upload class="size-4" />
                                Pilih PDF
                                <input
                                    class="hidden"
                                    type="file"
                                    multiple
                                    accept="application/pdf"
                                    :disabled="!props.editingItem?.id"
                                    @change="handleFiles"
                                >
                            </label>
                        </div>

                        <InputError :message="lampiranForm.errors.files ?? lampiranForm.errors['files.0']" />

                        <div
                            v-if="props.editingItem?.lampiran_files?.length || lampiranSelectedCount"
                            class="grid gap-3"
                        >
                            <div v-if="props.editingItem?.lampiran_files?.length" class="grid gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Lampiran tersimpan</p>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <a
                                        v-for="lampiran in props.editingItem.lampiran_files"
                                        :key="lampiran.id"
                                        :href="lampiran.url"
                                        target="_blank"
                                        class="flex items-center gap-3 rounded-lg border border-border bg-background px-3 py-2 transition hover:border-primary/40 hover:bg-primary/5"
                                    >
                                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-muted text-muted-foreground">
                                            <FileText class="size-4" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-foreground">{{ lampiran.nama_file }}</p>
                                            <p class="truncate text-xs text-muted-foreground">{{ lampiran.file_path }}</p>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <div v-if="lampiranSelectedCount" class="grid gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">File siap upload</p>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <div
                                        v-for="(file, index) in lampiranForm.files"
                                        :key="`${file.name}-${file.size}-${file.lastModified}`"
                                        class="flex items-center gap-3 rounded-lg border border-primary/25 bg-primary/5 px-3 py-2"
                                    >
                                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white text-primary">
                                            <FileText class="size-4" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-foreground">{{ file.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ Math.ceil(file.size / 1024) }} KB</p>
                                        </div>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition hover:bg-destructive/10 hover:text-destructive"
                                            @click="removeLampiranFile(index)"
                                        >
                                            <X class="size-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-lg border border-dashed border-border bg-muted/30 px-4 py-3 text-sm text-muted-foreground"
                        >
                            Belum ada lampiran tersimpan. Pilih beberapa file PDF untuk mengunggah lampiran ke hasil kerja ini.
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="text-sm"
                                :disabled="!props.editingItem?.id || !lampiranSelectedCount || lampiranForm.processing"
                                @click="submitLampiran"
                            >
                                <Spinner v-if="lampiranForm.processing" class="mr-2" />
                                {{ lampiranForm.processing ? 'Mengupload...' : 'Upload Lampiran' }}
                            </Button>
                        </div>
                    </div>
                </section>

                <!-- ── Footer ─────────────────────────────────────────── -->
                <DialogFooter class="gap-2 pt-1">
                    <Button
                        type="button"
                        variant="outline"
                        class="text-sm"
                        @click="emit('update:open', false)"
                    >
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="min-w-[120px] text-sm">
                        <Spinner v-if="form.processing" class="mr-2" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Hasil Kerja' }}
                    </Button>
                </DialogFooter>
            </form>

        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Preview indikator slide-down */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* Rencana aksi list transition */
.list-enter-active,
.list-leave-active {
    transition: all 0.18s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(-8px);
}
</style>
