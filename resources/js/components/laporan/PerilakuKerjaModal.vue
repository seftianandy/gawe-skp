<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PerilakuKerjaController from '@/actions/App/Http/Controllers/PerilakuKerjaController';
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
import { Spinner } from '@/components/ui/spinner';
import type { PerilakuKerjaForm } from '@/types';

type Props = {
    open: boolean;
    laporanId: number;
    editingItem?: PerilakuKerjaForm | null;
};

const props = withDefaults(defineProps<Props>(), {
    editingItem: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

function cloneItem(item: PerilakuKerjaForm | null | undefined): PerilakuKerjaForm {
    return {
        id: item?.id ?? null,
        nama: item?.nama ?? '',
        deskripsi: item?.deskripsi ?? '',
        bukti_perilaku: item?.bukti_perilaku ?? [],
        bukti_perilaku_baru: [],
        hapus_bukti_perilaku: [],
    };
}

const form = useForm<PerilakuKerjaForm>(cloneItem(null));

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }

        form.defaults(cloneItem(props.editingItem));
        form.reset();
        form.clearErrors();
    },
);

const title = computed(() =>
    props.editingItem ? 'Edit perilaku kerja' : 'Tambah perilaku kerja',
);

function submit(): void {
    const action = props.editingItem?.id
        ? PerilakuKerjaController.update({
              laporan: props.laporanId,
              perilaku_kerja: props.editingItem.id,
          })
        : PerilakuKerjaController.store(props.laporanId);

    form.submit(action.method, action.url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    Kelola catatan perilaku kerja dan unggah bukti fotonya.
                </DialogDescription>
            </DialogHeader>

            <form class="grid gap-5" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="nama">Nama perilaku</Label>
                    <Input id="nama" v-model="form.nama" placeholder="Contoh: Kolaboratif" />
                    <InputError :message="form.errors.nama" />
                </div>

                <div class="grid gap-2">
                    <Label for="deskripsi">Deskripsi</Label>
                    <textarea
                        id="deskripsi"
                        v-model="form.deskripsi"
                        class="min-h-28 rounded-xl border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none ring-0 transition placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                    />
                    <InputError :message="form.errors.deskripsi" />
                </div>

                <PhotoUploader
                    label="Bukti foto perilaku kerja"
                    :existing-photos="form.bukti_perilaku"
                    :deleted-ids="form.hapus_bukti_perilaku"
                    :new-files="form.bukti_perilaku_baru"
                    :error="form.errors['bukti_perilaku_baru.0']"
                    @update:new-files="form.bukti_perilaku_baru = $event"
                    @update:deleted-ids="form.hapus_bukti_perilaku = $event"
                />

                <DialogFooter>
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        Simpan perilaku kerja
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
